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
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Filesystem\Folder;
use Joomla\Utilities\ArrayHelper;

/**
 * SP LMS custom profile plugin.
 *
 * @since  1.0.0
 */
class PlgUserProfilelms extends CMSPlugin
{
	/**
	 * Stor avatars.
	 *
	 * @var array
	 * @since 1.0.0
	 */
	protected static $avatar = array();

	/**
	 * last_avatar name.
	 *
	 * @var string
	 */
	protected static $last_avatar = '';

	/**
	 * onContentPrepareData function
	 *
	 * @param  string $context
	 * @param  object $data
	 * @return void
	 */
	function onContentPrepareData($context, $data)
	{
		// Check we are manipulating a valid form.
		if (!in_array($context, array('com_users.profile','com_users.registration','com_users.user','com_admin.profile')))
		{
			return true;
		}

		if (is_object($data))
		{
			$userId = $data->id ?? 0;

			if (!isset($data->profilelms) && $userId > 0)
			{
				// Load the profile data from the database.
				$db = Factory::getDbo();
				$query = $db->getQuery(true)
					->select(
						[
							$db->quoteName('profile_key'),
							$db->quoteName('profile_value'),
						]
					)
					->from($db->quoteName('#__user_profiles'))
					->where($db->quoteName('user_id') . ' = ' . $db->quote($userId))
					->where($db->quoteName('profile_key') . ' LIKE ' . $db->quote('profilelms.%'))
					->order($db->quoteName('ordering'));

				$db->setQuery($query);
				$results = $db->loadRowList();

				// Merge the profilelms data.
				$data->profilelms = [];

				foreach ($results as $v)
				{
					$k = str_replace('profilelms.', '', $v[0]);
					$data->profilelms[$k] = json_decode($v[1], true);

					if ($data->profilelms[$k] === null)
					{
						$data->profilelms[$k] = $v[1];
					}
				}
			}
		}

		return true;
	}

	/**
	 * @param	Form	The form to be altered.
	 * @param	array	The associated data for the form.
	 * @return	boolean
	 * @since	1.6
	 */
	function onContentPrepareForm($form, $data)
	{

		// Load user_profile plugin language
		$lang = Factory::getLanguage();
		$lang->load('plg_user_profilelms', JPATH_ADMINISTRATOR);

		if (!($form instanceof Form))
		{
			Factory::getApplication()->enqueueMessage('JERROR_NOT_A_FORM');
			return false;
		}
		// Check we are manipulating a valid form.
		if (!in_array($form->getName(), array('com_users.profile', 'com_users.registration','com_users.user','com_admin.profile')))
		{
			return true;
		}

		if ($form->getName()=='com_users.profile')
		{
			// Add the profile fields to the form.
			Form::addFormPath(dirname(__FILE__).'/profiles');
			$form->loadFile('profile', true);
 
			// Toggle whether the something field is required.
			if ($this->params->get('profile-require_something', 1) > 0)
			{
				$form->setFieldAttribute('lms_avatar', 'required', $this->params->get('profile-require_something') == 2, 'profilelms');
			}
			else
			{
				$form->removeField('lms_avatar', 'profilelms');
			}
		}
 
		//In this example, we treat the frontend registration and the back end user create or edit as the same. 
		elseif ($form->getName()=='com_users.registration' || $form->getName()=='com_users.user' )
		{		
			// Add the registration fields to the form.
			Form::addFormPath(dirname(__FILE__).'/profiles');
			$form->loadFile('profile', true);

			// Toggle whether the something field is required.
			if ($this->params->get('register-require_something', 1) > 0)
			{
				$form->setFieldAttribute('lms_avatar', 'required', $this->params->get('register-require_something') == 2, 'profilelms');
			}
			else
			{
				$form->removeField('lms_avatar', 'profilelms');
			}
		}			
	}

	// check file type
	protected static function fileExtensionCheck($file, $allowed)
	{
		$ext = pathinfo($file['profilelms']['lms_avatar']['name'], PATHINFO_EXTENSION);
		if(in_array( strtolower($ext), $allowed) )
		{
			return true;
		}

		return false;
	}

