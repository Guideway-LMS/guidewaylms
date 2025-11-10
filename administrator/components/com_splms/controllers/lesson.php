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
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\MVC\Controller\FormController;

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class SplmsControllerLesson extends FormController {

	public function __construct($config = array()) {
		parent::__construct($config);
	}

	protected function allowAdd($data = array()) {
		return parent::allowAdd($data);
	}

    protected function allowEdit($data = array(), $key = 'id') {
		$recordId = (int) isset($data[$key]) ? $data[$key] : 0;
		$user = Factory::getUser();
		// Zero record (id:0), return component edit permission by calling parent controller method
		if (!$recordId) {
			return parent::allowEdit($data, $key);
		}
		// Check edit on the record asset (explicit or inherited)
		if ($user->authorise('core.edit', 'com_splms.lesson.' . $recordId)) {
			return true;
		}
		// Check edit own on the record asset (explicit or inherited)
		if ($user->authorise('core.edit.own', 'com_splms.lesson.' . $recordId)){
			// Existing record already has an owner, get it
			$record = $this->getModel()->getItem($recordId);
			if (empty($record)) {
				return false;
			}
			// Grant if current user is owner of the record
			return $user->id == $record->created_by;
		} 
		return false;
	}

	 // Delete File
    public function delete_media() {

        $model      = $this->getModel();
        $input      = Factory::getApplication()->input;
        $filePath   = $input->post->get('filePath', NULL, 'STRING');
        $itemID     = $input->post->get('itemId', NULL, 'INT');

        $report = array();
        $report['status'] = false;

        $report['itemID'] = $itemID;

        if(isset($filePath) && $filePath) {
            $report['delete'] = $model->removeAttachmentByID($itemID);
            if(File::exists($filePath)) {
                // Delete thumb
                if (File::delete($filePath)) {
                    $report['status']   = true;
                    $report['message']  = Text::_('SPLMS_ATTACHMENT_SUCCESSFULLY_REMOVED');
                }

            } else {
                $report['status'] = false;
                $report['message']  = Text::_('SPLMS_ATTACHMENT_ISNOT_EXIST');
            }
        } else {
            $report['status'] = false;
            $report['message']  = Text::_('SPLMS_NO_ATTACHMENT_FOUND');
        }

        echo json_encode($report);
        die;
    }

}
