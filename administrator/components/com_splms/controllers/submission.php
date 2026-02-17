<?php
/**
 * @package     com_splms
 * @license     GNU/GPLv2 or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Component\ComponentHelper;

// ATENÇÃO: O nome da classe deve ser no SINGULAR (Submission), sem 's' no final.
class SplmsControllerSubmission extends BaseController
{
    public function save()
    {
        // 1. Verificação de Segurança
        if (!\Joomla\CMS\Session\Session::checkToken()) {
            $this->setRedirect('index.php', 'Sessão expirada. Recarregue a página.', 'error');
            return false;
        }

        $app   = Factory::getApplication();
        $input = $app->input;

        // 2. Recebe os dados do formulário
        $data = $input->post->get('jform', array(), 'array');
        
        // Garante que pegamos o ID corretamente (pode vir como 'id' ou 'submission_id')
        $submissionId = isset($data['id']) ? (int)$data['id'] : 0;
        if (!$submissionId && isset($data['submission_id'])) {
            $submissionId = (int)$data['submission_id'];
        }
        
        // URL para onde voltamos após salvar
        $redirectUrl = 'index.php?option=com_splms&view=submissions';

        if (!$submissionId) {
            $this->setRedirect($redirectUrl, 'ID do trabalho não encontrado.', 'error');
            return false;
        }

        // ====================================================================
        // 🟢 SPRINT 9: LÓGICA DE APROVAÇÃO (Nota de Corte)
        // ====================================================================
        
        $grade = isset($data['grade']) ? (float)$data['grade'] : 0;
        $params = ComponentHelper::getParams('com_splms');
        
        // Pega a nota de corte (padrão 60, mas ajusta para 6.0 se sua escala for pequena)
        // DICA: Se você usa notas de 0 a 10, mude o 60 abaixo para 6
        $passingScore = (float)$params->get('assignment_passing_score', 60);

        if ($grade >= $passingScore) {
            $data['status'] = 1; // APROVADO
        } else {
            $data['status'] = 2; // REPROVADO
        }
        
        // ====================================================================

        // 3. Processo de Salvamento
        Table::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_splms/tables');
        $table = Table::getInstance('Submission', 'SplmsTable');

        try {
            // Carrega dados antigos (Corrige erro "user_id default value")
            if (!$table->load($submissionId)) {
                throw new Exception('Registro não encontrado.');
            }

            // Injeta os novos dados
            if (!$table->bind($data)) {
                throw new Exception($table->getError());
            }

            if (!$table->check()) {
                throw new Exception($table->getError());
            }

            // Salva e Dispara E-mail
            if (!$table->store()) {
                throw new Exception($table->getError());
            }

            // Mensagem de Sucesso
            $statusText = ($data['status'] == 1) ? 'APROVADO' : 'REPROVADO';
            $msg = "Avaliação salva! Status: $statusText (Nota: $grade). O aluno foi notificado.";
            $type = 'success';

        } catch (Exception $e) {
            $msg = 'Erro ao salvar: ' . $e->getMessage();
            $type = 'error';
        }

        // 4. Redireciona para a lista
        $this->setRedirect(Route::_($redirectUrl, false), $msg, $type);
    }
}