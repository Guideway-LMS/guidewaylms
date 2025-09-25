<?php
/**
 * @package Plg_Lighthouse
 * @author JoomShaper <support@joomshaper.com>
 * @copyright Copyright (c) 2010 - 2020 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

/**
 * Trait for handling table fields
 *
 * @since	1.0.0
 */
trait AuditTableFieldsTrait
{
	private function auditAndFixTableFields()
	{
		$missing = $this->auditMissingFields();
		$this->fixMissingTableFields($missing);

		$removed = $this->auditRemovedFields();
		$this->fixRemovedTableFields($removed);

		return empty($missing) && empty($removed);
	}

	private function auditMissingFields()
	{
		$missing = [];

		foreach ($this->tables as $name => $table)
		{
			$dbFields = $this->getTableColumns($name);
			$sqlFields = array_keys($this->fields[$name]);

			$diff = array_diff($sqlFields, $dbFields);

			if (!empty($diff))
			{
				foreach ($diff as $field)
				{
					$missing[$name][$field] = $this->fields[$name][$field];
				}
			}
		}

		if (!empty($missing))
		{
			$this->html[] = "<h2 class='mt-3'>Missing and removed table fields detected</h2><br/>";

			$this->html[] = "<h4>Missing fields: </h4>";
			$this->html[] = "<ul class='lighthouse-list'>";

			foreach ($missing as $table => $fields)
			{
				$this->html[] = "<li>";
				$this->html[] = "<strong>Table <code>" . $table . "</code></strong>";
				$this->html[] = "<ul class='lighthouse-list'>";

				foreach ($fields as $field => $structure)
				{
					$this->html[] = "<li>";
					$this->html[] = "<span class='icon text-danger'>&#x2A02;</span>";
					$this->html[] = "Missing field: <code>" . $structure . "</code>";
					$this->html[] = "</li>";
				}

				$this->html[] = "</ul>";
				$this->html[] = "</li>";
			}

			$this->html[] = "</ul>";
		}

		return $missing;
	}

	private function auditRemovedFields()
	{
		$removed = [];

		foreach ($this->tables as $name => $table)
		{
			$dbFields = $this->getTableColumns($name);
			$sqlFields = array_keys($this->fields[$name]);

			$diff = array_diff($dbFields, $sqlFields);

			if (!empty($diff))
			{
				foreach ($diff as $field)
				{
					$removed[$name][] = $field;
				}
			}
		}

		if (!empty($removed))
		{
			$this->html[] = "<h4>Already removed fields: </h4>";
			$this->html[] = "<ul class='lighthouse-list'>";

			foreach ($removed as $table => $fields)
			{
				$this->html[] = "<li>";
				$this->html[] = "<strong>Table <code>" . $table . "</code></strong>";
				$this->html[] = "<ul class='lighthouse-list'>";

				foreach ($fields as $field)
				{
					$this->html[] = "<li>";
					$this->html[] = "<span class='icon text-danger'>&#x2A02;</span>";
					$this->html[] = "Removed field: <code>" . $field . "</code>";
					$this->html[] = "</li>";
				}

				$this->html[] = "</ul>";
				$this->html[] = "</li>";
			}

			$this->html[] = "</ul>";
		}

		return $removed;
	}

	private function fixMissingTableFields($missingFields)
	{
		if (!empty($missingFields))
		{
			$this->html[] = "<h4>Fixing missing fields...</h4>";
			$this->html[] = "<ul class='lighthouse-list'>";

			foreach ($missingFields as $table => $fields)
			{
				$this->html[] = "<li>";
				$this->html[] = "<strong>Table <code class='success'>" . $table . "</code></strong>";
				$this->html[] = "<ul class='lighthouse-list'>";

				foreach ($fields as $field => $structure)
				{
					try
					{
						$db = Factory::getDbo();
						$createFieldSql = "ALTER TABLE " . $db->quoteName($table) . " ADD " . $structure;
						$db->setQuery($createFieldSql);
						$db->execute();

						$this->html[] = "<li>";
						$this->html[] = "<span class='icon text-success'>&#x2714;</span>";
						$this->html[] = "Fixed: Added the missing field: <code class='success'>" . $structure . "</code> successfully!";
						$this->html[] = "</li>";
					}
					catch (Exception $e)
					{
						$this->errors[] = $e->getMessage();
						$this->html[] = "<li><span class='icon text-danger'>&#x274C;</span>Error: Failed to fix for the problem <code>" . $e->getMessage() . "</code></li>";
						continue;
					}
				}

				$this->html[] = "</ul>";
				$this->html[] = "</li>";
			}

			$this->html[] = "</ul>";
		}
	}

	private function fixRemovedTableFields($removedFields)
	{
		if (!empty($removedFields))
		{
			$this->html[] = "<h4>Fixing removed fields...</h4>";
			$this->html[] = "<ul class='lighthouse-list'>";

			foreach ($removedFields as $table => $fields)
			{
				$this->html[] = "<li>";
				$this->html[] = "<strong>Table <code class='success'>" . $table . "</code></strong>";
				$this->html[] = "<ul class='lighthouse-list'>";

				foreach ($fields as $field)
				{
					try
					{
						$db = Factory::getDbo();
						$createFieldSql = "ALTER TABLE " . $db->quoteName($table) . " DROP " . $db->quoteName($field);
						$db->setQuery($createFieldSql);
						$db->execute();

						$this->html[] = "<li>";
						$this->html[] = "<span class='icon text-success'>&#x2714;</span>";
						$this->html[] = "Fixed: Removed the non-existing field: <code class='success'>" . $field . "</code> successfully!";
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
				$this->html[] = "</li>";
			}

			$this->html[] = "</ul>";
		}
	}
}
