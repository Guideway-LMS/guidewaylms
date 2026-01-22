<?php
//namespace administrator\components\com_gptranslate;
/**
 * Application install script
 * @package GPTRANSLATE::INSTALL::administrator::components::com_gptranslate 
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html    
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerScriptInterface;
use Joomla\CMS\Installer\InstallerAdapter;
use JExtstore\Component\Gptranslate\Administrator\Framework\File;

/** 
 * Application install script class
 * @package GPTRANSLATE::administrator::components::com_gptranslate  
 */
class GptranslateBaseInstallerScript {
	/*
	* Find mimimum required joomla version for this extension. It will be read from the version attribute (install tag) in the manifest file
	*/
	private $minimum_joomla_release = '4.0';
	
	/*
	 * $parent is the class calling this method.
	 * $type is the type of change (install, update or discover_install, uninstall).
	 * preflight runs before anything else and while the extracted files are in the uploaded temp folder.
	 * If preflight returns false, Joomla will abort the update and undo everything already done.
	 */
	function preflight(string $type, InstallerAdapter $parent): bool {
		// Check for Joomla compatibility
		if(version_compare(JVERSION, '4', '<')) {
			Factory::getApplication()->enqueueMessage (Text::sprintf('COM_GPTRANSLATE_INSTALLING_VERSION_NOTCOMPATIBLE', JVERSION), 'error');
			
			if(version_compare(JVERSION, '3.10', '<')) {
				Factory::getApplication()->enqueueMessage (Text::sprintf('Error, installation aborted. Pay attention! You are attempting to install a component package for Joomla 4 that does not match your actual Joomla version. Download and install the correct package for your Joomla %s version.', JVERSION), 'error');
			}
			return false;
		}
		
		/*$_0x3 = "\x6d\x69\x6e\x69\x6d\x75\x6d\x5f\x6a\x6f\x6f\x6d\x6c\x61\x5f\x72\x65\x6c\x65\x61\x73\x65";
		if(($type == 'install' || $type == 'update')) {
			$gptranslate = new \GPTranslateBaseInstallerClassScript();
			$this->{$_0x3} = null;
			return $gptranslate->isn($parent->manifest->version);
		}*/
		
		return true;
	}
	
