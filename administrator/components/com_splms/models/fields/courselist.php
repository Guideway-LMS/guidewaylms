<?php
/**
 * @package     SP LMS
 *
 * @copyright   Copyright (C) 2010 - 2021 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No Direct Access
defined ('_JEXEC') or die('Resticted Aceess');

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\ListField;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Version;
use Joomla\CMS\Language\Text;

FormHelper::loadFieldClass('list');

$version = new Version();
$JoomlaVersion = $version->getShortVersion();

if (version_compare($JoomlaVersion, '4.0.0', '>='))
{
	JLoader::registerAlias('JFormFieldList', 'Joomla\CMS\Form\Field\ListField');
}

class JFormFieldCourselist extends JFormFieldList
{

	protected $type   = 'Courselist';
	protected $layout = 'joomla.form.field.list-fancy-select';

	public function getCourses() {
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('*');
		$query->from($db->quoteName('#__splms_courses'));
		$query->where($db->quoteName('published')." = 1");
		$query->where('published = 1');
		$query->order('ordering DESC');
		$db->setQuery($query);
		return $db->loadObjectList();
	}

	public function getOptions() {

		$doc = Factory::getDocument();
		// Legacy JS removed to prevent conflict with new AJAX implementation in lesson/edit.php
		// $doc->addScriptDeclaration('...');
		
		$courses = $this->getCourses();

		$options = [];
		$options[] = HTMLHelper::_('select.option', '', Text::_('COM_SPLMS_LESSON_FIELD_SELECT_COURSE'));

		foreach ($courses as $course) {
			$options[] = HTMLHelper::_('select.option', $course->id, $course->title);
		}

		return array_merge(parent::getOptions(), $options);

	}

}
