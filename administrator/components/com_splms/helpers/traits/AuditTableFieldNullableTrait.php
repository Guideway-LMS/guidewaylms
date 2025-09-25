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
trait AuditTableFieldNullableTrait
{
	private function auditNullTypeMismatches()
	{
		$mismatches = [];

		if (!empty($this->nullables))
		{
			foreach ($this->nullables as $table => $fields)
			{
				$installedNullables = $this->getTableInformation($table, 'null');

				$diff = array_diff_assoc($fields, $installedNullables);

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
			$this->html[] = "<h2 class='mt-3'>Field Null type mismatches have been detected</h2>";
			$this->html[] = "<ul class='lighthouse-list'>";

			foreach ($mismatches as $table => $mismatch)
			{
				$this->html[] = "<li>";
				$this->html[] = "<strong>Table <code>" . $table . "</code></strong>";
				$this->html[] = "<ul class='lighthouse-list'>";

				foreach ($mismatch as $field => $type)
				{
					$info = $this->getTableInformation($table, 'null');
					$actual = isset($info[$field]) ? $info[$field] : 'n/a';

					$this->html[] = "<li>";
					$this->html[] = "<span class='icon text-danger'>&#x2A02;</span>";
					$this->html[] = "Mismatch found on the field: <code>" . $field .
						"</code>, Expected field is null: <code>" . $type .
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

	private function fixNullTypeMismatches($mismatches)
	{
		if (!empty($mismatches))
		{
			$this->html[] = "<h4>Fixing field's Null types mismatches...</h4>";
			$this->html[] = "<ul class='lighthouse-list'>";

			foreach ($mismatches as $table => $mismatch)
			{
				$this->html[] = "<li>";
				$this->html[] = "<strong>Table <code class='success'>" . $table . "</code></strong>";
				$this->html[] = "<ul class='lighthouse-list'>";

				foreach ($mismatch as $field => $type)
				{
					$info = $this->getTableInformation($table, 'null');
					$actual = isset($info[$field]) ? $info[$field] : 'n/a';

					try
					{
						$db = Factory::getDbo();
						$sql = '';

						if (isset($this->types[$table], $this->types[$table][$field]))
						{
							if ($type === 'yes')
							{
								$sql = "ALTER TABLE " . $db->quoteName($table) .
									" MODIFY " . $db->quoteName($field) . " " .
									$this->types[$table][$field] . " DEFAULT NULL";
							}
							else
							{
								$sql = "ALTER TABLE " . $db->quoteName($table) .
									" MODIFY " . $db->quoteName($field) . " " .
									$this->types[$table][$field] . " NOT NULL";
							}
						}

						if (!empty($sql))
						{
							$db->setQuery($sql);
							$db->execute();
						}
						else
						{
							continue;
						}

						$this->html[] = "<li>";
						$this->html[] = "<span class='icon text-success'>&#x2714;</span>";
						$this->html[] = "Fixed: Modified mismatching field: <code class='success'>" . $field .
							"</code>, Changed field is null from <code class='success'>" . $actual .
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

	private function auditAndFixNullTypeMismatches()
	{
		$mismatches = $this->auditNullTypeMismatches();
		$this->fixNullTypeMismatches($mismatches);

		return empty($mismatches);
	}
}
