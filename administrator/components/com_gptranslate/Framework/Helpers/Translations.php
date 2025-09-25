<?php
namespace JExtstore\Component\Gptranslate\Administrator\Framework\Helpers;
/**
 * @package GPTRANSLATE::FRAMEWORK::administrator::components::com_gptranslate
 * @subpackage framework
 * @subpackage helpers
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use JExtstore\Component\Gptranslate\Administrator\Framework\Helpers\Error as BaseError;
use JExtstore\Component\Gptranslate\Administrator\Framework\Exception as GptranslateException;

// CSV import fields
define ('COM_GPTRANSLATE_PAGELINK', 0);
define ('COM_GPTRANSLATE_TRANSLATIONS', 1);
define ('COM_GPTRANSLATE_ORIGINAL_LANGUAGE', 2);
define ('COM_GPTRANSLATE_TRANSLATED_LANGUAGE', 3);
define ('COM_GPTRANSLATE_PUBLISHED', 4);
define ('COM_GPTRANSLATE_TRANSLATION_DATE', 5);
define ('COM_GPTRANSLATE_TRANSLATION_ENGINE', 6);

define ('COM_GPTRANSLATE_FIELD_NUM', 7);

/**
 * Importer class for metainfo records
 * 
 * @package GPTRANSLATE::FRAMEWORK::administrator::components::com_gptranslate
 * @subpackage framework
 * @subpackage file
 * @since 3.5
 */
class Translations extends BaseError {
	/**
	 * Database connector
	 * 
	 * @access private
	 * @var Object
	 */
	private $dbo;
	
	/**
	 * Application object
	 *
	 * @access private
	 * @var Object
	 */
	private $app;
	
