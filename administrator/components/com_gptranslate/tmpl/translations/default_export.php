<?php 
/** 
 * @package GPTRANSLATE::TRANSLATIONS::administrator::components::com_gptranslate
 * @subpackage views
 * @subpackage translations
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2024 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;

$delimiter = ';';
$enclosure = '"';

// Clean dirty buffer
ob_end_clean();
// Open buffer
ob_start();
// Open out stream
$outstream = fopen("php://output", "w");
// Funzione di scrittura nell'output stream
function __gptoutputCSV(&$vals, $index, $userData) {
	if(property_exists($vals, 'translations')) {
		// Translate published status field
		$translationsArray = (array)$vals;
		unset($translationsArray['id']);
		unset($translationsArray['checked_out']);
		unset($translationsArray['checked_out_time']);

		// Export records only if metainfo are assigned
		fputcsv($userData[0], $translationsArray, $userData[1], $userData[2]);
	}
}

// Echo delle intestazioni
fputcsv ( $outstream, array (
		Text::_ ( 'COM_GPTRANSLATE_PAGELINK' ),
		Text::_ ( 'COM_GPTRANSLATE_TRANSLATIONS' ),
		Text::_ ( 'COM_GPTRANSLATE_LANGUAGE_ORIGINAL' ),
		Text::_ ( 'COM_GPTRANSLATE_LANGUAGE_TRANSLATED' ),
		Text::_ ( 'COM_GPTRANSLATE_PUBLISHED' ),
		Text::_ ( 'COM_GPTRANSLATE_DATE' ),
		Text::_ ( 'COM_GPTRANSLATE_CHATGPT_TRANSLATION_ENGINE' )
), $delimiter, $enclosure );

// Output di tutti i records
array_walk($this->items, "__gptoutputCSV", array($outstream, $delimiter, $enclosure));
fclose($outstream);

// Recupero output buffer content
$contents = ob_get_clean();
$size = strlen($contents);

header ( 'Pragma: public' );
header ( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
header ( 'Expires: ' . gmdate ( 'D, d M Y H:i:s' ) . ' GMT' );
header ( 'Content-Disposition: attachment; filename="gptranslations_pg' . $this->pagination->pagesCurrent . '.csv"' );
header ( 'Content-Type: text/plain' );
header ( "Content-Length: " . $size );
echo $contents;
	
exit ();