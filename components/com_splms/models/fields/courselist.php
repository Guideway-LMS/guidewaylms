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
use Joomla\CMS\Form\FormField;
use Joomla\CMS\HTML\HTMLHelper;
// Requred Componenet and module helper
//require_once JPATH_ROOT . '/modules/mod_sp_soccer_recent_results/helper.php';

class JFormFieldCourselist extends FormField {

    protected $type = 'courselist';

    protected function getInput(){

        // Get Tournaments
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        // Select all records from the user profile table where key begins with "custom.".
        $query->select($db->quoteName(array('id', 'title' )));
        $query->from($db->quoteName('#__splms_courses'));
        $query->where($db->quoteName('published')." = 1");
        $query->order('ordering ASC');

        $db->setQuery($query);  
        $results = $db->loadObjectList();
        $course_list = $results;
        $options = [];
        foreach($course_list as $course){
            $options[] = HTMLHelper::_( 'select.option', $course->id, $course->title );
        }
        
        return HTMLHelper::_('select.genericlist', $options, $this->name, array('class'=>'custom-select'), 'value', 'text', $this->value);
    }
}
