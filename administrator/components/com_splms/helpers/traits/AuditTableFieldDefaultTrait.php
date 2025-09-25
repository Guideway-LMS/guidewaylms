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
trait AuditTableFieldDefaultTrait
{
	private function auditDefaultValueMismatches()
	{
		$mismatches = [];

		if (!empty($this->defaults))
		{
			foreach ($this->defaults as $table => $fields)
			{
				$installedDefaults = $this->getTableInformation($table, 'default');
				$diff = array_diff_assoc($fields, $installedDefaults);

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
			$this->html[] = "<h2 class='mt-3'>Field Default Value mismatches have been detected</h2>";
			$this->html[] = "<ul class='lighthouse-list'>";

			foreach ($mismatches as $table => $mismatch)
			{
				$this->html[] = "<li>";
				$this->html[] = "<strong>Table <code>" . $table . "</code></strong>";
				$this->html[] = "<ul class='lighthouse-list'>";

				foreach ($mismatch as $field => $value)
				{
					$info = $this->getTableInformation($table, 'default');
					$actual = isset($info[$field]) ? $info[$field] : 'n/a';
					$actual = is_null($actual) ? 'NULL' : $actual;
					$expected = is_null($value) ? 'NULL' : $value;

					$this->html[] = "<li>";
					$this->html[] = "<span class='icon text-danger'>&#x2A02;</span>";
					$this->html[] = "Mismatch found on the field: <code>" . $field .
						"</code>, Expected Default value: <code>" . $expected .
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

	private function fixDefaultValueMismatches($mismatches)
	{
		if (!empty($mismatches))
		{
			$this->html[] = "<h4>Fixing field's Default Values mismatches...</h4>";
			$this->html[] = "<ul class='lighthouse-list'>";

			foreach ($mismatches as $table => $mismatch)
			{
				$this->html[] = "<li>";
				$this->html[] = "<strong>Table <code class='success'>" . $table . "</code></strong>";
				$this->html[] = "<ul class='lighthouse-list'>";

				foreach ($mismatch as $field => $value)
				{
					$info = $this->getTableInformation($table, 'default');
					$actual = isset($info[$field]) ? $info[$field] : 'n/a';
					$actual = is_null($actual) ? 'NULL' : $actual;
					$expected = is_null($value) ? 'NULL' : $value;

					try
					{
						$db = Factory::getDbo();
						$sql = "ALTER TABLE " . $db->quoteName($table) .
							" ALTER COLUMN " . $db->quoteName($field) .
							(is_null($value) ? " SET DEFAULT NULL" : " SET DEFAULT " . $db->quote($value));

						$db->setQuery($sql);
						$db->execute();

						$this->html[] = "<li>";
						$this->html[] = "<span class='icon text-success'>&#x2714;</span>";
						$this->html[] = "Fixed: Modified mismatching field: <code class='success'>" . $field .
							"</code>, Changed Default Value from <code class='success'>" . $actual .
							" <span style='font-size: 16px;'>&#x2799;</span> " . $expected . "</code>";
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

	private function auditAndFixDefaultValueMismatches()
	{
		$mismatches = $this->auditDefaultValueMismatches();
		$this->fixDefaultValueMismatches($mismatches);

		return empty($mismatches);
	}
}
