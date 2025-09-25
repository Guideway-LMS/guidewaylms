<?php
namespace JExtstore\Component\Gptranslate\Administrator\Table;
/**
 *
 * @package GPTRANSLATE::USERS::administrator::components::com_gptranslate
 * @subpackage tables
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html 
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\Database\DatabaseDriver;
use Joomla\Event\DispatcherInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory;
use JExtstore\Component\Gptranslate\Administrator\Framework\Exception\Exceptions;

/**
 * Tracking of links redirected by the plugin
 *
 * @package GPTRANSLATE::USERS::administrator::components::com_gptranslate
 * @subpackage tables
 * @since 1.6
 */
class TranslationsTable extends Table {
	use Exceptions;
	
	/**
	 * @var int Primary key
	 */
	public $id = 0;
	
	/**
	 * @var string
	 */
	public $pagelink = '';
	
	/**
	 * @var string
	 */
	public $translations = '';
	
	/**
	 * @var string
	 */
	public $languageoriginal = '';
	
	/**
	 * @var string
	 */
	public $languagetranslated = '';
	
	/**
	 *
	 * @var int
	 */
	public $checked_out = null;
	
	/**
	 *
	 * @var datetime
	 */
	public $checked_out_time = null;
	
	/**
	 * @var int
	 */
	public $published = 1;
	
	/**
	 * @var string
	 */
	public $translate_date = null;
	
	/**
	 * @var string
	 */
	public $translation_engine = null;
	
	/**
	 * Load Table override
	 * @override
	 *
	 * @see Table::load()
	 */
	public function load($idEntity = null, $reset = true) {
		// If not $idEntity set return empty object
		if($idEntity) {
			if(!parent::load ( $idEntity )) {
				return false;
			}
		}
		
		$this->translations = json_decode($this->translations, true);

		// Use uksort with the custom comparison function
		if(is_array($this->translations)) {
			uksort($this->translations, function ($a, $b) {
				return strlen($b) - strlen($a);
			});
		} else {
			$this->translations = [];
		}

		return true;
	}
	
	/**
	 * Bind Table override
	 * @override
	 *
	 * @see JTable::bind()
	 */
	public function bind($fromArray, $ignore = array(), $saveTask = false, $sessionTask = false) {
		parent::bind($fromArray);
		
		// Encode the JSON field structure
		if($saveTask) {
			// Transform the date string, get date time in UTC from DB
			$app = Factory::getApplication();
			$user = $app->getIdentity ();
			$joomlaConfig = $app->getConfig ();
			$originalTimezone = $user->getParam ( 'timezone', $joomlaConfig->get ( 'offset' ) );
			
			$dateObject = Factory::getDate($this->translate_date, $originalTimezone);
			// Set local time zone
			$dateObject->setTimezone(new \DateTimeZone('UTC'));
			
			$this->translate_date = $dateObject->format('Y-m-d H:i:s', false, false);
			
			if($this->translations) {
				$combinedTranslations = array_combine($this->translations['original'], $this->translations['translated']);
				$this->translations = json_encode($combinedTranslations);
			} else {
				$this->translations = json_encode([]);
			}
		}
		
		return true;
	}
	
	/**
	 * Class constructor
	 * @param DatabaseDriver $db DatabaseDriver object.
	 * @param DispatcherInterface  $dispatcher  Event dispatcher for this table
	 *
	 * return Object&
	 */
	public function __construct(DatabaseDriver $db, ?DispatcherInterface $dispatcher = null) {
		parent::__construct ( '#__gptranslate', 'id', $db, $dispatcher );
		
		// Support null values for datetime field
		$this->_supportNullValue = true;
	}
}