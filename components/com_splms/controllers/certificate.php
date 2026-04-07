<?php
/**
 * @package    Guideway LMS
 * @subpackage com_splms
 * @author     Michael & Guideway Team
 * GUIDEWAY CUSTOM - Persistência do Registro e Geração Automática do PDF (Versão 100% Blindada)
 */

defined('_JEXEC') or die;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;

class SplmsControllerCertificate extends BaseController
{
   public function generate()
{
    file_put_contents(JPATH_ROOT . '/images/controller.txt', 'ENTROU CONTROLLER');

    $app   = Factory::getApplication();
    $user  = Factory::getUser();
    $db    = Factory::getDbo();
    $input = $app->input;

    if ($user->guest) {
        $app->enqueueMessage('Você precisa estar logado.', 'warning');
        $app->redirect('index.php');
        return;
    }

    // 🔑 PRIMEIRO pega o course_id
    $course_id = $input->getInt('course_id');

    if (!$course_id) {
        $app->enqueueMessage('Curso inválido.', 'error');
        $app->redirect('index.php');
        return;
    }

    // 🔒 AGORA valida conclusão (depois de ter tudo pronto)
    BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_splms/models');
    $lessonsModel = BaseDatabaseModel::getInstance('Lessons', 'SplmsModel');

    $isCourseCompleted = false;

    if ($lessonsModel) {
        $isCourseCompleted = $lessonsModel::hasCompleted(
            $course_id,
            $user->id,
            'course'
        );
    }

    if (!$isCourseCompleted) {
        $app->enqueueMessage('Você ainda não concluiu este curso.', 'warning');
        $app->redirect('index.php?option=com_splms&view=course&id=' . (int)$course_id);
        return;
    }
    $course_id = $input->getInt('course_id');

    if (!$course_id) {
        die('course_id obrigatório.');
    }

    // 🔎 1. Verifica se já existe certificado
    $query = $db->getQuery(true)
        ->select('*')
        ->from($db->quoteName('#__splms_certificates'))
        ->where('userid = ' . (int) $user->id)
        ->where('course_id = ' . (int) $course_id);

    $db->setQuery($query);
    $item = $db->loadObject();

    // 🧠 2. Se NÃO existe → cria
    if (!$item) {

        // pega categoria
        $queryCat = $db->getQuery(true)
            ->select('category_id')
            ->from('#__splms_courses')
            ->where('id = ' . (int) $course_id);

        $db->setQuery($queryCat);
        $catid = (int) $db->loadResult();

        $hoje = Factory::getDate()->toSql();
        $hash = 'GW-' . date('Y') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));

        $cert = new stdClass();
        $cert->userid             = $user->id;
        $cert->course_id          = $course_id;
        $cert->coursescategory_id = $catid;
        $cert->certificate_no     = $hash;
        $cert->issue_date         = $hoje;
        $cert->created            = $hoje;
        $cert->created_by         = $user->id;
        $cert->modified           = $hoje;
        $cert->modified_by        = 0;
        $cert->published          = 1;

        $db->insertObject('#__splms_certificates', $cert, 'id');

        // recarrega item
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__splms_certificates'))
            ->where('id = ' . (int) $cert->id);

        $db->setQuery($query);
        $item = $db->loadObject();
    }

    if (!$item) {
        die('Erro ao gerar certificado.');
    }

    // 🔐 Segurança
    if ($item->userid != $user->id && !$user->authorise('core.admin')) {
        die('Acesso negado.');
    }

    // 🚀 Gera PDF
    $helperPath = JPATH_SITE . '/components/com_splms/helpers/CertificateHelper.php';

    if (!file_exists($helperPath)) {
        die('Helper não encontrado.');
    }

    require_once $helperPath;

    if (ob_get_length()) {
        ob_clean();
    }

    CertificateHelper::gerarPdf($item);
    exit;
}
//     public function generate()
//     {
//         file_put_contents(JPATH_ROOT . '/images/controller.txt', 'ENTROU CONTROLLER');
//         $app   = Factory::getApplication();
//         $user  = Factory::getUser();
//         $db    = Factory::getDbo();
//         $input = $app->input;

//         if ($user->guest) {
//             $app->enqueueMessage('Você precisa estar logado para gerar o certificado.', 'warning');
//             $app->redirect('index.php');
//             return;
//         }

//         $id            = $input->getInt('id'); 
//         $submission_id = $input->getInt('submission_id');

//         if (!$id && $submission_id) {
//             $course_id = $input->getInt('course_id');
            
//             // Tenta descobrir o ID do curso de 3 formas seguras
//             if (!$course_id) {
//                 try {
//                     $query = $db->getQuery(true)->select('id')->from('#__splms_courses')->where('id = ' . (int)$submission_id);
//                     $db->setQuery($query);
//                     if ($db->loadResult()) {
//                         $course_id = $submission_id;
//                     }
//                 } catch (Exception $e) { }