	function onUserBeforeSave($user, $isnew, $new)
	{
		//Import filesystem libraries. Perhaps not necessary, but does not hurt
		$input = Factory::getApplication()->input;
		self::$avatar = $input->files->get('jform');

		//get last uploaded avatar
		if (!empty(self::existAvatar($new['id'])))
		{
			self::$last_avatar = json_decode(self::existAvatar($new['id'])->profile_value)->avatar;
		}

		// New avatar
		$avatar_name = self::$avatar['profilelms']['lms_avatar']['name'];

		if (isset($avatar_name) && $avatar_name !='')
		{
			if (!self::fileExtensionCheck(self::$avatar, array('png', 'jpg')))
			{
				throw new RuntimeException(Text::_('INVALID_FILE_TYPE'), 1);
			}
			elseif (self::$avatar['profilelms']['lms_avatar']['size'] > 800000)
			{
				throw new RuntimeException(Text::_('INVALID_FILE_SIZE'), 1);
			}
		}
	
	}

 
	function onUserAfterSave($data, $isNew, $result, $error)
	{

		$userId	= ArrayHelper::getValue($data, 'id', 0, 'int');

		$avatar_name = self::$avatar['profilelms']['lms_avatar']['name'];

		// has avatar
		if (isset($avatar_name) && $avatar_name != '')
		{
			$folder_path = JPATH_ROOT . '/media/com_splms/users/';
			if (!Folder::exists( $folder_path ))
			{
				Folder::create($folder_path);
			}

			// Cleans the name of teh file by removing weird characters
			$filename = File::makeSafe($avatar_name); 
			$src = self::$avatar['profilelms']['lms_avatar']['tmp_name'];
		}
 
		if (($userId && $result && isset($data['profilelms']) && (count($data['profilelms']))) || $src)
		{
			
				if (isset($avatar_name) && $avatar_name != '')
				{
					$exist_avatar = '';
					if (!empty(self::existAvatar($userId)))
					{
						$exist_avatar = json_decode(self::existAvatar($userId)->profile_value)->avatar;
					}

					if (isset($exist_avatar) && $exist_avatar != '')
					{
						File::delete(JPATH_ROOT.$exist_avatar);
					}
				}

				$db = Factory::getDbo();

				$query = $db->getQuery(true)
					->delete($db->quoteName('#__user_profiles'))
					->where($db->quoteName('user_id') . ' = ' . $userId)
					->andWhere($db->quoteName('profile_key') . ' LIKE ' . $db->quote('profilelms.%'));
				$db->setQuery($query);
				$db->execute();
			
				if ((isset($avatar_name) && $avatar_name != ''))
				{
					$file_path = '/media/com_splms/users/'. $userId.'-'.$filename;

					// if file upload then insert into DB
					if (File::upload($src, $folder_path. '/' .$userId.'-'.$filename)) {
						$data['profilelms']['avatar'] = array('avatar'=> $file_path);
					}
				}

				// already has avatar and not uploaded
				if (self::$last_avatar != '' && $avatar_name =='') {
					$data['profilelms']['avatar'] = array('avatar'=> self::$last_avatar);
				}elseif (self::$last_avatar == '' && $avatar_name =='') {
					$data['profilelms']['avatar'] = array('avatar'=> '');
				}

				$query->clear()
				->select($db->quoteName('ordering'))
				->from($db->quoteName('#__user_profiles'))
				->where($db->quoteName('user_id') . ' = ' . $userId);
				$db->setQuery($query);
				$usedOrdering = $db->loadColumn();

				$order = 1;
				$query->clear()
					->insert($db->quoteName('#__user_profiles'));

				foreach ($data['profilelms'] as $k => $v)
				{
					while (in_array($order, $usedOrdering))
					{
						$order++;
					}

					$query->values(implode(',',[$userId, $db->quote('profilelms.' . $k), $db->quote(json_encode($v)), $order++]));
				}

				$db->setQuery($query);
				$db->execute();

			
		}
 
		return true;
	}
 

	protected static function existAvatar($user_id)
	{
		$lmsdb = Factory::getDbo();
		$lmsquery = $lmsdb->getQuery(true);
		$lmsquery->select('profile_value');
		$lmsquery->from($lmsdb->quoteName('#__user_profiles'));
		$lmsquery->where($lmsdb->quoteName('user_id')." = ". $lmsdb->quote($user_id));
		$lmsquery->where($lmsdb->quoteName('profile_key') . ' = '. $lmsdb->quote('profilelms.avatar'));
		$lmsdb->setQuery($lmsquery);

		return $lmsdb->loadObject();
	}

	/**
	 * Remove all user profile information for the given user ID
	 *
	 * Method is called after user data is deleted from the database
	 *
	 * @param	array		$user		Holds the user data
	 * @param	boolean		$success	True if user was succesfully stored in the database
	 * @param	string		$msg		Message
	 */
	function onUserAfterDelete($user, $success, $msg){
		if (!$success) {
			return false;
		}
 
		$userId	= ArrayHelper::getValue($user, 'id', 0, 'int');
 
		if ($userId)
		{
			$db = Factory::getDbo();

			$query = $db->getQuery(true)
				->delete($db->quoteName('#__user_profiles'))
				->where($db->quoteName('user_id') . ' = ' . $userId)
				->andWhere($db->quoteName('profile_key') . ' LIKE ' . $db->quote('profilelms.%'));
			$db->setQuery($query);
			$db->execute();
		}
 
		return true;
	}
 }