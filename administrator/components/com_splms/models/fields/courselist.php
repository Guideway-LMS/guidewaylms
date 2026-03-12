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
		$doc->addScriptDeclaration('
            jQuery(function($){
				$("#jform_course_id").on("change", function(e) {
					e.preventDefault();
					let closestFieldset = $(this).closest("fieldset");

					let topicInputFieldId = "#jform_'. (string)$this->element['topicid'] .'";
					let topicInputField = $("#jform_'. (string)$this->element['topicid'] .'");
					let SelectedCourseId = $(this).val();

					// Set courseId to topicInput
					topicInputField.attr("data-courseid", SelectedCourseId);					
					
					$.get(location.href + "&courseid=" + SelectedCourseId)
					.then(function(page) {
						topicInputField.html($(page).find(topicInputFieldId).html());

						let data = [];
						topicInputField.find("option:selected").each(function(){
							data.push($(this).val());
						});
						
						if (data.length) {
							for (var i = 0; i < data.length; i++) {
								data[i] = data[i].replace(/^\s*/, "").replace(/\s*$/, "");
							}
							topicInputField.val(data).trigger("liszt:updated");
						} else {
							topicInputField.trigger("liszt:updated");
						}
					})
				});
            });
		');
		
		$courses = $this->getCourses();

		$options = [];
		$options[] = HTMLHelper::_('select.option', '', '- Selecione um Curso -');

		foreach ($courses as $course) {
			$options[] = HTMLHelper::_('select.option', $course->id, $course->title);
		}

		return array_merge(parent::getOptions(), $options);

	}

}
