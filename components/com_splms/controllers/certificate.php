<?php
/**
 * @package     Guideway LMS
 * @subpackage  com_splms
 * @author      Michael
 */

defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;

class SplmsControllerCertificate extends BaseController
{
    /**
     * Gera o certificado em PDF
     */
    public function generate()
    {
        // 1. Verificar se o usuário está logado
        $user = Factory::getUser();
        if ($user->guest) {
            Factory::getApplication()->enqueueMessage('Você precisa estar logado para gerar o certificado.', 'warning');
            return;
        }

        // 2. Coletar o ID (Usamos 'id' para bater com a URL do Joomla que vimos antes)
        $input = Factory::getApplication()->input;
        $id = $input->getInt('id');

        if (!$id) {
            Factory::getApplication()->enqueueMessage('ID de certificado inválido.', 'error');
            return;
        }

        // 3. Buscar dados no Banco de Dados
        // Como você corrigiu o usuário no painel, vamos usar o Model padrão do SP LMS para pegar o item
        $model = $this->getModel('certificate');
        $item = $model->getItem($id);

        if ($item) {
            // Garantir que o aluno só veja o próprio certificado (Segurança)
            if ($item->userid != $user->id && !$user->authorise('core.admin')) {
                Factory::getApplication()->enqueueMessage('Você não tem permissão para visualizar este certificado.', 'error');
                return;
            }

            // 4. Carregar o Helper e Gerar o PDF
            $helperPath = JPATH_SITE . '/components/com_splms/helpers/CertificateHelper.php';
            
            if (file_exists($helperPath)) {
                require_once $helperPath;
                
                // Chamamos a função gerarPdf que configuramos com o TCPDF
                CertificateHelper::gerarPdf($item);
            } else {
                Factory::getApplication()->enqueueMessage('Helper de Certificado não encontrado.', 'error');
            }
        } else {
            Factory::getApplication()->enqueueMessage('Certificado não encontrado no sistema.', 'error');
        }

        // Interrompe a execução para o PDF ser renderizado
        Factory::getApplication()->close();
    }
}