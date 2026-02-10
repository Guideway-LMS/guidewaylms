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
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route; // Vital para o redirecionamento

class SplmsViewLesson extends HtmlView{

  protected $item;
  protected $params;

  function display($tpl = null) {
    
    // ========================================================================
    // 🔒 1. REDIRECIONAMENTO DE SEGURANÇA (O PULO DO GATO)
    // ========================================================================
    // Isso TEM que ser a primeira coisa. Se o usuário não estiver logado,
    // nós o mandamos para o login ANTES que o sistema tente carregar a lição.
    // Isso evita o erro "Attempt to assign property 'topics' on null".
    $user = Factory::getUser();
    $app  = Factory::getApplication();

    if ($user->guest) {
        // 1. Pega a URL atual (para voltar aqui depois)
        $uri = Uri::getInstance();
        $return = base64_encode($uri->toString());
        
        // 2. Gera o link de login
        $loginUrl = Route::_('index.php?option=com_users&view=login&return=' . $return, false);
        
        // 3. Redireciona e PARA A EXECUÇÃO IMEDIATAMENTE
        $app->enqueueMessage('Por favor, faça login para acessar esta aula.', 'notice');
        $app->redirect($loginUrl);
        return; // <--- Isso impede que o código abaixo (que causa o erro) rode
    }
    // ========================================================================


    // Daqui para baixo, só executa se estiver logado.
    
    // Assign data to the view
    // É AQUI (Linha ~55) que o erro acontecia. Agora estamos protegidos.
    $this->item = $this->get('Item'); 

    $this->params = $app->getParams();
    $menus = Factory::getApplication()->getMenu();
    $menu = $menus->getActive();

    //Joomla Component Helper & Get LMS Params
    $params = ComponentHelper::getParams('com_splms');

    // Load Lessons model
    BaseDatabaseModel::addIncludePath(JPATH_SITE.'/components/com_splms/models');

    // Check for errors.
    if (count($errors = $this->get('Errors'))) {
      throw new \Exception(implode("\n", $errors), 500);
      return false;
    }

    // Load Models
    $courses_model  = BaseDatabaseModel::getInstance('Courses', 'SplmsModel');
    $teachers_model = BaseDatabaseModel::getInstance('Teachers', 'SplmsModel');
    $lessons_model  = BaseDatabaseModel::getInstance('Lessons', 'SplmsModel');

    // Get User (Já temos na variável $user lá em cima, mas mantemos compatibilidade)
    $this->user = $user;

    // Proteção extra: Só tenta carregar dados se o item realmente existir
    if ($this->item) {
        $this->lessons = $lessons_model->getLessons($this->item->course_id);
        $this->teacher = $teachers_model->getTeacher($this->item->teacher_id);
        $this->courese = $courses_model->getCourse($this->item->course_id);
        $this->isAuthorised = $courses_model->getIsbuycourse($this->user->id, $this->item->course_id);
        $this->has_complete_lesson = $lessons_model->hasCompleted($this->item->id, $this->user->id, 'lesson');

        // check authorised or free course
        if($this->item->lesson_type > 0 && !$this->isAuthorised && $this->courese->price > 0) {
          $output  = '<div class="alert alert-warning">';
          $output .= '<p>' . Text::_('COM_SPLMS_LESSON_NO_ACCESS') .'</p>';
          $output .= '<a href="' . $this->courese->url . '">' . $this->courese->title .'</a>';
          $output .= '</div>';

          echo $output;
          return; 
        }
    } else {
        // Se cair aqui, a lição não existe (Erro 404 real)
        throw new \Exception(Text::_('JERROR_LAYOUT_PAGE_NOT_FOUND'), 404);
    }
    
    if (isset($this->teacher) && $this->teacher) {
      $this->teacher_description = strip_tags($this->teacher->description);
      if (strlen($this->teacher_description) > 400) {
        // truncate string
        $descriptionCut = substr($this->teacher_description, 0, 340);
        // make sure it ends in a word so assassinate doesn't become ass...
        $this->teacher_description = substr($descriptionCut, 0, strrpos($descriptionCut, ' ')).'...';
        // Show Desription
        $this->teacher_description = $this->teacher_description;
      }else{
        $this->teacher_description = $this->teacher_description;
      }
    }

    //Generate Item Meta
    if ($this->item) {
        $itemMeta                       = array();
        $itemMeta['title']              = $this->item->title;
        $cleanText                      = $this->item->description;
        $itemMeta['metadesc']   = HTMLHelper::_('string.truncate', OutputFilter::cleanText($cleanText), 155);
        if ($this->item->vdo_thumb) {
          $itemMeta['image']      = Uri::base() . $this->item->vdo_thumb;
        }
        SplmsHelper::itemMeta($itemMeta);
    }
    
    parent::display($tpl);
  }

}