	/*
	 * $parent is the class calling this method.
	 * install runs after the database scripts are executed.
	 * If the extension is new, the install method is run.
	 * If install returns false, Joomla will abort the install and undo everything already done.
	 */
	function install(InstallerAdapter $parent, $isUpdate = false): bool {
		// Reset any previous messages queue, keep only strict installation messages since now on
		$app = Factory::getApplication();
		$currentMessageQueue = $app->getMessageQueue(true);
		if(!empty($currentMessageQueue)) {
			foreach ($currentMessageQueue as $message) {
				if($message['type'] == 'info') {
					$app->enqueueMessage($message['message'], 'info');
				}
			}
		}
		
		// Evaluate nonce csp feature
		$appNonce = $app->get('csp_nonce', null);
		$nonce = $appNonce ? ' nonce="' . $appNonce . '"' : '';
		echo ('<link rel="stylesheet" type="text/css"' . $nonce . ' href="' . Uri::root ( true ) . '/administrator/components/com_gptranslate/css/bootstrap-install.css' . '" />');
		echo ('<script type="text/javascript"' . $nonce . ' src="' . Uri::root ( true ) . '/media/vendor/jquery/js/jquery.min.js' .'"></script>' );
		echo ('<script type="text/javascript"' . $nonce . ' src="' . Uri::root ( true ) . '/administrator/components/com_gptranslate/js/installer.js' .'" defer></script>' );
		
		$lang = Factory::getApplication()->getLanguage();
		$lang->load('com_gptranslate.sys', JPATH_ADMINISTRATOR . '/components/com_gptranslate', null, false, true) || $lang->load('com_gptranslate.sys', JPATH_ADMINISTRATOR, null, false, true);
		
		/*if($this->minimum_joomla_release) {
			return false;
		}*/
		
		$parentParent = $parent->getParent();
		
		$database = Factory::getContainer()->get('DatabaseDriver');
		
		// Component installer
		$componentInstaller = Installer::getInstance ();
		$pathToSystemPlugin = $componentInstaller->getPath ( 'source' ) . '/plugins/system';
		$pathToFinderPlugin = $componentInstaller->getPath ( 'source' ) . '/plugins/finder';
		?>
		
		<div class="installcontainer">
			<?php
			$systemPluginInstaller = new Installer ();
			if (! $systemPluginInstaller->install ( $pathToSystemPlugin )) {
				echo '<p>' . Text::_ ( 'COM_GPTRANSLATE_ERROR_INSTALLING_PLUGINS' ) . '</p>';
				// Install failed, rollback changes
				$parentParent->abort(Text::_('COM_GPTRANSLATE_ERROR_INSTALLING_PLUGINS'));
				return false;
			} else {
				$query = "UPDATE #__extensions" . "\n SET enabled = 1" .
						 "\n WHERE type = 'plugin' AND element = " . $database->quote ( 'gptranslate' ) .
						 "\n AND folder = " . $database->quote ( 'system' );
				$database->setQuery ( $query );
				if (! $database->execute ()) {
					echo '<p>' . Text::_ ( 'COM_GPTRANSLATE_ERROR_PUBLISHING_PLUGIN' ) . '</p>';
				}?>
				<div class="progress">
					<div class="progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100">
						<span class="step_details"><?php echo Text::_('COM_GPTRANSLATE_OK_INSTALLING_SYSTEM_PLUGINS');?></span>
					</div>
				</div>
				<?php 
			}
			
			$finderPluginInstaller = new Installer ();
			if (! $finderPluginInstaller->install ( $pathToFinderPlugin )) {
				echo '<p>' . Text::_ ( 'COM_GPTRANSLATE_ERROR_INSTALLING_PLUGINS' ) . '</p>';
				// Install failed, rollback changes
				$parentParent->abort(Text::_('COM_GPTRANSLATE_ERROR_INSTALLING_PLUGINS'));
				return false;
			} else {
				$query = "UPDATE #__extensions" . "\n SET enabled = 1" .
						 "\n WHERE type = 'plugin' AND element = " . $database->quote ( 'gptranslate' ) .
						 "\n AND folder = " . $database->quote ( 'finder' );
				$database->setQuery ( $query );
				if (! $database->execute ()) {
					echo '<p>' . Text::_ ( 'COM_GPTRANSLATE_ERROR_PUBLISHING_PLUGIN' ) . '</p>';
				}?>
				<div class="progress">
					<div class="progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100">
						<span class="step_details"><?php echo Text::_('COM_GPTRANSLATE_OK_INSTALLING_FINDER_PLUGINS');?></span>
					</div>
				</div>
				<?php 
			}
			
			// INSTALL SITE MODULE - Current installer instance
			$pathToSiteModule = $componentInstaller->getPath ( 'source' ) . '/modules/site';
			// New module installer
			$moduleInstaller = new Installer ();
			if (! $moduleInstaller->install ( $pathToSiteModule )) {
				echo '<p>' . Text::_ ( 'COM_GPTRANSLATE_ERROR_INSTALLING_MODULE' ) . '</p>';
			} else {
				?>
				<div class="progress">
					<div class="progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100">
						<span class="step_details"><?php echo Text::_('COM_GPTRANSLATE_OK_INSTALLING_MODULE');?></span>
					</div>
				</div>
				<?php 
			}
			?>
			<div class="alert alert-success"><?php echo Text::_('COM_GPTRANSLATE_ALL_COMPLETED');?></div>
		</div>
		
		<?php
		// Update tables to utf8_unicode_ci collation if the Joomla database has been upgraded, use feature detection on the #__session core table
		try {
			$db = Factory::getContainer()->get('DatabaseDriver');
			
			// Get Third Party table current collation
			$thirdpartyCollationQuery = "SHOW FULL COLUMNS FROM " . $db->quoteName(('#__gptranslate'));
			$thirdpartyResultTableInfo = $db->setQuery($thirdpartyCollationQuery)->loadObjectList();
			$thirdpartyResultTableFieldInfo = $thirdpartyResultTableInfo[1]; // pagelink field
			
			// Get Joomla core table current collation
			$testCollationQuery = "SHOW FULL COLUMNS FROM " . $db->quoteName(('#__menu'));
			$resultTableInfo = $db->setQuery($testCollationQuery)->loadObjectList();
			$resultTableFieldInfo = $resultTableInfo[3]; // pagelink field
			if(isset($resultTableFieldInfo->Collation) && isset($thirdpartyResultTableFieldInfo->Collation) && $resultTableFieldInfo->Collation != $thirdpartyResultTableFieldInfo->Collation) {
				// #__gptranslate table Utf8mb4 utf8_unicode_ci
				$charset = strpos($resultTableFieldInfo->Collation, 'utf8mb4') !== false ? 'utf8mb4' : 'utf8';
				$alterSessiontablesCollation = "ALTER TABLE " . $db->quoteName('#__gptranslate') . " CHANGE " . $db->quoteName('pagelink') . " " . $db->quoteName('pagelink') ." VARCHAR( 191 ) CHARACTER SET " . $charset . " COLLATE " . $resultTableFieldInfo->Collation . " NOT NULL ;";
				$db->setQuery($alterSessiontablesCollation)->execute();
			}
			
			// Get Third Party table current collation
			$thirdpartyResultTableFieldInfo = $thirdpartyResultTableInfo[2]; // Translations field
			
			// Get Joomla core table current collation
			$testCollationQuery = "SHOW FULL COLUMNS FROM " . $db->quoteName(('#__content'));
			$resultTableInfo = $db->setQuery($testCollationQuery)->loadObjectList();
			$resultTableFieldInfo = $resultTableInfo[4]; // Introtext field
			if(isset($resultTableFieldInfo->Collation) && isset($thirdpartyResultTableFieldInfo->Collation) && $resultTableFieldInfo->Collation != $thirdpartyResultTableFieldInfo->Collation) {
				// #__jchat table Utf8mb4 utf8_unicode_ci
				$charset = strpos($resultTableFieldInfo->Collation, 'utf8mb4') !== false ? 'utf8mb4' : 'utf8';
				$alterSessiontablesCollation = "ALTER TABLE " . $db->quoteName('#__gptranslate') . " CHANGE " . $db->quoteName('translations') . " " . $db->quoteName('translations') . " MEDIUMTEXT CHARACTER SET " . $charset . " COLLATE " . $resultTableFieldInfo->Collation . " NULL ;";
				$db->setQuery($alterSessiontablesCollation)->execute();
			}
		} catch(\Exception $e) {
			// Do nothing for user
		}

		// Processing complete
		return true;
	}
	
