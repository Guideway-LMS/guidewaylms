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
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;

class SplmsModelCertificates extends ListModel {

	protected function getListQuery() {

		$app = Factory::getApplication();
		$user = Factory::getUser();
		
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select('a.*, c.title AS course_title');
		$query->from($db->quoteName('#__splms_certificates', 'a'));
		$query->join('LEFT', $db->quoteName('#__splms_courses', 'c') . ' ON c.id = a.course_id');

		// 🔥 FILTRA PELO USUÁRIO LOGADO
		$query->where('a.userid = ' . (int) $user->id);

		// Filter category
		if ($categoryId = $this->getState('category.id')) {
    		$query->where('a.catid = ' . (int) $categoryId);
		}

		$query->where('a.published = 1');
		$query->order('a.ordering ASC');

		return $query;
	}

	//if item not found
	public function &getItem($id = null) {
		$item = parent::getItem($id);
		if(Factory::getApplication()->isSite()) {
			if($item->id) {
				return $item;
			} else {
				throw new \Exception(Text::_('COM_SPLMS_NO_ITEMS_FOUND'), 404);
			}
		} else {
			return $item;
		}
	}

	// Get user By user id
	public static function getUser($user_id) {
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		
		$query->select($db->quoteName(array('a.name', 'a.email', 'b.profile_value', 'b.profile_key')));

		$query->from($db->quoteName('#__users', 'a'));

		$query->join('LEFT', $db->quoteName('#__user_profiles', 'b') . ' ON (' . $db->quoteName('a.id') . ' = ' . $db->quoteName('b.user_id') . ')');
		$query->where($db->quoteName('a.block')." = 0");
		$query->where($db->quoteName('a.id')." = ".$db->quote($user_id));
		//$query->where($db->quoteName('b.profile_key') . ' LIKE \'profilelms.avatar%\'');
		$db->setQuery($query);
		$result = $db->loadObject();

		return $result;
	}

	// Get Course By id
	public static function getCerficateById($user_id) {
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('b.title', 'a.id', 'a.userid', 'a.instructor')));
		$query->from($db->quoteName('#__splms_certificates', 'a'));
		$query->join('LEFT', $db->quoteName('#__splms_courses', 'b') . ' ON (' . $db->quoteName('a.course_id') . ' = ' . $db->quoteName('b.id') . ')');
		$query->where($db->quoteName('a.userid')." = ".$db->quote($user_id));
		$query->where($db->quoteName('a.published')." = 1");
		$query->order('a.ordering DESC');
		$db->setQuery($query);
		$results = $db->loadObjectList();

		foreach ($results as &$result) {
			$result->certificate_url = Route::_('index.php?option=com_splms&view=certificate&id='.$result->id.':' . splmshelper::getItemid('certificates'));
		}

		return $results;
	}

	// Get Course Category By Category id
	public static function getCourseCategory($id) {
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('id', 'title', 'alias')));
		$query->from($db->quoteName('#__splms_coursescategories'));
		$query->where($db->quoteName('published')." = 1");
		$query->where($db->quoteName('id')." = ".$db->quote($id));
		$query->order('ordering DESC');
		$db->setQuery($query);
		$result = $db->loadObject();
		
		return $result;
	}
	
	/**
     * Verifica se o usuário possui certificado para o curso (Regra da Sprint)
     * Requisito: user_id = usuário logado AND course_id = curso atual
     */
    public function hasCertificate($userId, $courseId) {
        $db = $this->getDbo();
        $query = $db->getQuery(true);

        $query->select('COUNT(*)')
              ->from($db->quoteName('#__splms_certificates'))
              ->where($db->quoteName('userid') . ' = ' . (int) $userId)
              ->where($db->quoteName('course_id') . ' = ' . (int) $courseId)
              ->where($db->quoteName('published') . ' = 1');

        $db->setQuery($query);
        
        // Retorna true se encontrar um registro, satisfazendo a regra da Sprint
        return (bool) $db->loadResult();
    }

}