<?php
/**
 * @package     com_splms
 * @license     GNU/GPLv2 or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;

class SplmsControllerSubmissions extends BaseController
{
    public function saveGrade()
    {
        // 1. Verificações de segurança
        JSession::checkToken() or die('Invalid Token');
        
        $input = Factory::getApplication()->input;
        $user  = Factory::getUser();

        // 2. Recebe os dados
        $id       = $input->getInt('submission_id');
        $grade    = $input->get('grade', 0, 'FLOAT'); // Aceita decimais
        $feedback = $input->get('feedback', '', 'RAW'); // Texto livre

        // --- INICIO DA SUA IMPLEMENTAÇÃO (Lógica de Aprovação) ---
        // Pega as configurações globais que definimos no XML
        $params = \Joomla\CMS\Component\ComponentHelper::getParams('com_splms');
        
        // Busca a nota de corte (60). Se não achar nada, usa 60 por segurança.
        $passingScore = $params->get('assignment_passing_score', 60);

        // Se a nota for maior ou igual a 60, status 1 (Aprovado). Senão, status 2 (Reprovado).
        $status = ($grade >= $passingScore) ? 1 : 2;

        // 3. Atualiza o Banco
        if ($id) {
            $db = Factory::getDbo();
            $query = $db->getQuery(true);

            $fields = array(
                $db->quoteName('grade') . ' = ' . (float)$grade,
                $db->quoteName('feedback') . ' = ' . $db->quote($feedback),
                $db->quoteName('status') . ' = ' . (int)$status // Status dinâmico (Aprovado/Reprovado)
            );

            $conditions = array(
                $db->quoteName('id') . ' = ' . (int)$id
            );

            $query->update($db->quoteName('#__splms_submissions'))
                  ->set($fields)
                  ->where($conditions);

            $db->setQuery($query);

            try {
                $db->execute();
                $this->setRedirect('index.php?option=com_splms&view=submissions', 'Avaliação salva com sucesso!', 'message');
            } catch (Exception $e) {
                $this->setRedirect('index.php?option=com_splms&view=submissions', 'Erro ao salvar: ' . $e->getMessage(), 'error');
            }
        } else {
            $this->setRedirect('index.php?option=com_splms&view=submissions', 'ID inválido.', 'error');
        }
    }
}