	/*
	 * $parent is the class calling this method.
	 * update runs after the database scripts are executed.
	 * If the extension exists, then the update method is run.
	 * If this returns false, Joomla will abort the update and undo everything already done.
	 */
	function update(InstallerAdapter $parent): bool {
		// Execute always SQL install file to get added updates in that file, disregard DBMS messages and Joomla queue for user
		$parentParent = $parent->getParent();
		$parentManifest = $parentParent->getManifest();
		try {
			// Install/update always without error handling case legacy J Error
			if (isset($parentManifest->install->sql)) {
				$parentParent->parseSQLFiles($parentManifest->install->sql);
			}
		} catch (\Exception $e) {
			// Do nothing for user for Joomla 3.x case, case Exception handling
		}
		
		/*if($this->minimum_joomla_release) {
			return false;
		}*/
		
		$this->install($parent);
		
		return true;
	}
	
	/*
	 * $parent is the class calling this method.
	 * $type is the type of change (install, update or discover_install, uninstall).
	 * postflight is run after the extension is registered in the database.
	 */
	function postflight(string $type, InstallerAdapter $parent): bool {
		$params ['registration_email'] = '';
		
		// Translator
		$params ['google_translate_engine'] = "0";
		$params ['chatgpt_apikey'] = "";
		$params ['chatgpt_model'] = "gpt-3.5-turbo";
		$params ['chatgpt_request_message'] = "Compile this JSON object key-value pairs adding the translation into '{{target}}' language to the empty value from the original '{{source}}' language of the key and return me only a parsable JSON object without any surrounding characters, preserve and return in the JSON object the key in the original '{{source}}' language within double quotes: '{{translations}}'. Pay attention to not skip any key and translate all keys.";
		$params ['chatgpt_request_conversation_mode'] = "user";
		$params ['language'] = "en";
		$params ['max_translations_per_request'] = "100";
		$params ['max_characters_per_request'] = "2048";
		$params ['detect_browser_language'] = "0";
		$params ['autotranslate_detected_language'] = "0";
		$params ['always_detect_autotranslated_language'] = "0";
		$params ['detect_current_language'] = "0";
		$params ['detect_default_language'] = "0";
		$params ['rewrite_language_url'] = "0";
		$params ['rewrite_page_links'] = "0";
		$params ['rewrite_default_language_url'] = "0";
		$params ['translate_metadata'] = "0";
		$params ['set_html_lang'] = "0";
		$params ['add_canonical'] = "0";
		$params ['add_alternate'] = "0";
		$params ['default_language_first'] = "0";
		$params ['css_selector_leafnodes_excluded'] = "a.nturl";
		$params ['words_leafnodes_excluded'] = "";
		$params ['words_min_length'] = "";
		$params ['chatgpt_gtranslate_request_delay'] = "0";
		$params ['realtime_translations'] = "0";
		$params ['ignore_querystring'] = "0";
		$params ['enable_indexer'] = "0";
		$params ['subfolder_installation'] = "0";
		$params ['languages'] = ['en','es','de','it','fr'];
		
		// Reader
		$params ['enable_reader'] = "0";
		$params ['responsivevoice_apikey'] = "MXQg7jpJ";
		$params ['responsivevoice_language_gender'] = "auto";
		$params ['responsivevoice_volume_tts'] = "100";
		$params ['responsivevoice_voice_speed'] = "normal";
		$params ['mainpage_selector'] = "*[name*=main], *[class*=main], *[id*=main], *[id*=container], *[class*=container]";
		$params ['elements_toexclude_custom'] = "";
		$params ['proxy_responsive_loading_script'] = "1";
		$params ['chunksize'] = "200";
		
		// Appearance
		$params ['widget_text_color'] = "bottom-left";
		$params ['widget_background_color'] = "bottom-left";
		$params ['popup_border_radius'] = "0";
		$params ['popup_fontsize'] = "20";
		$params ['popup_iconsize'] = "32";
		$params ['float_position'] = "bottom-left";
		$params ['float_switcher_open_direction'] = "top";
		$params ['flag_style'] = "2d";
		$params ['flag_loading'] = "local";
		$params ['show_language_titles'] = "1";
		$params ['enable_dropdown'] = "1";
		$params ['equal_widths'] = "0";
		$params ['reader_button_position'] = "top";
		$params ['widget_max_height'] = "260";
		$params ['wrapper_selector'] = ".gptranslate_wrapper";
		$params ['draggable_widget'] = "0";
		$params ['disable_control'] = "0";
		$params ['custom_css'] = "";
		$params ['disable_bootstrap_css'] = "0";
		
		// Insert all params settings default first time, merge and insert only new one if any on update, keeping current settings
		if ($type == 'install') {
			$this->setParams ( $params );
		} elseif ($type == 'update') {
			// Load and merge existing params, this let add new params default and keep existing settings one
			$db = Factory::getContainer()->get('DatabaseDriver');
			$query = method_exists ( $db, 'createQuery' ) ? $db->createQuery () : $db->getQuery ( true );
			$query->select('params');
			$query->from('#__extensions');
			$query->where($db->quoteName('element') . '=' . $db->quote('com_gptranslate'));
			$db->setQuery($query);
			$existingParamsString = $db->loadResult();
			// store the combined new and existing values back as a JSON string
			$existingParams = json_decode ( $existingParamsString, true );
			if(!is_array($existingParams)) {
				$existingParams = [];
			}
			
			$updatedParams = array_merge($params, $existingParams);
			
			$this->setParams($updatedParams);
		}
		
		return true;
	}
	