	/**
	 * Store uploaded file to cache folder,
	 * fully manage error messages and ask for database insert
	 * 
	 * @access public
	 * @return boolean 
	 */
	public function import() {
		// Get file info
		$file = $this->app->getInput()->files->get('datasourceimport', null, 'raw');
		$tmpFile = $file['tmp_name'];
		$tmpFileName = $file['name'];
		try {
			if(!$tmpFile || !$tmpFileName) {
				throw new GptranslateException(Text::_('COM_GPTRANSLATE_NOFILE_SELECTED'), 'error');
			}
			
			$tmpFileExtension = @array_pop(explode('.', $tmpFileName));
			if($tmpFileExtension != 'csv') {
				throw new GptranslateException(Text::_('COM_GPTRANSLATE_EXT_ERROR'), 'error');
			}

			// Deserialize contents
			$fileHandle = fopen($tmpFile, "r");
			if(!is_resource($fileHandle)) {
				throw new GptranslateException(Text::_('COM_GPTRANSLATE_DATA_FILE_NOT_READABLE'), 'error');
			}
			
			// Parse the CSV files dataset into an importable array
			$skip = true;
			$dbQueryArray = array();
			while ( $csvRecord = fgetcsv ( $fileHandle, 0, ';', '"' ) ) {
				// Skip prima riga intestazioni
				if($skip) {
					$skip = false;
					continue;
				}
				//Insert
				array_push ( $dbQueryArray, $csvRecord );
			}
			
			// Check if some valid data to import are available
			if(!count($dbQueryArray)) {
				throw new GptranslateException(Text::_('COM_GPTRANSLATE_NO_IMPORT_DATA_FOUND'), 'warning');
			}
			
			// Prepare the values array
			foreach ($dbQueryArray as $dbRecord) {
				// Ensyre at least that the number of csv fields are correct
				if(count($dbRecord) != COM_GPTRANSLATE_FIELD_NUM) {
					continue;
				}
				
				// Check if the link as primary key already exists in this table
				$selectQuery = "SELECT" . $this->dbo->quoteName('id') .
							   "\n FROM " . $this->dbo->quoteName('#__gptranslate') .
							   "\n WHERE" .
							   $this->dbo->quoteName('pagelink') . " = " . $this->dbo->quote($dbRecord[COM_GPTRANSLATE_PAGELINK]);
				$linkExists = $this->dbo->setQuery ( $selectQuery )->loadResult();
	
				// If the link exists just update it, otherwise insert a new one
				if($linkExists) {
					$query = "UPDATE" .
							 "\n " . $this->dbo->quoteName('#__gptranslate') .
							 "\n SET " .
							 "\n " . $this->dbo->quoteName('pagelink') . " = " . $this->dbo->quote($dbRecord[COM_GPTRANSLATE_PAGELINK]) . "," .
							 "\n " . $this->dbo->quoteName('translations') . " = " . $this->dbo->quote($dbRecord[COM_GPTRANSLATE_TRANSLATIONS]) . "," .
							 "\n " . $this->dbo->quoteName('languageoriginal') . " = " . $this->dbo->quote($dbRecord[COM_GPTRANSLATE_ORIGINAL_LANGUAGE]) . "," .
							 "\n " . $this->dbo->quoteName('languagetranslated') . " = " . $this->dbo->quote($dbRecord[COM_GPTRANSLATE_TRANSLATED_LANGUAGE]) . "," .
							 "\n " . $this->dbo->quoteName('published') . " = " . (int)($dbRecord[COM_GPTRANSLATE_PUBLISHED]) . "," .
							 "\n " . $this->dbo->quoteName('translate_date') . " = " . $this->dbo->quote($dbRecord[COM_GPTRANSLATE_TRANSLATION_DATE]) . "," .
							 "\n " . $this->dbo->quoteName('translation_engine') . " = " . $this->dbo->quote($dbRecord[COM_GPTRANSLATE_TRANSLATION_ENGINE]) .
							 "\n WHERE " .
							 "\n " . $this->dbo->quoteName('pagelink') . " = " . $this->dbo->quote($dbRecord[COM_GPTRANSLATE_PAGELINK]);
					$this->dbo->setQuery ( $query );
				} else {
					$query = "INSERT INTO" .
							 "\n " . $this->dbo->quoteName('#__gptranslate') . "(" .
							 $this->dbo->quoteName('pagelink') . "," .
							 $this->dbo->quoteName('translations') . "," .
							 $this->dbo->quoteName('languageoriginal') . "," .
							 $this->dbo->quoteName('languagetranslated') . "," .
							 $this->dbo->quoteName('published') . "," .
							 $this->dbo->quoteName('translate_date') . "," .
							 $this->dbo->quoteName('translation_engine') . ") VALUES (" .
							 $this->dbo->quote($dbRecord[COM_GPTRANSLATE_PAGELINK]) . "," .
							 $this->dbo->quote($dbRecord[COM_GPTRANSLATE_TRANSLATIONS]) . "," .
							 $this->dbo->quote($dbRecord[COM_GPTRANSLATE_ORIGINAL_LANGUAGE]) . "," .
							 $this->dbo->quote($dbRecord[COM_GPTRANSLATE_TRANSLATED_LANGUAGE]) . "," .
							 (int)($dbRecord[COM_GPTRANSLATE_PUBLISHED]) . "," .
							 $this->dbo->quote($dbRecord[COM_GPTRANSLATE_TRANSLATION_DATE]) . "," .
							 $this->dbo->quote($dbRecord[COM_GPTRANSLATE_TRANSLATION_ENGINE]) . ")";
					$this->dbo->setQuery ( $query );
				}
				$this->dbo->execute ();
			}
		}
		catch(GptranslateException $e) {
			$this->setError($e);
			return false;
		} catch (\Exception $e) {
			$gptException = new GptranslateException(Text::sprintf('COM_GPTRANSLATE_ERROR_STORING_DATA', $e->getMessage()), 'error');
			$this->setError($gptException);
			return false;
		}
		
		return true;
	}

	/**
	 * Class constructor
	 * 
	 * @access public
	 * @param Object $dbo
	 * @param Object $app
	 * @return Object &
	 */
	public function __construct($dbo, $app) {
		// DB connector
		$this->dbo = $dbo;
		
		// Application
		$this->app = $app;
	}
}