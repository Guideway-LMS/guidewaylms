<?php
/**
 * @package com_splms
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2024 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

// No Direct Access
defined ('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\FormController;

class SplmsControllerCertificate extends FormController {

  public function __construct($config = array()) {
    parent::__construct($config);
  }

  protected function allowAdd($data = array()) {
    return parent::allowAdd($data);
  }

  protected function allowEdit($data = array(), $key = 'id') {
    $id = isset( $data[ $key ] ) ? $data[ $key ] : 0;
    if( !empty( $id ) ) {
      return Factory::getUser()->authorise( "core.edit", "com_splms.certificate." . $id );
    }
  }

  // === INJEÇÃO GUIDEWAY: CONTROLE DE TEMPLATES E CONVERSÃO DE DADOS ===
  public function save($key = null, $urlVar = null)
  {
      $app  = Factory::getApplication();
      $user = Factory::getUser();
      $db   = Factory::getDbo();
      
      // Pega os dados que vieram do formulário XML (jform)
      $data = $app->input->post->get('jform', array(), 'array');
      $isNew = empty($data['id']);

      // Se não for um Super Admin, aplicamos as regras para o Professor
      if (!$user->authorise('core.admin')) {
          
          // REGRA 1: Trava de 5 templates por Professor (Apenas na criação de um NOVO)
          if ($isNew) {
              $query = $db->getQuery(true)
                  ->select('COUNT(*)')
                  ->from($db->quoteName('#__splms_certificate_templates'))
                  ->where($db->quoteName('teacher_id') . ' = ' . (int)$user->id);
              
              $db->setQuery($query);
              $count = (int) $db->loadResult();

              if ($count >= 5) {
                  $app->enqueueMessage('Atenção: Você atingiu o limite máximo de 5 templates de certificado.', 'warning');
                  $this->setRedirect('index.php?option=com_splms&view=certificates');
                  return false; // Bloqueia o salvamento e volta para a lista
              }

              // REGRA 2: Força o ID do Professor como dono do template
              $data['teacher_id'] = $user->id;
          }
      }

      // REGRA 3: Converte os múltiplos cursos selecionados num formato JSON para salvar no banco
      if (isset($data['associated_courses']) && is_array($data['associated_courses'])) {
          $data['associated_courses'] = json_encode($data['associated_courses']);
      } elseif (empty($data['associated_courses'])) {
          // Se não selecionar nenhum curso, salva um array vazio
          $data['associated_courses'] = json_encode([]);
      }

      // Devolve os dados manipulados para o objeto POST para o Joomla seguir o fluxo normal de salvamento
      $app->input->post->set('jform', $data);

      return parent::save($key, $urlVar);
  }
  // =====================================================================

}