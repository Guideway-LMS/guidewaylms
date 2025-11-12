<?php
/**
 * @version   $Id$
 * @package   GTranslate
 * @copyright Copyright (C) 2008-2023 GTranslate Inc. All rights reserved.
 * @license   GNU/GPL v3 http://www.gnu.org/licenses/gpl.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\String\StringHelper;

$settings = $params->toArray ();
if($settings ['disable_bootstrap_css'] != 1) {
	HTMLHelper::_ ( 'bootstrap.loadcss' );
}

// Move default language to the first one in the list
if($settings ['default_language_first']) {
	$defaultLanguageKeyIndex = array_search($settings ['language'], $settings ['languages']);
	if ($defaultLanguageKeyIndex !== false) {
		// Remove the 'de' element from its current position
		$defaultLanguage = $settings ['languages'][$defaultLanguageKeyIndex];
		unset($settings ['languages'][$defaultLanguageKeyIndex]);
		
		// Re-index the array to maintain numerical indexes
		$settings ['languages'] = array_values($settings ['languages']);
		
		// Add 'de' to the beginning of the array
		array_unshift($settings ['languages'], $defaultLanguage);
	}
}

$gpt_settings = array (
		'default_language' => $settings ['language'],
		'languages' => $settings ['languages'],
		'wrapper_selector' => $settings ['wrapper_selector'],
		'float_switcher_open_direction' => $settings ['float_switcher_open_direction'],
		'detect_browser_language' => $settings ['detect_browser_language'],
		'detect_current_language' => $settings ['detect_current_language'],
		'detect_default_language' => $settings ['detect_default_language'],
		'autotranslate_detected_language' => $settings ['autotranslate_detected_language'],
		'always_detect_autotranslated_language' => $settings ['always_detect_autotranslated_language'],
		'widget_text_color' => $settings['widget_text_color'],
		'show_language_titles' => $settings ['show_language_titles'],
		'enable_dropdown' => $settings ['enable_dropdown'],
		'equal_widths' => $settings ['equal_widths'],
		'reader_button_position' => $settings ['reader_button_position'],
		'custom_css' => $settings ['custom_css']
);

$alt_flags = array ();
$raw_alt_flags = isset($settings ['alt_flags']) ? $settings ['alt_flags'] : [];
foreach ( $raw_alt_flags as $country ) {
	if ($country == 'usa' or $country == 'canada')
		$alt_flags ['en'] = $country;
	elseif ($country == 'brazil')
		$alt_flags ['pt'] = $country;
	elseif ($country == 'mexico' or $country == 'argentina' or $country == 'colombia')
		$alt_flags ['es'] = $country;
	elseif ($country == 'quebec')
		$alt_flags ['fr'] = $country;
}
$gpt_settings ['alt_flags'] = $alt_flags;

$float_position = $settings ['float_position'];

if($float_position != 'inline'){
	list ( $switcher_vertical_position, $switcher_horizontal_position ) = explode ( '-', $float_position );
} else {
	list ( $switcher_vertical_position, $switcher_horizontal_position ) = ['inline', 'inline'];
}

$gpt_settings ['switcher_horizontal_position'] = $switcher_horizontal_position;
$gpt_settings ['switcher_vertical_position'] = $switcher_vertical_position;

if($settings ['disable_control']) {
	$gpt_settings ['wrapper_selector'] = '';
}

$widget_code = '';
if ($gpt_settings ['wrapper_selector'] == '.gptranslate_wrapper') {
	$gpt_settings ['wrapper_selector'] = '#gpt-wrapper-' . $module->id;
	$widget_code .= '<div class="gptranslate_wrapper" id="gpt-wrapper-' . $module->id . '"></div>';
}

$uri = Uri::getInstance ();
$document = Factory::getDocument ();

$orig_url = $uri->toString ( array (
		'path',
		'query'
) );
$orig_domain = $uri->getHost ();

$app = Factory::getApplication();
$doc = $app->getDocument();
$wa = $doc->getWebAssetManager();

array_map ( function ($script) use ($wa) {
	$wa->useScript ( 'bootstrap.' . $script );
}, [
	'toast'
] );

$bas64FunctionNameEncode = 'base'. 64 . '_encode';
$ajaxEndpoint = Uri::root ( false ) . 'index.php?option=com_ajax&plugin=gptranslate&format=json';
$wa->addInlineScript(	'var gptServerSideLink = "' . $ajaxEndpoint . '";' .
						'var gptLiveSite = "' . Uri::base() . '";' .
						'var gptMaxTranslationsPerRequest = ' . $settings ['max_translations_per_request'] . ';' .
						'var maxCharactersPerRequest = ' . $settings ['max_characters_per_request'] . ';' .
						'var gptRewriteLanguageUrl = ' . $settings ['rewrite_language_url'] . ';' .
						'var gptRewriteDefaultLanguageUrl = ' . $settings ['rewrite_default_language_url'] . ';' .
						'var gptRewritePageLinks = ' . $settings ['rewrite_page_links'] . ';' .
						'var gptTranslateMetadata = ' . $settings ['translate_metadata'] . ';' .
						'var gptSetHtmlLang = ' . $settings ['set_html_lang'] . ';' .
						'var gptAddCanonical = ' . $settings ['add_canonical'] . ';' .
						'var gptAddAlternate = ' . $settings ['add_alternate'] . ';' .
						'var gptSubfolderInstallation = ' . $settings ['subfolder_installation'] . ';' .
						'var gptIgnoreQuerystring = ' . $settings ['ignore_querystring'] . ';' .
						'var gptChatgptGtranslateRequestDelay = ' . (int)$settings ['chatgpt_gtranslate_request_delay'] . ';' .
						'var chatgptApiKey = "' . $bas64FunctionNameEncode($settings ['chatgpt_apikey']) . '";' .
						'var chatgptApiModel = "' . $settings ['chatgpt_model'] . '";' .
						'var chatgptRequestMessage = "' . StringHelper::str_ireplace('"', '', $settings ['chatgpt_request_message'])  . '";' .
						'var chatgptRequestConversationMode = "' . $settings ['chatgpt_request_conversation_mode'] . '";' .
						'var chatgptEnableReader = ' . $settings ['enable_reader']  . ';' .
						'var chatgptResponsivevoiceLanguageGender = "' . $settings ['responsivevoice_language_gender'] . '";' .
						'var chatgptResponsivevoiceApiKey = "' . $settings ['responsivevoice_apikey'] . '";' .
						'var chatgptChunksize = "' . $settings ['chunksize'] . '";' . 
						'var chatgptCssSelectorLeafnodesExcluded = "' . StringHelper::str_ireplace('"', '', trim(trim(preg_replace('/,+/', ',', StringHelper::str_ireplace(["\r", "\n"], ",", $settings ['css_selector_leafnodes_excluded'])),',')))  . '";' .
						'var chatgptWordsLeafnodesExcluded = "' . StringHelper::str_ireplace('"', '', preg_replace('/[, ]+$/', '', $settings ['words_leafnodes_excluded']))  . '";' .
						'var chatgptWordsMinLength = "' . (int)$settings['words_min_length']  . '";' . 
						'var chatgptMainpageSelector = "' . StringHelper::str_ireplace('"', '', $settings ['mainpage_selector'])  . '";' .
						'var chatgptElementsToExcludeCustom = "' . StringHelper::str_ireplace('"', '', trim($settings ['elements_toexclude_custom']))  . '";' .
						'var chatgptPopupFontsize = ' . (int)$settings['popup_fontsize']  . ';' .
						'var chatgptDraggableWidget = ' . (int)$settings ['draggable_widget']  . ';' .
						'var gptAudioVolume = ' . $settings ['responsivevoice_volume_tts'] . ';' .
						'var gptVoiceSpeed = "' . $settings ['responsivevoice_voice_speed'] . '";' .
						'var gTranslateEngine = ' . (($settings ['google_translate_engine'] || !trim($settings ['chatgpt_apikey'])) ? 1 : 0) . ';' .
						'var gptDisableControl = ' . $settings ['disable_control'] . ';' .
						'var svgIconArrow = \'<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 285 285"><path d="M282 76.5l-14.2-14.3a9 9 0 0 0-13.1 0L142.5 174.4 30.3 62.2a9 9 0 0 0-13.2 0L3 76.5a9 9 0 0 0 0 13.1l133 133a9 9 0 0 0 13.1 0l133-133a9 9 0 0 0 0-13z" style="fill:%23' . StringHelper::str_ireplace('#', '', $settings['widget_text_color'])  . '"/></svg>\'');

// Manage repeatable fields for words exclusion by language
$words_leafnodes_excluded_bylanguage_repeatable = isset($settings['words_leafnodes_excluded_bylanguage_repeatable']) && is_array($settings['words_leafnodes_excluded_bylanguage_repeatable']) ? json_encode($settings['words_leafnodes_excluded_bylanguage_repeatable']) : false;
if($words_leafnodes_excluded_bylanguage_repeatable) {
	$wa->addInlineScript( 'var chatgptWordsLeafnodesExcludedByLanguage = ' . $words_leafnodes_excluded_bylanguage_repeatable . ';' );
}

// Translations
$wa->addInlineScript(	'var MOD_GPTRANSLATE_TRANSLATING="' . Text::_('MOD_GPTRANSLATE_TRANSLATING', true) . '";' .
						'var MOD_GPTRANSLATE_TRANSLATING_WAIT="' . Text::_('MOD_GPTRANSLATE_TRANSLATING_WAIT', true) . '";' .
						'var MOD_GPTRANSLATE_TRANSLATING_COMPLETE="' . Text::_('MOD_GPTRANSLATE_TRANSLATING_COMPLETE', true) . '";' .
						'var MOD_GPTRANSLATE_READING_INPROGRESS="' . Text::_('MOD_GPTRANSLATE_READING_INPROGRESS', true) . '";' .
						'var MOD_GPTRANSLATE_READING_END="' . Text::_('MOD_GPTRANSLATE_READING_END', true) . '";' .
						'var MOD_GPTRANSLATE_READING_EMPTY="' . Text::_('MOD_GPTRANSLATE_READING_EMPTY', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_AF="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_AF', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_SQ="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_SQ', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_AM="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_AM', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_AR="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_AR', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_HY="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_HY', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_AZ="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_AZ', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_EU="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_EU', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_BE="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_BE', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_BN="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_BN', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_BS="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_BS', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_BG="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_BG', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_CA="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_CA', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_CEB="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_CEB', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_NY="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_NY', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_ZH_CN="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_ZH_CN', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_ZH_TW="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_ZH_TW', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_CO="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_CO', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_HR="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_HR', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_CS="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_CS', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_DA="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_DA', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_NL="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_NL', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_EN="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_EN', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_EO="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_EO', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_ET="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_ET', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_TL="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_TL', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_FI="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_FI', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_FR="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_FR', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_FY="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_FY', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_GL="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_GL', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_KA="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_KA', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_DE="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_DE', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_EL="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_EL', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_GU="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_GU', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_HT="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_HT', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_HA="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_HA', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_HAW="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_HAW', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_IW="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_IW', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_HI="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_HI', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_HMN="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_HMN', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_HU="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_HU', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_IS="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_IS', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_IG="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_IG', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_ID="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_ID', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_GA="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_GA', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_IT="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_IT', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_JA="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_JA', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_JW="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_JW', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_KN="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_KN', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_KK="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_KK', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_KM="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_KM', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_KO="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_KO', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_KU="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_KU', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_KY="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_KY', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_LO="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_LO', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_LA="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_LA', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_LV="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_LV', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_LT="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_LT', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_LB="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_LB', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_MK="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_MK', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_MG="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_MG', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_MS="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_MS', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_ML="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_ML', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_MT="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_MT', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_MI="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_MI', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_MR="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_MR', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_MN="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_MN', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_MY="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_MY', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_NE="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_NE', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_NO="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_NO', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_PS="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_PS', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_FA="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_FA', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_PL="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_PL', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_PT="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_PT', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_PA="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_PA', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_RO="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_RO', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_RU="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_RU', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_SM="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_SM', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_GD="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_GD', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_SR="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_SR', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_ST="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_ST', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_SN="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_SN', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_SD="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_SD', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_SI="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_SI', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_SK="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_SK', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_SL="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_SL', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_SO="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_SO', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_ES="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_ES', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_SU="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_SU', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_SW="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_SW', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_SV="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_SV', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_TG="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_TG', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_TA="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_TA', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_TE="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_TE', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_TH="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_TH', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_TR="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_TR', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_UK="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_UK', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_UR="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_UR', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_UZ="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_UZ', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_VI="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_VI', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_CY="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_CY', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_XH="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_XH', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_YI="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_YI', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_YO="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_YO', true) . '";' .
						'var MOD_GPTRANSLATE_LANGUAGE_NAME_ZU="' . Text::_('MOD_GPTRANSLATE_LANGUAGE_NAME_ZU', true) . '";'
		);

$base_path = Uri::root () . 'media/mod_gptranslate';
$gpt_settings ['flags_location'] = $base_path . '/flags/';
$gpt_settings ['flag_loading'] = $settings ['flag_loading'];
$gpt_settings ['flag_style'] = $settings ['flag_style'];
$gpt_settings ['widget_max_height'] = $settings ['widget_max_height'];

$wa->addInlineScript ( "window.gptranslateSettings = window.gptranslateSettings || {};window.gptranslateSettings['" . $module->id . "'] = " . json_encode ( $gpt_settings ) . ";" );

if($settings['proxy_responsive_loading_script'] == 1) {
	$wa->registerAndUseScript ( 'gptranslate.responsivevoice', 'media/mod_gptranslate/js/responsivevoice.js', [ ], [
			'defer' => 'defer'
	] );
} else {
	$wa->registerAndUseScript ( 'gptranslate.responsivevoice', 'https://code.responsivevoice.org/responsivevoice.js?key=' . $settings ['responsivevoice_apikey'], [ ], [ 
			'defer' => 'defer'
	] );
}
$wa->registerAndUseScript ( 'gptranslate.jsonrepair', 'media/mod_gptranslate/js/jsonrepair/index.js', [ ], [ 'type' => 'module' ]);
$wa->registerAndUseScript ( 'gptranslate.mainscript', 'media/mod_gptranslate/js/gptranslate.js', [ ], [
		'data-gt-orig-url' => $orig_url,
		'data-gt-orig-domain' => $orig_domain,
		'data-gt-widget-id' => $module->id,
		'type' => 'module'
]);

$wa->addInlineStyle('div.gpt_float_switcher .gt-selected,div.gpt_float_switcher,div.gpt_options{background-color:' . ($settings['widget_background_color'] ? : '#FFFFFF') . '}' .
					'div.gpt_float_switcher,div.gpt_float_switcher div.gt-selected div.gpt-current-lang,div.gpt_float_switcher, div.gpt_float_switcher div.gpt_options a{color:' . ($settings['widget_text_color'] ? : '#000000') . ';font-size: ' . $settings['popup_fontsize'] . 'px}' .
					'div.gpt_float_switcher{border-radius:' . $settings['popup_border_radius'] . 'px}' .
					'div.gpt_float_switcher img,svg.svg-inline--fa{box-sizing:border-box;width:' . $settings['popup_iconsize'] . 'px}');

echo $widget_code;
