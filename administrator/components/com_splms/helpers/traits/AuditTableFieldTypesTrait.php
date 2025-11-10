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
trait AuditTableFieldTypesTrait
{
	private function auditFieldTypesMismatches()
	{
		$mismatches = [];

		if (!empty($this->types))
		{
			foreach ($this->types as $table => $types)
			{
				$installedTableType = $this->getTableInformation($table, 'type');
				$diff = array_diff_assoc($types, $installedTableType);

				if (!empty($diff))
				{
					$mismatches[$table] = $diff;
				}
			}
		}

		/**
		 * Message for mismatches
		 */
		if (!empty($mismatches))
		{
			$this->html[] = "<h2 class='mt-3'>Type mismatches have been detected</h2>";
			$this->html[] = "<ul class='lighthouse-list'>";

			foreach ($mismatches as $table => $mismatch)
			{
				$this->html[] = "<li>";
				$this->html[] = "<strong>Table <code>" . $table . "</code></strong>";
				$this->html[] = "<ul class='lighthouse-list'>";

				foreach ($mismatch as $field => $type)
				{
					$info = $this->getTableInformation($table, 'type');
					$actual = isset($info[$field]) ? $info[$field] : 'n/a';

					$this->html[] = "<li>";
					$this->html[] = "<span class='icon text-danger'>&#x2A02;</span>";
					$this->html[] = "Mismatch found on the field: <code>" . $field .
						"</code>, Expected: <code>" . $type .
						"</code>, Found: <code>" . $actual . "</code>";
					$this->html[] = "</li>";
				}

				$this->html[] = "</ul>";
				$this->html[] = "</li>";
			}

			$this->html[] = "</ul>";
		}

		return $mismatches;
	}

	private function fixTypeMismatches($mismatches)
	{
		if (!empty($mismatches))
		{
			$this->html[] = "<h4>Fixing field types mismatches...</h4>";
			$this->html[] = "<ul class='lighthouse-list'>";

			foreach ($mismatches as $table => $mismatch)
			{
				$this->html[] = "<li>";
				$this->html[] = "<strong>Table <code class='success'>" . $table . "</code></strong>";
				$this->html[] = "<ul class='lighthouse-list'>";

				foreach ($mismatch as $field => $type)
				{
					try
					{
						$info = $this->getTableInformation($table, 'type');
						$actual = isset($info[$field]) ? $info[$field] : 'n/a';

						$db = Factory::getDbo();
						$sql = "ALTER TABLE " . $db->quoteName($table) . " MODIFY " .
							$db->quoteName($field) . " " .
							$type;
						$db->setQuery($sql);
						$db->execute();

						$this->html[] = "<li>";
						$this->html[] = "<span class='icon text-success'>&#x2714;</span>";
						$this->html[] = "Fixed: Modified mismatching field: <code class='success'>" . $field .
							"</code>, Changed from <code class='success'>" . $actual .
							" <span style='font-size: 16px;'>&#x2799;</span> " . $type . "</code>";
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

	private function auditAndFixFieldTypeMismatches()
	{
		$mismatches = $this->auditFieldTypesMismatches();
		$this->fixTypeMismatches($mismatches);

		return empty($mismatches);
	}
}
