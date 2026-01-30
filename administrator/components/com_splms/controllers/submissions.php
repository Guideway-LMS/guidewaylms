<?php
/**
 * @package     com_splms
 * @license     GNU/GPLv2 or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

class SplmsControllerSubmissions extends BaseController
{
    public function saveGrade()
    {
        // 1. Verificações de segurança
        if (!JSession::checkToken()) {
            $this->setRedirect(Route::_('index.php?option=com_splms&view=submissions', false), 'Token Inválido', 'error');
            return false;
        }

        $input = Factory::getApplication()->input;
        $submission_id = $input->getInt('submission_id');
        $grade         = $input->getFloat('grade');
        $feedback      = $input->getString('feedback', '', 'STRING');
        
        // --- CONFIGURAÇÃO DA REGRA DE APROVAÇÃO ---
        $passing_grade = 60; // Nota de corte
        // ------------------------------------------

        if ($submission_id > 0) {
            $db = Factory::getDbo();

            // 2. Passo Vital (NOVO): Buscar quem é o aluno e qual é a lição
            // Sem isso, não conseguimos atualizar a barra de progresso dele
            $query = $db->getQuery(true)
                ->select($db->quoteName(array('user_id', 'lesson_id')))
                ->from($db->quoteName('#__splms_submissions'))
                ->where($db->quoteName('id') . ' = ' . (int) $submission_id);
            $db->setQuery($query);
            $submission = $db->loadObject();

            if (!$submission) {
                $this->setRedirect(Route::_('index.php?option=com_splms&view=submissions', false), 'Envio não encontrado.', 'error');
                return false;
            }

            // 3. Atualizar a tabela de Submissões (Registrar a Nota)
            $queryUpdate = $db->getQuery(true)
                ->update($db->quoteName('#__splms_submissions'))
                ->set($db->quoteName('grade') . ' = ' . (float) $grade)
                ->set($db->quoteName('feedback') . ' = ' . $db->quote($feedback))
                ->set($db->quoteName('status') . ' = 1') // 1 = Corrigido
                ->where($db->quoteName('id') . ' = ' . (int) $submission_id);
            
            $db->setQuery($queryUpdate);
            
            try {
                $db->execute();

                // 4. LÓGICA DE CONCLUSÃO E PROGRESSO (AQUI ESTÁ A TAREFA)
                // Regra: Se Nota >= 60 -> Status 1 (Concluído)
                //        Se Nota < 60  -> Status 2 (Pendente/Reprovado)
                
                $status_aula = ($grade >= $passing_grade) ? 1 : 2;
                
                // Atualiza a tabela de progresso do aluno (useritems)
                $queryProgress = $db->getQuery(true)
                    ->update($db->quoteName('#__splms_useritems'))
                    ->set($db->quoteName('published') . ' = ' . (int) $status_aula)
                    ->where($db->quoteName('user_id') . ' = ' . (int) $submission->user_id)
                    ->where($db->quoteName('item_id') . ' = ' . (int) $submission->lesson_id)
                    ->where($db->quoteName('item_type') . ' = ' . $db->quote('lesson'));

                $db->setQuery($queryProgress);
                $db->execute();

                // Mensagens de feedback para o professor saber o que aconteceu
                $msgType = 'message';
                $msgText = 'Trabalho avaliado com sucesso!';
                
                if ($status_aula == 1) {
                    $msgText .= ' Nota suficiente: Aula marcada como CONCLUÍDA.';
                } else {
                    $msgType = 'warning';
                    $msgText .= ' Nota abaixo da média (' . $passing_grade . '): Aula mantida como PENDENTE.';
                }

                $this->setRedirect(Route::_('index.php?option=com_splms&view=submissions', false), $msgText, $msgType);
                return true;

            } catch (Exception $e) {
                $this->setRedirect(Route::_('index.php?option=com_splms&view=submissions', false), 'Erro ao salvar: ' . $e->getMessage(), 'error');
                return false;
            }
        }

        $this->setRedirect(Route::_('index.php?option=com_splms&view=submissions', false), 'ID inválido.', 'error');
        return false;
    }
}