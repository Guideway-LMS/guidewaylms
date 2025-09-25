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

class JFormFieldCertificatelist extends FormField {

    protected $type = 'certificatelist';

    protected function getInput(){

        // Get Certificates
        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select($db->quoteName(array('b.title', 'a.id', 'a.userid', 'a.instructor', 'c.name')));
        $query->from($db->quoteName('#__splms_certificates', 'a'));
        $query->join('LEFT', $db->quoteName('#__splms_courses', 'b') . ' ON (' . $db->quoteName('a.course_id') . ' = ' . $db->quoteName('b.id') . ')');
        $query->join('LEFT', $db->quoteName('#__users', 'c') . ' ON (' . $db->quoteName('a.userid') . ' = ' . $db->quoteName('c.id') . ')');
        $query->where($db->quoteName('a.published')." = 1");
        $query->order('a.ordering DESC');
        $db->setQuery($query);
        $results = $db->loadObjectList();
        $c_list = $results;
        $options = array();
        foreach($c_list as $certificate){
            $options[] = HTMLHelper::_( 'select.option', $certificate->id, $certificate->title . ' ( ' . $certificate->name . ' )' );
        }
        
        return HTMLHelper::_('select.genericlist', $options, $this->name,array('class' => 'custom-select'), 'value', 'text', $this->value);
    }
}
