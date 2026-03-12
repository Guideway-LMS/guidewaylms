<?php
/**
 * @package com_splms
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2024 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

// No Direct Access
defined ('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\Installer;

class com_splmsInstallerScript
{
    public function uninstall($parent) {

        $extensions = array(
            array('type'=>'module', 'name'=>'mod_splmscourses'),
            array('type'=>'module', 'name'=>'mod_splmscoursescategegory'),
            array('type'=>'module', 'name'=>'mod_splmscoursesearch'),
            array('type'=>'module', 'name'=>'mod_splmseventcategories'),
            array('type'=>'module', 'name'=>'mod_splmsupcomingevents'),
            array('type'=>'module', 'name'=>'mod_splmspersons'),
            array('type'=>'module', 'name'=>'mod_splmscart'),
            array('type'=>'module', 'name'=>'mod_splmseventcalendar'),
            array('type'=>'plugin', 'name'=>'profilelms'),
            array('type'=>'plugin', 'name'=>'splmsupdater')
        );

        foreach ($extensions as $key => $extension) {

            $db = Factory::getDbo();
            $query = $db->getQuery(true);
            $query->select($db->quoteName(array('extension_id')));
            $query->from($db->quoteName('#__extensions'));
            $query->where($db->quoteName('type') . ' = '. $db->quote($extension['type']));
            $query->where($db->quoteName('element') . ' = '. $db->quote($extension['name']));
            $db->setQuery($query);
            $id = $db->loadResult();

            if(isset($id) && $id) {
                $installer = new Installer;
                $result = $installer->uninstall($extension['type'], $id);
            }
        }
    }


    /**
     * Runs just before any installation action is performed on the component.
     * Verifications and pre-requisites should run in this function.
     *
     * @param  string    $type   - Type of PreFlight action. Possible values are:
     *                           - * install
     *                           - * update
     *                           - * discover_install
     * @param  \stdClass $parent - Parent object calling object.
     *
     * @return void
     */
    public function preflight($type, $parent) 
    {

        $db = Factory::getDbo();
		$query = $db->getQuery(true)
		->select($db->quoteName('e.manifest_cache'))
		->from($db->quoteName('#__extensions', 'e'))
		->where($db->quoteName('e.element') . ' = ' . $db->quote('com_splms'));
		$db->setQuery($query);
        
        $result = $db->loadResult();

        if (!empty($result))
        {
            $manifest_cache = json_decode($result);

            if (isset($manifest_cache->version) && $manifest_cache->version)
            {
    
                if ($manifest_cache->version >= 3.3)
                {
                    if (!array_key_exists('admission_deadline', $db->getTableColumns("#__splms_courses")))
                    {
                        
                        $query = "ALTER TABLE `#__splms_courses` ADD `admission_deadline` varchar(150) NOT NULL DEFAULT '' AFTER `download`";
                        $db->setQuery($query);
                        $db->execute();
                    }
                }
                
            }
        }
    }

    function postflight($type, $parent) {
        $extensions = array(
            array('type'=>'module', 'name'=>'mod_splmscourses'),
            array('type'=>'module', 'name'=>'mod_splmscoursescategegory'),
            array('type'=>'module', 'name'=>'mod_splmscoursesearch'),
            array('type'=>'module', 'name'=>'mod_splmseventcategories'),
            array('type'=>'module', 'name'=>'mod_splmsupcomingevents'),
            array('type'=>'module', 'name'=>'mod_splmspersons'),
            array('type'=>'module', 'name'=>'mod_splmscart'),
            array('type'=>'module', 'name'=>'mod_splmseventcalendar'),
            array('type'=>'plugin', 'name'=>'profilelms', 'group'=>'user'),
            array('type'=>'plugin', 'name'=>'splmsupdater', 'group'=>'system')
        );

        foreach ($extensions as $key => $extension) {
            $ext = $parent->getParent()->getPath('source') . '/' . $extension['type'] . 's/' . $extension['name'];
            $installer = new Installer;
            $installer->install($ext);

            if($extension['type'] == 'plugin') {
                $db = Factory::getDbo();
                $query = $db->getQuery(true);

                $fields = array($db->quoteName('enabled') . ' = 1');
                $conditions = array(
                    $db->quoteName('type') . ' = ' . $db->quote($extension['type']),
                    $db->quoteName('element') . ' = ' . $db->quote($extension['name']),
                    $db->quoteName('folder') . ' = ' . $db->quote($extension['group'])
                );

                $query->update($db->quoteName('#__extensions'))->set($fields)->where($conditions);
                $db->setQuery($query);
                $db->execute();
            }
        }
    }
}
