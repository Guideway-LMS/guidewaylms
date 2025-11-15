<?php
/**
 * Plugin System - GW Progress (AJAX endpoint)
 * Exemplo seguro para salvar progresso de lição/curso via com_ajax
 */

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\Response\JsonResponse;
use Joomla\Database\DatabaseInterface;

class PlgSystemGwprogress extends CMSPlugin
{
    /**
     * Método chamado pelo com_ajax:
     * URL: index.php?option=com_ajax&plugin=gwprogress&group=system&format=json
     */
    public function onAjaxGwprogress()
    {
        try {
            $app   = Factory::getApplication();
            $input = $app->input;

            // (1) Requer usuário logado (ajuste conforme a política do seu projeto)
            $user = Factory::getUser();
            if ($user->guest) {
                return new JsonResponse(null, JText::_('PLG_SYSTEM_GWPROGRESS_ERR_LOGIN_REQUIRED'), true);
            }

            // (2) CSRF token (se for POST de formulário com token Joomla)
            // if (!\JSession::checkToken('post')) {
            //     return new JsonResponse(null, JText::_('JINVALID_TOKEN'), true);
            // }

            // (3) Coleta e valida entradas
            $userId   = (int) $input->get('user_id', 0, 'int');
            $lessonId = (int) $input->get('lesson_id', 0, 'int');
            $progress = (float) $input->get('progress', 0, 'float'); // 0.00 até 100.00 (ou 0..1, ajuste se preferir)
            $status   = (string) $input->get('status', 'Nao_iniciado', 'cmd'); // 'Iniciado','Concluido','Nao_iniciado'

            if ($userId <= 0 || $lessonId <= 0) {
                return new JsonResponse(null, JText::_('PLG_SYSTEM_GWPROGRESS_ERR_INVALID_PARAMS'), true);
            }

            // (4) Normaliza limites
            if ($progress < 0)   { $progress = 0.0; }
            if ($progress > 100) { $progress = 100.0; }

            // (5) Segurança: impede atualizar progresso de outro usuário (caso usem user_id do request)
            if ((int)$user->id !== $userId) {
                return new JsonResponse(null, JText::_('PLG_SYSTEM_GWPROGRESS_ERR_FORBIDDEN'), true);
            }

            /** @var DatabaseInterface $db */
            $db = Factory::getContainer()->get(DatabaseInterface::class);

            // (6) Monta nome da tabela com prefixo (#__)
            $table = $db->quoteName('#__splms_course_progress');

            // (7) Tenta UPDATE primeiro
            $query = $db->getQuery(true)
                ->update($table)
                ->set($db->quoteName('status') . ' = ' . $db->quote($status))
                ->set($db->quoteName('progress') . ' = ' . $db->quote($progress))
                ->set($db->quoteName('updated_at') . ' = CURRENT_TIMESTAMP')
                ->where($db->quoteName('user_id') . ' = ' . (int)$userId)
                ->where($db->quoteName('lesson_id') . ' = ' . (int)$lessonId);

            $db->setQuery($query);
            $db->execute();

            if ($db->getAffectedRows() === 0) {
                // (8) Se não existia, faz INSERT
                $columns = ['user_id', 'lesson_id', 'status', 'progress', 'updated_at'];
                $values  = [
                    (int)$userId,
                    (int)$lessonId,
                    $db->quote($status),
                    $db->quote($progress),
                    'CURRENT_TIMESTAMP',
                ];

                $query = $db->getQuery(true)
                    ->insert($table)
                    ->columns(array_map([$db, 'quoteName'], $columns))
                    ->values(implode(',', $values));

                $db->setQuery($query);
                $db->execute();
            }

            return new JsonResponse(['ok' => true, 'message' => 'Progress saved']);
        } catch (\Throwable $e) {
            return new JsonResponse(null, $e->getMessage(), true);
        }
    }
}
