<?php
/**
 * @package com_splms
 * GUIDEWAY CUSTOM - Joshua - Validador de Upload Seguro
 * Valida arquivos antes de salvar (extensão, tamanho, MIME type)
 */

defined('_JEXEC') or die('Restricted access');

class SplmsUploadValidator
{
    private $config = [
        'allowed_extensions' => ['pdf', 'zip', 'mp4'],
        'allowed_mime_types' => [
            'application/pdf',
            'application/zip',
            'application/x-zip-compressed',
            'video/mp4'
        ],
        'max_size' => 10485760 // 10MB em bytes
    ];

    /**
     * Validar arquivo completo
     * @param array $arquivo Array do $_FILES
     * @return mixed true se válido, string com erro se inválido
     */
    public function validar($arquivo)
    {
        if (!$arquivo || !isset($arquivo['error'])) {
            return 'Nenhum arquivo fornecido';
        }

        // Verificar erros de upload
        if ($arquivo['error'] !== UPLOAD_ERR_OK) {
            return $this->getUploadErrorMessage($arquivo['error']);
        }

        // Validar extensão
        $resultadoExtensao = $this->validarExtensao($arquivo['name']);
        if ($resultadoExtensao !== true) {
            return $resultadoExtensao;
        }

        // Validar tipo MIME
        $resultadoMime = $this->validarMimeType($arquivo['type'], $arquivo['tmp_name']);
        if ($resultadoMime !== true) {
            return $resultadoMime;
        }

        // Validar tamanho
        $resultadoTamanho = $this->validarTamanho($arquivo['size']);
        if ($resultadoTamanho !== true) {
            return $resultadoTamanho;
        }

        return true;
    }

    /**
     * Validar extensão do arquivo
     */
    public function validarExtensao($nomeArquivo)
    {
        $extensao = strtolower(pathinfo($nomeArquivo, PATHINFO_EXTENSION));
        
        if (!in_array($extensao, $this->config['allowed_extensions'])) {
            $permitidas = implode(', ', array_map('strtoupper', $this->config['allowed_extensions']));
            return "Extensao nao permitida. Permitido: {$permitidas}";
        }

        return true;
    }

    /**
     * Validar tipo MIME
     */
    // GUIDEWAY CUSTOM - 17/03/2026 - Valida MIME type real do arquivo no servidor
public function validarMimeType($mimeType, $tmpPath = null)
{
    if ($tmpPath && file_exists($tmpPath)) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeReal = $finfo->file($tmpPath);
        if (!in_array($mimeReal, $this->config['allowed_mime_types'])) {
            return 'Tipo de arquivo nao permitido';
        }
        return true;
    }
    if (!in_array($mimeType, $this->config['allowed_mime_types'])) {
        return 'Tipo de arquivo nao permitido';
    }
    return true;
}


    /**
     * Validar tamanho
     */
    public function validarTamanho($tamanho)
    {
        if ($tamanho > $this->config['max_size']) {
            $maxMB = $this->config['max_size'] / (1024 * 1024);
            return "Arquivo muito grande. Maximo: {$maxMB}MB";
        }

        if ($tamanho <= 0) {
            return 'Arquivo vazio';
        }

        return true;
    }

    /**
     * Converter código de erro para mensagem
     */
    private function getUploadErrorMessage($errorCode)
    {
        $mensagens = [
            UPLOAD_ERR_INI_SIZE => 'Arquivo excede limite do servidor',
            UPLOAD_ERR_FORM_SIZE => 'Arquivo excede limite do formulario',
            UPLOAD_ERR_PARTIAL => 'Upload incompleto',
            UPLOAD_ERR_NO_FILE => 'Nenhum arquivo enviado',
            UPLOAD_ERR_NO_TMP_DIR => 'Pasta temporaria nao encontrada',
            UPLOAD_ERR_CANT_WRITE => 'Erro ao escrever no disco',
            UPLOAD_ERR_EXTENSION => 'Upload bloqueado por extensao PHP'
        ];

        return $mensagens[$errorCode] ?? 'Erro desconhecido no upload';
    }

    /**
     * Obter extensão do arquivo
     */
    public function getExtensao($nomeArquivo)
    {
        return strtolower(pathinfo($nomeArquivo, PATHINFO_EXTENSION));
    }
}
