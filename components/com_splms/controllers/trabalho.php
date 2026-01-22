<?php
/**
 * @package com_splms
 * GUIDEWAY CUSTOM - Joshua - Controller de Upload de Trabalhos
 * Gerencia upload e download seguro de arquivos
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Response\JsonResponse;

require_once JPATH_COMPONENT . '/helpers/FileManager.php';

class SplmsControllerTrabalho extends BaseController
{
    /**
     * Upload de arquivo
     * Endpoint: index.php?option=com_splms&task=trabalho.upload
     */
    public function upload()
    {
        // Verificar se usuario esta logado
        $user = Factory::getUser();
        if (!$user->id) {
            echo new JsonResponse(null, 'Voce precisa estar logado', true);
            jexit();
        }

        // Pegar dados do formulario
        $input = Factory::getApplication()->input;
        $lessonId = $input->getInt('lesson_id', 0);
        $comentario = $input->getString('comentario', '');

        if (!$lessonId) {
            echo new JsonResponse(null, 'ID da licao nao fornecido', true);
            jexit();
        }

        // USAR JInputFiles para seguranca
        $files = $input->files->get('arquivo');

        if (!$files || $files['error'] !== UPLOAD_ERR_OK) {
            $errorMsg = $this->getUploadErrorMessage($files['error'] ?? UPLOAD_ERR_NO_FILE);
            echo new JsonResponse(null, $errorMsg, true);
            jexit();
        }

        // Salvar arquivo usando FileManager
        $fileManager = new SplmsFileManager();
        $resultado = $fileManager->salvar($files, $user->id, $lessonId);

        if (isset($resultado['error'])) {
            echo new JsonResponse(null, $resultado['error'], true);
            jexit();
        }

        // Salvar no banco de dados
        try {
            $trabalhoId = $this->salvarNoBanco(
                $user->id,
                $lessonId,
                $resultado,
                $comentario
            );

            echo new JsonResponse([
                'id' => $trabalhoId,
                'arquivo' => $resultado['nome_original'],
                'tamanho' => $fileManager->formatarTamanho($resultado['tamanho'])
            ], 'Trabalho enviado com sucesso!');

        } catch (Exception $e) {
            // Se erro no banco, deletar arquivo
            $fileManager->deletar($resultado['nome_salvo']);
            
            echo new JsonResponse(null, 'Erro ao salvar: ' . $e->getMessage(), true);
        }

        jexit();
    }

    /**
     * Download seguro de arquivo
     * Endpoint: index.php?option=com_splms&task=trabalho.download&id=X
     */
    public function download()
    {
        $user = Factory::getUser();
        $input = Factory::getApplication()->input;
        $trabalhoId = $input->getInt('id', 0);

        if (!$trabalhoId) {
            jexit('ID invalido');
        }

        // Buscar trabalho no banco
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('*')
            ->from($db->quoteName('#__splms_submissions'))
            ->where($db->quoteName('id') . ' = ' . (int)$trabalhoId);
        
        $db->setQuery($query);
        $trabalho = $db->loadObject();

        if (!$trabalho) {
            jexit('Trabalho nao encontrado');
        }

        // Verificar permissao: so o aluno dono ou professor podem baixar
        $podeAcessar = (
            $user->id == $trabalho->user_id || // E o dono
            $user->authorise('core.admin', 'com_splms') // E admin/professor
        );

        if (!$podeAcessar) {
            jexit('Acesso negado');
        }

        // Caminho do arquivo
        $fileManager = new SplmsFileManager();
        $filepath = $fileManager->getCaminho($trabalho->arquivo_nome_salvo);

        if (!$fileManager->existe($trabalho->arquivo_nome_salvo)) {
            jexit('Arquivo nao encontrado no servidor');
        }

        // Enviar arquivo para download
        header('Content-Description: File Transfer');
        header('Content-Type: ' . $trabalho->arquivo_tipo_mime);
        header('Content-Disposition: attachment; filename="' . $trabalho->arquivo_nome_original . '"');
        header('Content-Length: ' . filesize($filepath));
        header('Pragma: public');
        
        readfile($filepath);
        jexit();
    }

    /**
     * Salvar informacoes no banco de dados
     */
    private function salvarNoBanco($userId, $lessonId, $dadosArquivo, $comentario)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        // Verificar se ja existe envio pendente
        $query->select('id')
            ->from($db->quoteName('#__splms_submissions'))
            ->where($db->quoteName('user_id') . ' = ' . (int)$userId)
            ->where($db->quoteName('lesson_id') . ' = ' . (int)$lessonId)
            ->where($db->quoteName('status') . ' = ' . $db->quote('pendente'));
        
        $db->setQuery($query);
        $existente = $db->loadResult();

        if ($existente) {
            // Atualizar registro existente
            $query->clear();
            $query->update($db->quoteName('#__splms_submissions'))
                ->set($db->quoteName('arquivo_nome_original') . ' = ' . $db->quote($dadosArquivo['nome_original']))
                ->set($db->quoteName('arquivo_nome_salvo') . ' = ' . $db->quote($dadosArquivo['nome_salvo']))
                ->set($db->quoteName('arquivo_extensao') . ' = ' . $db->quote($dadosArquivo['extensao']))
                ->set($db->quoteName('arquivo_tamanho') . ' = ' . (int)$dadosArquivo['tamanho'])
                ->set($db->quoteName('arquivo_tipo_mime') . ' = ' . $db->quote($dadosArquivo['tipo_mime']))
                ->set($db->quoteName('comentario_aluno') . ' = ' . $db->quote($comentario))
                ->set($db->quoteName('data_envio') . ' = ' . $db->quote(Factory::getDate()->toSql()))
                ->where($db->quoteName('id') . ' = ' . (int)$existente);

            $db->setQuery($query);
            $db->execute();

            return $existente;
        }

        // Inserir novo registro
        $query->clear();
        $columns = [
            'user_id',
            'lesson_id',
            'arquivo_nome_original',
            'arquivo_nome_salvo',
            'arquivo_extensao',
            'arquivo_tamanho',
            'arquivo_tipo_mime',
            'comentario_aluno',
            'data_envio',
            'status'
        ];

        $values = [
            (int)$userId,
            (int)$lessonId,
            $db->quote($dadosArquivo['nome_original']),
            $db->quote($dadosArquivo['nome_salvo']),
            $db->quote($dadosArquivo['extensao']),
            (int)$dadosArquivo['tamanho'],
            $db->quote($dadosArquivo['tipo_mime']),
            $db->quote($comentario),
            $db->quote(Factory::getDate()->toSql()),
            $db->quote('pendente')
        ];

        $query->insert($db->quoteName('#__splms_submissions'))
            ->columns($db->quoteName($columns))
            ->values(implode(',', $values));

        $db->setQuery($query);
        $db->execute();

        return $db->insertid();
    }

    /**
     * Converter codigo de erro de upload
     */
    private function getUploadErrorMessage($errorCode)
    {
        switch ($errorCode) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return 'Arquivo muito grande';
            case UPLOAD_ERR_PARTIAL:
                return 'Upload incompleto';
            case UPLOAD_ERR_NO_FILE:
                return 'Nenhum arquivo enviado';
            case UPLOAD_ERR_NO_TMP_DIR:
                return 'Pasta temporaria nao encontrada';
            case UPLOAD_ERR_CANT_WRITE:
                return 'Erro ao escrever arquivo';
            default:
                return 'Erro no upload';
        }
    }
}
