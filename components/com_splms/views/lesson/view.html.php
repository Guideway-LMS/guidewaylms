<?php
/**
* @package com_splms
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2024 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

defined ('_JEXEC') or die('Resticted Aceess');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;

class SplmsViewLesson extends HtmlView{

  protected $item;
  protected $params;
  protected $submission; // Variável para guardar o envio

  function display($tpl = null) {
    
    // 1. Redirecionamento de Segurança (Visitantes)
    $user = Factory::getUser();
    $app  = Factory::getApplication();

    if ($user->guest) {
        $uri = Uri::getInstance();
        $return = base64_encode($uri->toString());
        $loginUrl = Route::_('index.php?option=com_users&view=login&return=' . $return, false);
        $app->enqueueMessage('Por favor, faça login para acessar esta aula.', 'notice');
        $app->redirect($loginUrl);
        return;
    }

    $this->item = $this->get('Item');
    $this->params = $app->getParams();
    
    // Load Models
    BaseDatabaseModel::addIncludePath(JPATH_SITE.'/components/com_splms/models');
    $courses_model  = BaseDatabaseModel::getInstance('Courses', 'SplmsModel');
    $teachers_model = BaseDatabaseModel::getInstance('Teachers', 'SplmsModel');
    $lessons_model  = BaseDatabaseModel::getInstance('Lessons', 'SplmsModel');

    $this->user = $user;

    if ($this->item) {
        $this->lessons = $lessons_model->getLessons($this->item->course_id);
        $this->teacher = $teachers_model->getTeacher($this->item->teacher_id);
        $this->courese = $courses_model->getCourse($this->item->course_id);
        $this->isAuthorised = $courses_model->getIsbuycourse($this->user->id, $this->item->course_id);
        $this->has_complete_lesson = $lessons_model->hasCompleted($this->item->id, $this->user->id, 'lesson');

        // --- NOVO: Verifica se já existe submissão para esta lição ---
        // Isso permite mostrar o status "Em Correção" em vez do formulário
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__splms_submissions'))
            ->where($db->quoteName('user_id') . ' = ' . (int)$user->id)
            ->where($db->quoteName('lesson_id') . ' = ' . (int)$this->item->id);
        $db->setQuery($query);
        $this->submission = $db->loadObject();
        // -------------------------------------------------------------

        if($this->item->lesson_type > 0 && !$this->isAuthorised && $this->courese->price > 0) {
          $output  = '<div class="alert alert-warning">';
          $output .= '<p>' . Text::_('COM_SPLMS_LESSON_NO_ACCESS') .'</p>';
          $output .= '<a href="' . $this->courese->url . '">' . $this->courese->title .'</a>';
          $output .= '</div>';
          echo $output;
          return; 
        }
    } else {
        throw new \Exception('Lição não encontrada', 404);
    }
    
    if (isset($this->teacher) && $this->teacher) {
      $this->teacher_description = strip_tags($this->teacher->description);
      if (strlen($this->teacher_description) > 400) {
        $descriptionCut = substr($this->teacher_description, 0, 340);
        $this->teacher_description = substr($descriptionCut, 0, strrpos($descriptionCut, ' ')).'...';
      }
    }

    // Meta Tags
    if ($this->item) {
        $itemMeta = array();
        $itemMeta['title'] = $this->item->title;
        $cleanText = $this->item->description;
        $itemMeta['metadesc'] = HTMLHelper::_('string.truncate', OutputFilter::cleanText($cleanText), 155);
        if ($this->item->vdo_thumb) {
          $itemMeta['image'] = Uri::base() . $this->item->vdo_thumb;
        }
        SplmsHelper::itemMeta($itemMeta);
    }
    
    parent::display($tpl);
  }
}