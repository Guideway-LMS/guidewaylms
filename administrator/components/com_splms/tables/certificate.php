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
use Joomla\CMS\Table\Table;

class SplmsTableCertificate extends Table {

  public function __construct(&$db) {
    // Aponta para a nossa nova tabela de templates de certificados
    parent::__construct('#__splms_certificate_templates', 'id', $db);
  }

  public function store($updateNulls = false) {
    $date = Factory::getDate()->toSql();

    // Se for um novo template e a data estiver vazia, preenche com a data atual
    if (!$this->id) {
      if (empty($this->created_at)) {
        $this->created_at = $date;
      }
    }

    return parent::store($updateNulls);
  }

  public function check() {
    // Garante que o template tenha um título antes de salvar no banco
    if (empty($this->title)) {
      $this->setError('O nome do template é obrigatório.');
      return false;
    }

    return true;
  }

}