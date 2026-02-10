<?php
/**
* @package com_splms
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2024 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route; 

// ============================================================================
// 🔒 BLOQUEIO DE SEGURANÇA INTELIGENTE (GUIDEWAY LMS)
// ============================================================================
$app_guard   = Factory::getApplication();
$user_guard  = Factory::getUser();
$input_guard = $app_guard->input;
$view_guard  = $input_guard->getCmd('view');

// Se quiser testar se está no arquivo certo, descomente a linha abaixo:
// die('<h1>ESTOU NO ARQUIVO CERTO DO FRONTEND!</h1>');

// Lista de telas restritas
$views_restritas = array('lesson', 'submission', 'quiz', 'order');

// Verifica se é visitante tentando ver conteúdo restrito
if ($user_guard->guest && in_array($view_guard, $views_restritas)) {
    
    // 1. Captura URL para retorno
    $uri_atual = Uri::getInstance()->toString();
    $retorno   = base64_encode($uri_atual);
    
    // 2. Monta link de login
    $link_login = Route::_('index.php?option=com_users&view=login&return=' . $retorno, false);
    
    // 3. Avisa e Redireciona
    $app_guard->enqueueMessage('🔒 Para continuar, por favor faça login.', 'warning');
    $app_guard->redirect($link_login);
    
    // 4. MATA O PROCESSO (Impede o erro "topics on null")
    exit(); 
}
// ============================================================================


require_once JPATH_COMPONENT . '/helpers/helper.php';
HTMLHelper::_('jquery.framework');
$doc = Factory::getDocument();
$app = Factory::getApplication();
$params = $app->getParams('com_splms');

// Include JS
$doc->addScriptdeclaration('var splms_url="' . Uri::base() . '";');
$doc->addScript(Uri::root(true) . '/components/com_splms/assets/js/shuffle-5.1.1.js');
$doc->addScript( Uri::root(true) . '/components/com_splms/assets/js/splms.js' );

// Include CSS files
$doc->addStylesheet( Uri::root(true) . '/components/com_splms/assets/css/font-splms.css' );
$doc->addStylesheet( Uri::root(true) . '/components/com_splms/assets/css/splms.css' );
if(!$params->get('disable_styling', 0)) {
    $doc->addStylesheet( Uri::root(true) . '/components/com_splms/assets/css/styles.css' );
}

$languageFilePath = JPATH_ROOT . '/components/com_splms/helpers/language.php';

if (file_exists($languageFilePath))
{
    require_once $languageFilePath;
}

$controller = BaseController::getInstance('Splms');
$input = Factory::getApplication()->input;
$controller->execute($input->getCmd('task'));
$controller->redirect();