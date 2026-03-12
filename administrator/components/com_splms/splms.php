<?php
/**
 * @package com_splms
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2024 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

// No Direct Access
defined ('_JEXEC') or die('Resticted Aceess');

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Access\Exception\NotAllowed;

// 1. Verifica Permissão de Acesso
if (!Factory::getUser()->authorise('core.manage', 'com_splms')){
  throw new NotAllowed(Text::_('JERROR_ALERTNOAUTHOR'), 403);
}

// --- 🔔 INÍCIO DA NOTIFICAÇÃO DE PENDÊNCIAS (ADICIONADO) ---
// Esta lógica roda sempre que o administrador entra no componente SP LMS
try {
    $db = Factory::getDbo();
    
    // Conta quantos trabalhos estão com status 0 (Pendente)
    $query = $db->getQuery(true)
        ->select('COUNT(*)')
        ->from($db->quoteName('#__splms_submissions'))
        ->where($db->quoteName('status') . ' = 0'); // 0 = Pendente
    
    $db->setQuery($query);
    $pendingCount = (int) $db->loadResult();
    
    // Se houver pendências, mostra a tarja amarela no topo do painel
    if ($pendingCount > 0) {
        $app = Factory::getApplication();
        $msg = '<span class="icon-flag" aria-hidden="true"></span> '; // Ícone de bandeira
        $msg .= '<strong>Atenção Professor:</strong> Existem <strong>' . $pendingCount . '</strong> trabalho(s) pendente(s) de correção.';
        
        // 'warning' cria a caixa amarela padrão do Joomla
        $app->enqueueMessage($msg, 'warning'); 
    }
} catch (Exception $e) {
    // Silêncio em caso de erro no banco para não travar o painel
}
// --- FIM DA NOTIFICAÇÃO ---


HTMLHelper::_('jquery.framework');
$doc = Factory::getDocument();
$doc->addStylesheet( Uri::root(true) . '/administrator/components/com_splms/assets/css/splms.css' );
$doc->addScript( Uri::root(true) . '/administrator/components/com_splms/assets/js/main.js' );

// Require helper file
JLoader::register('SplmsHelper', JPATH_COMPONENT . '/helpers/splms.php');
$controller = BaseController::getInstance('Splms');
$controller->execute(Factory::getApplication()->input->get('task'));
$controller->redirect();