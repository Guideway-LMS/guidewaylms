<?php

use Joomla\CMS\Factory;
/**
 * @package Plg_Lighthouse
 * @author JoomShaper <support@joomshaper.com>
 * @copyright Copyright (c) 2010 - 2020 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

defined('_JEXEC') or die;

/**
 * Audit tables trait
 *
 * @since	1.0.0
 */
trait AuditTablesTrait
{
	private function auditTables()
	{
		$installedTables = $this->getAllTheInstalledTables();
		$tableNames = $this->getTableNames();
		$missingTables = [];

		foreach ($tableNames as $name)
		{
			if (!in_array($this->getRealTableName($name), $installedTables))
			{
				$missingTables[] = $name;
			}
		}

		if (!empty($missingTables))
		{
			$this->html[] = "<h4>Missing tables: </h4>";
			$this->html[] = "<ul class='lighthouse-list'>";

			foreach ($missingTables as $tbl)
			{
				$this->html[] = "<li>";
				$this->html[] = "<span class='icon text-danger'>&#x2A02;</span>";
				$this->html[] = "Table <code>" . $tbl . "</code> does not exists.";
				$this->html[] = "</li>";
			}

			$this->html[] = "</ul>";
		}

		return $missingTables;
	}

	private function fixMissingTables($tables)
	{
		if (!empty($tables))
		{
			$this->html[] = "<h4>Fixing missing tables...</h4>";
			$this->html[] = "<ul class='lighthouse-list'>";

			foreach ($tables as $table)
			{
				$createSql = implode("\n", $this->tables[$table]);

				try
				{
					$db = Factory::getDbo();
					$db->setQuery($createSql);
					$db->execute();

					$this->html[] = "<li>";
					$this->html[] = "<span class='icon text-success'>&#x2714;</span>";
					$this->html[] = "Fixed: Created missing table: <code class='success'>" . $table . "</code> successfully!";
					$this->html[] = "</li>";
				}
				catch (Exception $e)
				{
					$this->errors[] = $e->getMessage();
					$this->html[] = "<li><span class='icon text-danger'>&#x274C;</span> Failed to fix for the problem <code>" . $e->getMessage() . "</code></li>";
					continue;
				}
			}

			$this->html[] = "</ul>";
		}
	}

	private function auditAndFixTables()
	{
		$missing = $this->auditTables();
		$this->fixMissingTables($missing);
	}
}