//                 if (!$course_id) {
//                     try {
//                         $query = $db->getQuery(true)
//                             ->select('course_id')
//                             ->from($db->quoteName('#__splms_course_orders'))
//                             ->where($db->quoteName('user_id') . ' = ' . (int)$user->id);
//                         $db->setQuery($query);
//                         $course_id = (int) $db->loadResult();
//                     } catch (Exception $e) { }
//                 }

//                 if (!$course_id) {
//                     try {
//                         $query = $db->getQuery(true)->select('id')->from('#__splms_courses')->where('published = 1')->order('id DESC');
//                         $db->setQuery($query);
//                         $course_id = (int) $db->loadResult();
//                     } catch (Exception $e) { }
//                 }
//             }

//             if ($course_id) {
//                 // Verifica duplicidade
//                 $existing_id = 0;
//                 try {
//                     $query = $db->getQuery(true)
//                         ->select('id')
//                         ->from($db->quoteName('#__splms_certificates'))
//                         ->where($db->quoteName('userid') . ' = ' . (int)$user->id)
//                         ->where($db->quoteName('course_id') . ' = ' . (int)$course_id);
//                     $db->setQuery($query);
//                     $existing_id = $db->loadResult();
//                 } catch (Exception $e) { }

//                 if ($existing_id) {
//                     $id = $existing_id;
//                 } else {
//                     // Busca a categoria do curso protegida contra erros de coluna
//                     $catid = 0;
//                     try {
//                         $queryCat = $db->getQuery(true)->select('category_id')->from('#__splms_courses')->where('id = ' . (int)$course_id);
//                         $db->setQuery($queryCat);
//                         $catid = (int) $db->loadResult();
//                     } catch (Exception $e) { }

//                     $hoje = Factory::getDate()->toSql();
//                     $hash = 'GW-' . date('Y') . '-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));

//                     $cert = new stdClass();
//                     $cert->userid             = $user->id;
//                     $cert->course_id          = $course_id;
//                     $cert->coursescategory_id = $catid; 
//                     $cert->certificate_no     = $hash;
//                     $cert->issue_date         = $hoje;
//                     $cert->created_by         = $user->id;
//                     $cert->created            = $hoje;
                    
//                     // CORREÇÃO: Informando os campos "modified" e "modified_by" que o banco exige!
//                     $cert->modified           = $hoje;
//                     $cert->modified_by        = 0;
                    
//                     $cert->published          = 1;

//                     try {
//                         $db->insertObject('#__splms_certificates', $cert, 'id');
//                         $id = $cert->id; 
//                     } catch (Exception $e) {
//                         $app->enqueueMessage('Erro final SQL ao inserir certificado: ' . $e->getMessage(), 'error');
//                         $app->redirect('index.php');
//                         return;
//                     }
//                 }
//             }
//         }

//         if (!$id) {
//             $app->enqueueMessage('Dados insuficientes para gerar o certificado.', 'error');
//             $app->redirect('index.php');
//             return;
//         }
// // GUIDEWAY CUSTOM - 17/03/2026 - Bloqueia acesso ao certificado se nao pertencer ao aluno logado
// $query = $db->getQuery(true)
//     ->select('id')
//     ->from($db->quoteName('#__splms_certificates'))
//     ->where($db->quoteName('id') . ' = ' . (int) $id)
//     ->where($db->quoteName('userid') . ' = ' . (int) $user->id)
//     ->where($db->quoteName('published') . ' = 1');
// $db->setQuery($query);
// $certificadoValido = $db->loadResult();

// if (!$certificadoValido && !$user->authorise('core.admin')) {
//     $app->redirect('index.php?option=com_splms&view=validate&erro=acesso_negado');
//     return;
// }

//         $model = $this->getModel('certificate');
//         $input->set('id', $id); 
//         $item = $model->getItem($id);

//         if ($item) {
//             if ($item->userid != $user->id && !$user->authorise('core.admin')) {
//                 $app->enqueueMessage('Você não tem permissão para visualizar este certificado.', 'error');
//                 $app->redirect('index.php');
//                 return;
//             }

//             $helperPath = JPATH_SITE . '/components/com_splms/helpers/CertificateHelper.php';
            
//             if (file_exists($helperPath)) {
//                 require_once $helperPath;
                
//                 if (ob_get_length()) {
//                     ob_clean();
//                 }
                
//                 CertificateHelper::gerarPdf($item);
//                 exit; 
//             } else {
//                 $app->enqueueMessage('Helper de Certificado não encontrado.', 'error');
//             }
//         } else {
//             $app->enqueueMessage('Certificado não encontrado no sistema.', 'error');
//         }

//         $app->redirect('index.php');
//     }
}