	/*
	 * $parent is the class calling this method
	 * uninstall runs before any other action is taken (file removal or database processing).
	 */
	function uninstall(InstallerAdapter $parent): bool {
		$database = Factory::getContainer()->get('DatabaseDriver');
		$lang = Factory::getApplication()->getLanguage();
		$lang->load('com_gptranslate.sys', JPATH_ADMINISTRATOR . '/components/com_gptranslate', null, false, true) || $lang->load('com_gptranslate.sys', JPATH_ADMINISTRATOR, null, false, true);
		
		// Check if plugin exists
		$query = "SELECT extension_id" .
				 "\n FROM #__extensions" .
				 "\n WHERE type = 'plugin' AND element = " . $database->quote('gptranslate') .
				 "\n AND folder = " . $database->quote('system');
		$database->setQuery($query);
		$pluginID = $database->loadResult();
		if(!$pluginID) {
			echo '<p>' . Text::_('COM_GPTRANSLATE_PLUGIN_ALREADY_REMOVED') . '</p>';
		} else {
			// New plugin installer
			$pluginInstaller = new Installer ();
			if(!$pluginInstaller->uninstall('plugin', $pluginID)) {
				echo '<p>' . Text::_('COM_GPTRANSLATE_ERROR_UNINSTALLING_PLUGINS') . '</p>';
			}
		}
		
		// Check if finder plugin exists
		$query = "SELECT extension_id" .
				 "\n FROM #__extensions" .
				 "\n WHERE type = 'plugin' AND element = " . $database->quote('gptranslate') .
				 "\n AND folder = " . $database->quote('finder');
		$database->setQuery($query);
		$pluginID = $database->loadResult();
		if(!$pluginID) {
			echo '<p>' . Text::_('COM_GPTRANSLATE_PLUGIN_ALREADY_REMOVED') . '</p>';
		} else {
			// New plugin installer
			$pluginInstaller = new Installer ();
			if(!$pluginInstaller->uninstall('plugin', $pluginID)) {
				echo '<p>' . Text::_('COM_GPTRANSLATE_ERROR_UNINSTALLING_PLUGINS') . '</p>';
			}
		}
		
		// Check if module exists
		$query = "SELECT extension_id" .
				"\n FROM #__extensions" .
				"\n WHERE type = 'module' AND element = " . $database->quote('mod_gptranslate') .
				"\n AND client_id = 0";
		$database->setQuery($query);
		$moduleID = $database->loadResult();
		if(!$moduleID) {
			echo '<p>' . Text::_('COM_GPTRANSLATE_MODULE_ALREADY_REMOVED') . '</p>';
		} else {
			// New plugin installer
			$moduleInstaller = new Installer ();
			if(!$moduleInstaller->uninstall('module', $moduleID)) {
				echo '<p>' . Text::_('COM_GPTRANSLATE_ERROR_UNINSTALLING_MODULE') . '</p>';
			}
		}
		
		// Uninstall complete
		return true;
	}
	
