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
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Toolbar\ToolbarHelper;

class SplmsViewCertificate extends HtmlView {

	protected $form;
	protected $item;
	protected $canDo;
	protected $id;

	public function display($tpl = null)
	{
		$this->form = $this->get('Form');

		// GUIDEWAY FIX — busca o item diretamente do banco para evitar erro 500
		// quando getItem() retorna false por asset_id NULL (Joomla 5.x)
		$db    = Factory::getDbo();
		$input = Factory::getApplication()->input;
		$id    = $input->getInt('id');

		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__splms_certificates'))
			->where($db->quoteName('id') . ' = ' . (int) $id);
		$db->setQuery($query);
		$this->item = $db->loadObject();

		// Se o item não existir, cria objeto vazio para novo registro
		if (!$this->item) {
			$this->item     = new \stdClass();
			$this->item->id = 0;
		}

		$this->id = $this->item->id;

		// GUIDEWAY FIX — trata falha de permissões sem travar a tela
		try {
			$this->canDo = SplmsHelper::getActions($this->item->id);
		} catch (\Exception $e) {
			$this->canDo = new \Joomla\CMS\Object\CMSObject;
			$this->canDo->set('core.edit', true);
			$this->canDo->set('core.create', true);
		}

		$this->addToolBar();
		parent::display($tpl);
	}

	protected function addToolBar()
	{
		$input = Factory::getApplication()->input;

		// Esconde o menu principal do administrador
		$input->set('hidemainmenu', true);

		$isNew = ($this->item->id == 0);

		ToolbarHelper::title(
			Text::_('COM_SPLMS_TITLE_CERTIFICATES') . ': ' . ($isNew ? Text::_('COM_SPSPLMS_NEW') : Text::_('COM_SPSPLMS_EDIT')),
			'pencil'
		);

		if ($isNew) {
			if ($this->canDo->get('core.create')) {
				ToolbarHelper::apply('certificate.apply', 'JTOOLBAR_APPLY');
			}
			ToolbarHelper::cancel('certificate.cancel', 'JTOOLBAR_CANCEL');
		} else {
			if ($this->canDo->get('core.edit')) {
				ToolbarHelper::apply('certificate.apply', 'JTOOLBAR_APPLY');
				ToolbarHelper::save('certificate.save', 'JTOOLBAR_SAVE');
			}
			ToolbarHelper::cancel('certificate.cancel', 'JTOOLBAR_CLOSE');
		}
	}
}