<?php
/**
* @package com_splms
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2024 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\MVC\Controller\BaseController;

// No Direct Access
defined ('_JEXEC') or die('Resticted Aceess');

class SplmsControllerTeachers extends BaseController {

	public function getModel($name = 'Teachers', $prefix = 'SplmsModel', $config = array('ignore_request' => true)) {
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}

	public function contact(){

		$app 	= Factory::getApplication();
		$input 	= $app->input;
		$output = array();
		
		$componentParams	= ComponentHelper::getParams('com_splms');
		$isCaptchaEnabled 	= (int) $componentParams->get('teacher_contact_recaptcha');

		if($isCaptchaEnabled){
			try {
				PluginHelper::importPlugin('captcha');
				$result = $app->triggerEvent('onCheckAnswer', [ $input->get('g-recaptcha-response') ]);
				if (!in_array(true, $result, true)) {
					$output['status']	= false;
					$output['content']	= Text::_('ERROR_RECAPTCHA_V2');
					echo json_encode($output);
					die();
				}
			} catch (\Exception $e) {
				$output['status']	= false;
				$output['content']	= $e->getMessage();
				echo json_encode($output);
				die();
			}
		}

		$mail  		= Factory::getMailer();

		$name 		= $input->post->get('name', NULL, 'STRING');
		$email 		= $input->post->get('email', NULL, 'STRING');
		$phone 		= $input->post->get('phone', NULL, 'STRING');
		$message 	= $input->post->get('message', NULL, 'STRING');
		$subject 	= $input->post->get('subject', NULL, 'STRING');
		$recipient	= base64_decode($input->post->get('teacher_email', NULL, 'STRING'));

		//message body
		$visitorip      = isset($_SERVER['HTTP_X_FORWARDED_FOR']) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
		
		// get site name 
		$site_name 		= isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : '';
		$subject 		= $subject . '  Email Address: ' . $email . '  ' . $site_name;


		$msg  = '';
		$msg .= '<p><span>Name : ' . $name .'</span></p>';
		$msg .= '<p><span>Phone : ' . $phone .'</span></p>';
		$msg .= '<p><span>Sender IP : ' . $visitorip .'</span></p>';
		$msg .= '<p><span>Email : ' . $email .'</span></p> <br />';
		$msg .= '<p><span>Message :</span> <br />' . $message .'</p>';
		

		// Sent email
		$sender = array($email, $name);
		$mail->setSender($sender);
		$mail->addRecipient($recipient);
		$mail->setSubject($subject);
		$mail->isHTML(true);
		$mail->Encoding = 'base64';
		$mail->setBody($msg);

		$output['status'] = false;
		if ($mail->Send()) {
			$output['status'] = true;
			$output['content'] = Text::_('COM_SPLMS_TEACHER_CONTACT_SUCCESS');
		} else {
			$output['content'] = Text::_('COM_SPLMS_TEACHER_CONTACT_ERROR');
		}

		echo json_encode($output);
		die();
	}

	public function toggleFollow() {
		$input = Factory::getApplication()->input;
		$user = Factory::getUser();
		$model = $this->getModel('Teacher');
		$teacher = (int) $input->get('teacher', 0, 'INT');
		$follower = new stdClass();
		if($user->id) {
			$follower = $model->toggleFollow($teacher);
			if (!empty($follower))
			{
				$follower->text = Text::_('COM_SPLMS_TEACHER_FOLLOW_STATUS_' . $follower->status);
				$follower->success = true;
			}
			
		} else {
			$follower->message = Text::_('COM_SPLMS_TEACHER_LOGIN_TO_FOLLOW');
			$follower->success = false;
		}
		echo json_encode($follower);
		die();
	}

	public function followers() {
		$input = Factory::getApplication()->input;
		$user = Factory::getUser();
		$model = $this->getModel('Teacher');
		$teacher = (int) $input->get('teacher', 0, 'INT');
		$start = (int) $input->get('start', 0, 'INT');

		$output = array();
		$output['status'] = false;
		if($teacher) {
			$followers = $model->getFollowers($teacher, 1, $start);
			$html = '';
			if(!empty($followers) && count($followers)) {
				foreach($followers as $follower) {
					$html .= LayoutHelper::render('avatar', array('avatar'=> $follower->avatar, 'title'=> $follower->name));
				}

				$output['status'] = true;
				$output['html'] = $html;
				$output['count'] = count($followers);
			}
		}

		echo json_encode($output);

		die();
	}

}
