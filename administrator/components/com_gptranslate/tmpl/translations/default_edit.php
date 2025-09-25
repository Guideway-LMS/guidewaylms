<?php 
/** 
 * @package GPTRANSLATE::LINKS::administrator::components::com_gptranslate
 * @subpackage views
 * @subpackage messages
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html 
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use JExtstore\Component\Gptranslate\Administrator\Framework\Helpers\Language;
$altFlags = $this->componentParams->get('alt_flags', []);
?>
<form action="index.php" method="post" name="adminForm" id="adminForm"> 
	<div class="card card-default accordion-group">
		<div class="card-header accordion-heading opened">
			<div class="accordion-toggle noaccordion">
				<h4><span class="icon-pencil" aria-hidden="true"></span><?php echo Text::_( 'COM_GPTRANSLATE_TRANSLATION_DETAILS' ); ?></h4>
			</div>
		</div>
		<div id="details" class="card-body card-block accordion-body">
	      	<div class="accordion-inner">
				<table class="admintable">
				<tbody>
					<tr>
						<td class="key left_title">
							<label for="pagelink" data-bs-content="<?php echo Text::_('COM_GPTRANSLATE_LINK_PAGE_DESC' ); ?>" class="hasPopover">
								<?php echo Text::_('COM_GPTRANSLATE_LINK_PAGE' ); ?>:
							</label>
						</td>
						<td class="right_details">
							<input class="form-control inputbox inputbox-large" type="text" name="pagelink" id="pagelink" data-validation="required" aria-required="true" aria-describedby="pagelink_arialbl" value="<?php echo $this->record->pagelink;?>"/>
							<small id="pagelink_arialbl" class="form-text text-muted"><?php echo Text::_('COM_GPTRANSLATE_LINK_PAGE_DESC')?></small>
						</td>
					</tr> 
					<tr>
						<td class="key left_title">
							<label for="name">
								<?php echo Text::_('COM_GPTRANSLATE_LANGUAGE_ORIGINAL' ); ?>:
							</label>
						</td>
						<td class="right_details">
							<input type="hidden" name="languageoriginal" id="languageoriginal" value="<?php echo $this->record->languageoriginal;?>"/>
							<?php
								$languageFlag = $this->record->languageoriginal != 'auto' ? '<img src="' . Uri::root(false) . 'media/mod_languages/images/' . preg_replace('/-.*/i', '', $this->record->languageoriginal) . '.gif" alt="flag"/> ' : '';
								
								if($this->record->languageoriginal == 'en' && in_array('usa', $altFlags)) {
									$this->record->languageoriginal = 'en-us';
									$languageFlag = $this->record->languageoriginal != 'auto' ? '<img src="' . Uri::root(false) . 'media/mod_languages/images/' . preg_replace('/-/i', '_', $this->record->languageoriginal) . '.gif" alt="flag"/> ' : '';
								}
								if($this->record->languageoriginal == 'tl') {
									$languageFlag = '<img style="width:18px;height:12px;margin-right:2px" src="' . Uri::root(false) . 'media/mod_gptranslate/flags/svg/tl.svg" alt="flag"/>';
								}
								
								echo $languageFlag . (Language::getInstance($this->record->languageoriginal)->loadLanguageTitle($this->record->languageoriginal) ? : Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_' . strtoupper($this->record->languageoriginal))); ?>
						</td>
					</tr>
					<tr>
						<td class="key left_title">
							<label for="name">
								<?php echo Text::_('COM_GPTRANSLATE_LANGUAGE_TRANSLATED' ); ?>:
							</label>
						</td>
						<td class="right_details">
							<input type="hidden" name="languagetranslated" id="languagetranslated" value="<?php echo $this->record->languagetranslated;?>"/>
							<?php 
							$languageFlag = $this->record->languagetranslated != 'auto' ? '<img src="' . Uri::root(false) . 'media/mod_languages/images/' . preg_replace('/-.*/i', '', $this->record->languagetranslated) . '.gif" alt="flag"/> ' : '';
							
							if($this->record->languagetranslated == 'en' && in_array('usa', $altFlags)) {
								$this->record->languagetranslated = 'en-us';
								$languageFlag = $this->record->languagetranslated != 'auto' ? '<img src="' . Uri::root(false) . 'media/mod_languages/images/' . preg_replace('/-/i', '_', $this->record->languagetranslated) . '.gif" alt="flag"/> ' : '';
							}
							if($this->record->languagetranslated == 'tl') {
								$languageFlag = '<img style="width:18px;height:12px;margin-right:2px" src="' . Uri::root(false) . 'media/mod_gptranslate/flags/svg/tl.svg" alt="flag"/>';
							}
							
							
							echo $languageFlag . (Language::getInstance($this->record->languagetranslated)->loadLanguageTitle($this->record->languagetranslated) ? : Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_' . strtoupper($this->record->languagetranslated))); ?>
						</td>
					</tr>
					<tr>
						<td class="key left_title">
							<label>
								<?php echo Text::_('COM_GPTRANSLATE_TRANSLATION_PUBLISHED' ); ?>:
							</label>
						</td>
						<td class="right_details">
							<fieldset class="radio btn-group" data-bs-toggle="buttons">
								<?php echo $this->lists['published']; ?>
							</fieldset>
						</td>
					</tr>
					<tr>
						<td class="key left_title">
							<label for="translate_date" data-bs-content="<?php echo Text::_('COM_GPTRANSLATE_LINK_PAGE_DESC' ); ?>">
								<?php echo Text::_('COM_GPTRANSLATE_TRANSLATION_DATE' ); ?>:
							</label>
						</td>
						<td class="right_details">
							<div class="input-group">
								<span class="input-group-text input-group-text-sideborder"><span class="icon-calendar" aria-hidden="true"></span></span>
								<?php 
									// Transform the date string, get date time in UTC from DB
									$dateObject = Factory::getDate($this->record->translate_date);
									// Set local time zone
									$dateObject->setTimezone(new \DateTimeZone($this->joomlaConfig));
								?>
								<input class="form-control inputbox" type="text" name="translate_date" id="translate_date" data-validation="required" data-role="calendar" aria-required="true" aria-describedby="date_translated_arialbl" value="<?php echo $dateObject->format('Y-m-d H:i:s', true, false);?>"/>
							</div>
							
							<small id="date_translated_arialbl" class="form-text text-muted"><?php echo Text::_('COM_GPTRANSLATE_TRANSLATION_DATE_DESC')?></small>
						</td>
					</tr>
					
					<tr>
						<td class="key left_title">
							<label>
								<?php echo Text::_('COM_GPTRANSLATE_CHATGPT_TRANSLATION_ENGINE' ); ?>:
							</label>
						</td>
						<td class="right_details">
							<fieldset class="radio btn-group" data-bs-toggle="buttons">
								<span class="badge bg-primary">
									<?php echo $this->record->translation_engine == 'chatgpt' ? Text::_('COM_GPTRANSLATE_CHATGPT_TRANSLATION_ENGINE_CHATGPT') : Text::_('COM_GPTRANSLATE_CHATGPT_TRANSLATION_ENGINE_GTRANSLATE');?>
								</span>
							</fieldset>
						</td>
					</tr>
					
					<tr>
						<td class="key left_title left_title_top">
							<label for="description" data-bs-content="<?php echo Text::_('COM_GPTRANSLATE_TRANSLATIONS_DESC' ); ?>" class="hasPopover">
								<?php echo Text::_('COM_GPTRANSLATE_TRANSLATIONS' ); ?>:
							</label>
						</td>
						<td class="right_details">
							<div aria-describedby="linkparams_arialbl">
				                <div class="translations-container">
					                <div class="card card-default accordion-group responsivestats">
										<div class="card-header p-0 accordion-heading opened">
											<div class="accordion-toggle accordion_lightblue noaccordion">
												<div class="input-group">
													<span class="input-group-text" aria-label="Filter"><span class="icon-filter" aria-hidden="true"></span> <?php echo Text::_('COM_GPTRANSLATE_FILTER');?></span>
													<input type="text" name="search" id="search" value="" class="text_area">
													<button class="btn btn-primary btn-sm" data-role="search-translations" onclick="return false;"><?php echo Text::_('COM_GPTRANSLATE_GO');?></button>
													<button class="btn btn-primary btn-sm" data-role="reset-search" onclick="return false;"><?php echo Text::_('COM_GPTRANSLATE_RESET');?></button>
												</div>
											</div>
										</div>
										<div class="card-body card-block ps-3 accordion-body accordion-inner">
						                    <button type="button" class="group-add group-add-start btn btn-sm btn-success" aria-label="<?php echo Text::_('COM_GPTRANSLATE_ADDNEW' ); ?>">
												<span class="icon-plus" aria-hidden="true"></span> <?php echo Text::_('COM_GPTRANSLATE_ADDNEW' ); ?>
							                </button>
											<?php foreach ($this->record->translations as $original=>$translated) {?>
												<div class="translation-row">
													<label class="badge bg-primary"><?php echo Text::_('COM_GPTRANSLATE_TEXT_ORIGINAL' ); ?></label>
													<textarea name="translations[original][]" data-role="original" data-validation="required"><?php echo $original;?></textarea>
													<label class="badge bg-primary"><?php echo Text::_('COM_GPTRANSLATE_TEXT_TRANSLATED' ); ?></label>
													<textarea name="translations[translated][]" data-role="translated" data-validation="required"><?php echo $translated;?></textarea>
													<button type="button" class="group-remove btn btn-sm btn-danger" aria-label="<?php echo Text::_('COM_GPTRANSLATE_DELETE' ); ?>">
									                    <span class="icon-minus" aria-hidden="true"></span> <?php echo Text::_('COM_GPTRANSLATE_DELETE' ); ?>
									                </button>
									                <button type="button" class="group-sync btn btn-sm btn-warning btn-invisible hasPopover" data-bs-title="<?php echo Text::_('COM_GPTRANSLATE_SYNC_TITLE');?>" data-bs-content="<?php echo Text::_('COM_GPTRANSLATE_SYNC_DESC');?>" aria-label="<?php echo Text::_('COM_GPTRANSLATE_SYNC' ); ?>">
									                    <span class="icon-refresh" aria-hidden="true"></span> <?php echo Text::_('COM_GPTRANSLATE_SYNC' ); ?>
									                </button>
												</div>						
											<?php }?>
										</div>
									</div>
								</div>
							</div>
							<small id="linkparams_arialbl" class="form-text text-muted"><?php echo Text::_('COM_GPTRANSLATE_LINK_PARAMS_DESC')?></small>
						</td>
					</tr>
				</tbody>
				</table>
			</div>
		</div>
	</div>		
	
	<div class="clr"></div>
 
	<input type="hidden" name="option" value="<?php echo $this->option;?>" /> 
	<input type="hidden" name="id" value="<?php echo $this->record->id; ?>" />
	<input type="hidden" name="translation_engine" value="<?php echo $this->record->translation_engine; ?>" />
	<input type="hidden" name="task" value="" /> 
</form>