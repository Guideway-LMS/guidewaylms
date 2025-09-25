<?php
namespace JExtstore\Component\Gptranslate\Administrator\Framework\Html;
/**  
 * @package GPTRANSLATE::administrator::components::com_gptranslate
 * @subpackage framework
 * @subpackage html
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\LanguageHelper;

/**
 * Languages available
 *
 * @package GPTRANSLATE::administrator::components::com_gptranslate
 * @subpackage framework
 * @subpackage html
 *        
 */
class Languages {

	/**
	 * Build the multiple select list for Menu Links/Pages
	 * 
	 * @access public
	 * @return array
	 */
	public static function getAvailableLanguageOptions() {
		$languagesArray = [
				'af' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_AF') ,
				'sq' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_SQ') ,
				'am' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_AM') ,
				'ar' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_AR') ,
				'hy' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_HY') ,
				'az' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_AZ') ,
				'eu' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_EU') ,
				'be' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_BE') ,
				'bn' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_BN') ,
				'bs' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_BS') ,
				'bg' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_BG') ,
				'ca' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_CA') ,
				'ceb' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_CEB') ,
				'ny' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_NY') ,
				'zh_cn' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_ZH_CN') ,
				'zh_tw' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_ZH_TW') ,
				'co' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_CO') ,
				'hr' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_HR') ,
				'cs' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_CS') ,
				'da' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_DA') ,
				'nl' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_NL') ,
				'en' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_EN') ,
				'eo' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_EO') ,
				'et' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_ET') ,
				'tl' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_TL') ,
				'fi' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_FI') ,
				'fr' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_FR') ,
				'fy' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_FY') ,
				'gl' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_GL') ,
				'ka' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_KA') ,
				'de' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_DE') ,
				'el' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_EL') ,
				'gu' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_GU') ,
				'ht' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_HT') ,
				'ha' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_HA') ,
				'haw' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_HAW') ,
				'iw' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_IW') ,
				'hi' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_HI') ,
				'hmn' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_HMN') ,
				'hu' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_HU') ,
				'is' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_IS') ,
				'ig' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_IG') ,
				'id' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_ID') ,
				'ga' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_GA') ,
				'it' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_IT') ,
				'ja' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_JA') ,
				'jw' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_JW') ,
				'kn' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_KN') ,
				'kk' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_KK') ,
				'km' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_KM') ,
				'ko' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_KO') ,
				'ku' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_KU') ,
				'ky' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_KY') ,
				'lo' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_LO') ,
				'la' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_LA') ,
				'lv' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_LV') ,
				'lt' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_LT') ,
				'lb' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_LB') ,
				'mk' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_MK') ,
				'mg' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_MG') ,
				'ms' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_MS') ,
				'ml' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_ML') ,
				'mt' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_MT') ,
				'mi' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_MI') ,
				'mr' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_MR') ,
				'mn' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_MN') ,
				'my' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_MY') ,
				'ne' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_NE') ,
				'no' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_NO') ,
				'ps' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_PS') ,
				'fa' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_FA') ,
				'pl' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_PL') ,
				'pt' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_PT') ,
				'pa' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_PA') ,
				'ro' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_RO') ,
				'ru' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_RU') ,
				'sm' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_SM') ,
				'gd' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_GD') ,
				'sr' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_SR') ,
				'st' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_ST') ,
				'sn' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_SN') ,
				'sd' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_SD') ,
				'si' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_SI') ,
				'sk' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_SK') ,
				'sl' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_SL') ,
				'so' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_SO') ,
				'es' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_ES') ,
				'su' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_SU') ,
				'sw' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_SW') ,
				'sv' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_SV') ,
				'tg' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_TG') ,
				'ta' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_TA') ,
				'te' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_TE') ,
				'th' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_TH') ,
				'tr' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_TR') ,
				'uk' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_UK') ,
				'ur' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_UR') ,
				'uz' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_UZ') ,
				'vi' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_VI') ,
				'cy' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_CY') ,
				'xh' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_XH') ,
				'yi' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_YI') ,
				'yo' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_YO') ,
				'zu' => Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_ZU')
		];
		
		$knownLangs = LanguageHelper::getLanguages();

		$langs[] = HTMLHelper::_('select.option',  '', Text::_('COM_GPTRANSLATE_ALL_LANGUAGES' ) );
		
		// Create found languages options
		foreach ($knownLangs as $langObject) {
			// Extract tag lang
			$langs[] = HTMLHelper::_('select.option',  $langObject->sef, $langObject->title );
		}
		$languageKeysExisting = array_map(function($element) {
			return $element->value;
		}, array_filter($langs, function($element) {
			return $element->value != '';
		}));
		
		// Add a selection of extra disting languages based on the ones used for translations that could be additional compared to Joomla installed languages an anyway unrelated
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = method_exists ( $db, 'createQuery' ) ? $db->createQuery () : $db->getQuery ( true );
		$query->select('DISTINCT ' . $db->quoteName('languagetranslated'))
			  ->from($db->quoteName('#__gptranslate'));
		$db->setQuery($query);
		$languagesTranslated = $db->loadColumn();
		
		if(!empty($languagesTranslated)) {
			foreach ($languagesTranslated as $languageKey) {
				if(!in_array($languageKey, $languageKeysExisting) && array_key_exists($languageKey, $languagesArray))  {
					$addedLanguageObject = new \stdClass();
					$addedLanguageObject->disable = false;
					$addedLanguageObject->text = $languagesArray[$languageKey];
					$addedLanguageObject->value =$languageKey;
					
					$langs[] = $addedLanguageObject;
				}
			}
		}
		usort($langs, function($a, $b) {
			return strcmp($a->text, $b->text);
		});
		
		return $langs;
	}
}