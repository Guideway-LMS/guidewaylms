<?php 
/** 
 * @package GPTRANSLATE::LINKS::administrator::components::com_gptranslate
 * @subpackage views
 * @subpackage links
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Factory;
use JExtstore\Component\Gptranslate\Administrator\Framework\Helpers\Language;
?>
<form action="index.php" method="post" name="adminForm" id="adminForm">
	<table class="full headerlist">
		<tr>
			<td class="left">
				<div class="input-group">
					<span class="input-group-text" aria-label="<?php echo Text::_('COM_GPTRANSLATE_FILTER');?>"><span class="icon-filter" aria-hidden="true"></span> <?php echo Text::_('COM_GPTRANSLATE_FILTER' ); ?>:</span>
					<input type="text" name="search" id="search" value="<?php echo htmlspecialchars($this->searchword, ENT_COMPAT, 'UTF-8');?>" class="text_area"/>
				</div>
				
				<button class="btn btn-primary btn-sm" onclick="this.form.submit();"><?php echo Text::_('COM_GPTRANSLATE_GO' ); ?></button>
				<button class="btn btn-primary btn-sm" onclick="document.getElementById('search').value='';this.form.submit();"><?php echo Text::_('COM_GPTRANSLATE_RESET' ); ?></button>
			</td>
			<td class="right d-flex justify-content-end">
				<div class="input-group d-none d-md-flex flex-end">
					<span class="input-group-text" aria-label="<?php echo Text::_('COM_GPTRANSLATE_STATE');?>"><span class="icon-filter" aria-hidden="true"></span> <?php echo Text::_('COM_GPTRANSLATE_STATE' ); ?></span>
					<?php
						echo $this->lists['state'];
						echo $this->lists['languages'];
						echo $this->lists['translationengine'];
						echo $this->pagination->getLimitBox();
					?>
				</div>
			</td>
		</tr>
	</table>

	<table class="adminlist table table-striped table-hover">
	<thead>
		<tr>
			<th width="1%">
				<?php echo Text::_('COM_GPTRANSLATE_NUM' ); ?>
			</th>
			<th width="1%">
				<input type="checkbox" name="checkall-toggle" value="" class="form-check-input" onclick="Joomla.checkAll(this)" />
			</th>
			<th class="title" width="25%">
				<?php echo HTMLHelper::_('grid.sort', 'COM_GPTRANSLATE_PAGELINK', 's.pagelink', @$this->orders['order_Dir'], @$this->orders['order'], 'translations.display' ); ?>
			</th>
			<th class="title" width="5%">
				<?php echo HTMLHelper::_('grid.sort', 'COM_GPTRANSLATE_LANGUAGE_ORIGINAL', 's.languageoriginal', @$this->orders['order_Dir'], @$this->orders['order'], 'translations.display' ); ?>
			</th>
			<th class="title" width="5%">
				<?php echo HTMLHelper::_('grid.sort', 'COM_GPTRANSLATE_LANGUAGE_TRANSLATED', 's.languagetranslated', @$this->orders['order_Dir'], @$this->orders['order'], 'translations.display' ); ?>
			</th>
			<th  width="5%" class="d-none d-md-table-cell">
				<?php echo HTMLHelper::_('grid.sort', 'COM_GPTRANSLATE_PUBLISHED', 's.published', @$this->orders['order_Dir'], @$this->orders['order'], 'translations.display' ); ?>
			</th>
			<th  width="5%" class="d-none d-md-table-cell">
				<?php echo HTMLHelper::_('grid.sort', 'COM_GPTRANSLATE_CHATGPT_TRANSLATION_ENGINE', 's.translation_engine', @$this->orders['order_Dir'], @$this->orders['order'], 'translations.display' ); ?>
			</th>
			<th  width="5%" class="d-none d-md-table-cell">
				<?php echo HTMLHelper::_('grid.sort', 'COM_GPTRANSLATE_DATE', 's.translate_date', @$this->orders['order_Dir'], @$this->orders['order'], 'translations.display' ); ?>
			</th>
			<th class="title d-none d-md-table-cell" style="width:2%">
				<?php echo HTMLHelper::_('grid.sort', 'COM_GPTRANSLATE_ID', 's.id', @$this->orders['order_Dir'], @$this->orders['order'], 'translations.display' ); ?>
			</th>
		</tr>
	</thead>
	<?php
	$k = 0;
	$canCheckin = $this->user->authorise('core.manage', 'com_checkin');
	$nullDate = $this->getModel()->getDbo()->getNullDate();
	$nowDate = Factory::getDate()->toUnix();
	$tz = $this->user->getTimezone();
	$altFlags = $this->componentParams->get('alt_flags', []);
	for ($i=0, $n=count( $this->items ); $i < $n; $i++) {
		$row = $this->items[$i];
		$link =  'index.php?option=com_gptranslate&task=translations.editEntity&cid[]='. $row->id ;

		// Menu item status
		$titlePublishing 	= !$row->published ? Text::_( 'Publish' ) : Text::_( 'Unpublish' );
		// Access check.
		if($this->user->authorise('core.edit.state', 'com_gptranslate')) {
			$taskPublishing	= !$row->published ? 'translations.publish' : 'translations.unpublish';
			
			$published = '<a href="javascript:void(0);" onclick="return Joomla.listItemTask(\'cb' . $i . '\',\'' . $taskPublishing . '\')">';
			$published .= $row->published ? 
				'<img class="hasTooltip" title="' . $titlePublishing . '" alt="' . $titlePublishing . '" src="' . Uri::base(true) . '/components/com_gptranslate/images/icon-16-tick.png" width="16" height="16"/>' : 
				'<img class="hasTooltip" title="' . $titlePublishing . '" alt="' . $titlePublishing . '" src="' . Uri::base(true) . '/components/com_gptranslate/images/publish_x.png" width="16" height="16"/>';
				$published .= '</a>';
		} else {
			$altPublishing 	= $row->published ? Text::_( 'Published' ) : Text::_( 'Unpublished' );
			$published = $row->published ? 
				'<img class="hasTooltip" title="' . $titlePublishing . '" alt="' . $titlePublishing . '" src="' . Uri::base(true) . '/components/com_gptranslate/images/icon-16-tick.png" width="16" height="16" alt="unpublish" />' : 
				'<img class="hasTooltip" title="' . $titlePublishing . '" alt="' . $titlePublishing . '" src="' . Uri::base(true) . '/components/com_gptranslate/images/publish_x.png" width="16" height="16" alt="publish" />';
		}
		
		$checked = null;
		// Access check.
		if($this->user->authorise('core.edit', 'com_gptranslate')) {
			$checked = $row->checked_out && $row->checked_out != $this->user->id ? 
						HTMLHelper::_('jgrid.checkedout', $i, Factory::getContainer()->get(\Joomla\CMS\User\UserFactoryInterface::class)->loadUserById($row->checked_out)->name, $row->checked_out_time, 'translations.', $canCheckin) . '<input type="checkbox" style="display:none" data-enabled="false" id="cb' . $i . '" name="cid[]" value="' . $row->id . '"/>': 
						HTMLHelper::_('grid.id', $i, $row->id);
		} else {
			$checked = '<input type="checkbox" style="display:none" data-enabled="false" id="cb' . $i . '" name="cid[]" value="' . $row->id . '"/>';
		}
		?>
		<tr>
			<td align="center">
				<?php echo $this->pagination->getRowOffset($i); ?>
			</td>
			<td>
				<?php echo $checked; ?>
			</td>
			<td>
			<?php
				if ( ($row->checked_out && ( $row->checked_out != $this->user->get ('id'))) || !$this->user->authorise('core.edit', 'com_gptranslate') ) {
					echo $row->pagelink;
				} else {
					?>
					<a href="<?php echo $link; ?>" title="<?php echo Text::_('COM_GPTRANSLATE_EDIT_LINK' ); ?>">
						<span class='fas fa-pen-square' aria-hidden='true'></span> 
						<?php echo $row->pagelink; ?>
					</a>
					<?php
				}
			?>
			<a target="blank" href="<?php echo $row->pagelink; ?>"> &nbsp;&nbsp;<span class="icon-out" aria-hidden="true"></span></a>
			</td>
			<td>
				<?php
					$languageFlag = $row->languageoriginal != 'auto' ? '<img src="' . Uri::root(false) . 'media/mod_languages/images/' . preg_replace('/-.*/i', '', $row->languageoriginal) . '.gif" alt="flag"/> ' : '';
					
					if($row->languageoriginal == 'en' && in_array('usa', $altFlags)) {
						$row->languageoriginal = 'en-us';
						$languageFlag = $row->languageoriginal != 'auto' ? '<img src="' . Uri::root(false) . 'media/mod_languages/images/' . preg_replace('/-/i', '_', $row->languageoriginal) . '.gif" alt="flag"/> ' : '';
					}
					if($row->languageoriginal == 'tl') {
						$languageFlag = '<img style="width:18px;height:12px;margin-right:2px" src="' . Uri::root(false) . 'media/mod_gptranslate/flags/svg/tl.svg" alt="flag"/>';
					}
					
					echo $languageFlag . (Language::getInstance($row->languageoriginal)->loadLanguageTitle($row->languageoriginal) ? : Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_' . strtoupper($row->languageoriginal)));
				?>
			</td>
			<td>
				<?php
					$languageFlag = $row->languagetranslated != 'auto' ? '<img src="' . Uri::root(false) . 'media/mod_languages/images/' . preg_replace('/-.*/i', '', $row->languagetranslated) . '.gif" alt="flag"/> ' : '';
				
					if($row->languagetranslated == 'en' && in_array('usa', $altFlags)) {
						$row->languagetranslated = 'en-us';
						$languageFlag = $row->languagetranslated != 'auto' ? '<img src="' . Uri::root(false) . 'media/mod_languages/images/' . preg_replace('/-/i', '_', $row->languagetranslated) . '.gif" alt="flag"/> ' : '';
					}
					if($row->languagetranslated == 'tl') {
						$languageFlag = '<img style="width:18px;height:12px;margin-right:2px" src="' . Uri::root(false) . 'media/mod_gptranslate/flags/svg/tl.svg" alt="flag"/>';
					}
				
					echo $languageFlag . (Language::getInstance($row->languagetranslated)->loadLanguageTitle($row->languagetranslated) ? : Text::_('COM_GPTRANSLATE_LANGUAGE_NAME_' . strtoupper($row->languagetranslated))); ?>
			</td>
			<td class="d-none d-md-table-cell">
				<?php echo $published;?>
			</td>
			<td class="d-none d-md-table-cell">
				<span class="badge bg-primary">
					<?php echo $row->translation_engine == 'chatgpt' ? Text::_('COM_GPTRANSLATE_CHATGPT_TRANSLATION_ENGINE_CHATGPT') : Text::_('COM_GPTRANSLATE_CHATGPT_TRANSLATION_ENGINE_GTRANSLATE');?>
				</span>
			</td>
			<td class="d-none d-md-table-cell">
				<?php 
					// Check if the intl extension is loaded
					if (!extension_loaded('intl')) {
						$dateObject = Factory::getDate($row->translate_date);
						// Set local time zone
						$dateObject->setTimezone(new \DateTimeZone($this->joomlaConfig));
						// Format date with local date time
						echo ucfirst($dateObject->format(Text::_('DATE_FORMAT_LC2'), true, false));
					} else {
						// Transform the date string, get date time in UTC from DB
						$dateObject = new DateTime($row->translate_date, new DateTimeZone('UTC'));
						
						// Set local time zone
						$dateObject->setTimezone(new \DateTimeZone($this->joomlaConfig));
						
						// Format date with local date time using IntlDateFormatter
						$formatter = new IntlDateFormatter(
							$this->joomlaConfigLanguage, // Locale
							IntlDateFormatter::FULL, // Date format style
							IntlDateFormatter::SHORT, // Time format style
							$this->joomlaConfig, // Timezone
							IntlDateFormatter::GREGORIAN // Calendar type
						);
						
						// Format the date object
						echo ucfirst($formatter->format($dateObject));
					}
				?> 
			</td>
			<td class="d-none d-md-table-cell">
				<?php echo $row->id;?>
			</td>
		</tr>
		<?php
	}
	?>
	<tfoot>
		<td colspan="13">
			<?php echo $this->pagination->getListFooter(); ?>
		</td>
	</tfoot>
	</table>

	<input type="hidden" name="option" value="<?php echo $this->option;?>" />
	<input type="hidden" name="task" value="translations.display" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo @$this->orders['order'];?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo @$this->orders['order_Dir'];?>" />
</form>