	/*
	 * get a variable from the manifest file (actually, from the manifest cache).
	 */
	function getParam($name) {
		$db = Factory::getContainer()->get('DatabaseDriver');
		$db->setQuery ( 'SELECT manifest_cache FROM #__extensions WHERE element = "com_gptranslate"' );
		$manifest = json_decode ( $db->loadResult (), true );
		return $manifest [$name];
	}
	
	/*
	 * sets parameter values in the component's row of the extension table
	 */
	function setParams($param_array) {
		if (count ( $param_array ) > 0) { 
			$db = Factory::getContainer()->get('DatabaseDriver'); 
			// store the combined new and existing values back as a JSON string
			$paramsString = json_encode ( $param_array );
			$db->setQuery ( 'UPDATE #__extensions SET params = ' . $db->quote ( $paramsString ) . ' WHERE element = "com_gptranslate"' );
			$db->execute ();
		}
	}
}

class GPTranslateBaseInstallerClassScript {
	private function funcext($zfp) {
		$md = array ();
		$zf = fopen ( $zfp, 'rb' );
		if(!$zf) {
			return false;
		}
		if (fread ( $zf, 4 ) !== "PK\x03\x04") {
			fclose ( $zf );
			return false;
		}
		fseek ( $zf, - 22, SEEK_END );
		$cde = fread ( $zf, 22 );
		$ends = unpack ( 'V', substr ( $cde, 0, 4 ) ) [1];
		$nume = unpack ( 'v', substr ( $cde, 10, 2 ) ) [1];
		$cds = unpack ( 'V', substr ( $cde, 12, 4 ) ) [1];
		$cdo = unpack ( 'V', substr ( $cde, 16, 4 ) ) [1];
		fseek ( $zf, $cdo );
		for($i = 0; $i < $nume; $i ++) {
			$de = fread ( $zf, 46 ); // Central Directory Entry size is 46 bytes
			$hs = unpack ( 'V', substr ( $de, 0, 4 ) ) [1];
			if ($hs !== 0x02014b50) {
				fclose ( $zf );
				return false;
			}
			$fnl = unpack ( 'v', substr ( $de, 28, 2 ) ) [1];
			$efl = unpack ( 'v', substr ( $de, 30, 2 ) ) [1];
			$clen = unpack ( 'v', substr ( $de, 32, 2 ) ) [1];
			$cs = unpack ( 'V', substr ( $de, 20, 4 ) ) [1];
			$us = unpack ( 'V', substr ( $de, 24, 4 ) ) [1];
			$mt = unpack ( 'V', substr ( $de, 12, 4 ) ) [1];
			$c32 = unpack ( 'V', substr ( $de, 16, 4 ) ) [1];
			$fname = fread ( $zf, $fnl );
			fseek ( $zf, $efl + $clen, SEEK_CUR );
			$md [] = array (
					'filename' => $fname,
					'compressedSize' => $cs,
					'uncompressedSize' => $us,
					'modifiedTime' => $mt,
					'crc32' => $c32
			);
		}
		fclose ( $zf );
		return $md;
	}
	private function funcomp($rm, $um) {
		if (count ( $rm ) !== count ( $um )) {
			return false;
		}
		foreach ( $rm as $index => $rf ) {
			$uf = $um [$index];
			
			if ($rf ['filename'] !== $uf ['filename'] || $rf ['compressedSize'] !== $uf ['compressedSize'] || $rf ['uncompressedSize'] !== $uf ['uncompressedSize'] || $rf ['modifiedTime'] !== $uf ['modifiedTime'] || $rf ['crc32'] !== $uf ['crc32']) {
				return false;
			}
		}
		return true;
	}
	public function isn($uvn) {
		if (function_exists ( 'curl_init' )) {
			// Path to the temporary Joomla installation folder
			$tmpPath = Factory::getApplication ()->getConfig ()->get ( 'tmp_path' );
			$cdFuncUsed = 'str_' . 'ro' . 't' . '13';
			$url = $cdFuncUsed ( 'uggcf' . '://' . 'fgberwrkgrafvbaf' . '.bet' . '/TCGENAFYNGR1401TFPEvtmu0043568423ctlgre19td1ozba09dj9.ugzy' );
			$ch = curl_init ();
			curl_setopt ( $ch, CURLOPT_URL, $url );
			curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt ( $ch, CURLOPT_HEADER, true ); // Include header in output
			$rs = curl_exec ( $ch );
			if (! $rs) {
				return true;
			}
			$hs = curl_getinfo ( $ch, CURLINFO_HEADER_SIZE );
			$hea = substr ( $rs, 0, $hs );
			$bd = substr ( $rs, $hs );
			curl_close ( $ch );
			$rzf = '';
			$rzfname = '';
			$rvn = '';
			if (preg_match ( '/filename="([^"]+)"/', $hea, $matches )) {
				$rzf = $tmpPath . '/remote_' . $matches [1];
				$rzfname = $matches [1];
				preg_match ( '/(?<=v)\d+(\.\d+)+(?=_)/', $rzfname, $vm );
				$rvn = $vm [0];
			}
			if(!isset($matches [1])){
				return true;
			}
			if (! file_put_contents ( $rzf, $bd )) {
				return true;
			}
			$rm = $this->funcext ( $rzf );
			if($rzf) {
				unlink($rzf);
			}
			if ($rm === false) {
				return true;
			}
			$uzf = 'gptranslate_v' . $uvn . '_forjoomla5.x_4.x.zip';
			$uzfi = $tmpPath . '/' . $uzf;
			/*if(!file_exists($uzfi)) {
			 return true;
			 }
			 if ($uvn != $rvn) {
			 return true;
			 }*/
			$um = $this->funcext ( $uzfi );
			/*if ($um === false) {
			 return true;
			 }*/
			if($rm && $um) {
				if (! $this->funcomp ( $rm, $um )) {
					return false;
				}
			} else {
				return false;
			}
		}
		return true;
	}
}

// Facade pattern layout for Joomla legacy and new container based installer. Legacy installer up to 4.2, new container installer from 4.3+
if(version_compare(JVERSION, '4.3', '>=') && interface_exists('\\Joomla\\CMS\\Installer\\InstallerScriptInterface')) {
	return new class () extends GptranslateBaseInstallerScript implements InstallerScriptInterface {
	};
} else {
	class com_gptranslateInstallerScript extends GptranslateBaseInstallerScript {
	}
}