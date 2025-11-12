<?php 
/** 
 * @package GPTRANSLATE::CPANEL::administrator::components::com_gptranslate
 * @subpackage views
 * @subpackage cpanel
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2015 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html 
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use JExtstore\Component\Gptranslate\Administrator\Framework\Language\Multilang;
use Joomla\String\StringHelper;
?>
<!-- CPANEL ICONS -->
<div class="row no-margin">
	<div class="card card-default accordion-group col-lg-5 p-0 span5">
		<div class="card-header accordion-toggle accordion_lightblue p-3 noaccordion">
			<h4 class="card-title m-0"><span class="icon-pencil" aria-hidden="true"></span><?php echo Text::_('COM_GPTRANSLATE_CPANEL_TASKS' ); ?></h4>
		</div>
		<div id="placeholder_cpanelicons" class="card-body card-block card-block-whitebg accordion-body">
			<div id="cpanel" class="ps-2 pt-3">
				<?php echo $this->icons; ?>
			
				<div id="updatestatus">
					<?php 
					if(is_object($this->updatesData)) {
						if(version_compare($this->updatesData->latest, $this->currentVersion, '>')) { ?>
							<a href="https://storejextensions.org/extensions/gptranslate.html" target="_blank" alt="storejoomla link">
								<label data-bs-content="<?php echo Text::sprintf('COM_GPTRANSLATE_GET_LATEST', $this->currentVersion, $this->updatesData->latest, $this->updatesData->relevance);?>" class="badge bg-danger hasPopover">
									<span class="icon-warning" aria-hidden="true"></span>
									<?php echo Text::sprintf('COM_GPTRANSLATE_OUTDATED', $this->updatesData->latest);?>
								</label>
							</a>
						<?php } else { ?>
							<label data-bs-content="<?php echo Text::sprintf('COM_GPTRANSLATE_YOUHAVE_LATEST', $this->currentVersion);?>" class="badge bg-success hasPopover">
								<span class="icon-checkmark" aria-hidden="true"></span>
								<?php echo Text::sprintf('COM_GPTRANSLATE_UPTODATE', $this->updatesData->latest);?>
							</label>	
						<?php }
					}
					?>
				</div>
				
				<?php echo $this->moduleStatus; ?>
			</div>
		</div>
	</div>
	
	<div class="col-lg-7 span7 accordion" id="gptranslate_accordion_cpanel">
		<div class="card card-default accordion-group">
	    	<div class="card-header p-0 accordion-heading">
	    		<div class="accordion-toggle" data-bs-toggle="collapse" data-bs-target="#gptranslate_stats">
		      		<h4 class="card-title m-3 accordion-title">
		      			<span class="icon-chart" aria-hidden="true"></span>
		      			<?php echo Text::_('COM_GPTRANSLATE_CPANEL_STATS');?>
	      			</h4>
	      		</div>
	    	</div>
	    	
	    	 <div id="gptranslate_stats" data-bs-parent="#gptranslate_accordion_cpanel" class="card-body card-block card-block-whitebg accordion-body accordion-chart collapse">
				<div class="accordion-inner">
					<div class="single_stat_container">
						<div class="statcircle">
							<span class="icon-checkmark icon-large" aria-hidden="true"></span>
						</div>
						<ul class="subdescription_stats">
							<li class="es-stat-no"><?php echo $this->infodata['chart_links_canvas']['translations_short']; ?></li>
							<li class="es-stat-title"><?php echo Text::_('COM_GPTRANSLATE_TRANSLATIONS_CHART');?></li>
						</ul>
					</div>
					
					<div class="single_stat_container">
						<div class="statcircle">
							<span class="icon-cancel icon-large" aria-hidden="true"></span>
						</div>
						<ul class="subdescription_stats">
							<li class="es-stat-no"><?php echo $this->infodata['chart_links_canvas']['utranslations_short']; ?></li>
							<li class="es-stat-title"><?php echo Text::_('COM_GPTRANSLATE_UTRANSLATIONS_CHART');?></li>
						</ul>
					</div>
					
					<?php
						unset($this->infodata['chart_links_canvas']['start']);
						unset($this->infodata['chart_links_canvas']['translations_short']);
						unset($this->infodata['chart_links_canvas']['utranslations_short']);
						unset($this->infodata['chart_links_canvas']['end']);
						foreach ($this->infodata['chart_links_canvas'] as $language=>$counter) {?>
						<div class="single_stat_container">
							<div class="statcircle">
								<span class="icon-flag icon-large" aria-hidden="true"></span>
							</div>
							<ul class="subdescription_stats">
								<li class="es-stat-no"><?php echo $counter; ?></li>
								<li class="es-stat-title"><?php echo Multilang::loadLanguageTitle($language) ? : StringHelper::ucfirst($language);?></li>
							</ul>
						</div>
					<?php }?>
					
  					<div class="chart_container">
						<canvas id="chart_links_canvas"></canvas>
					</div>
				</div>
			</div>
		</div>
		
		<div class="card card-default accordion-group">
		    <div class="card-header p-0 accordion-heading">
				<div class="accordion-toggle" data-bs-toggle="collapse" data-bs-target="#gptranslate_status">
					<h4 class="card-title m-3 accordion-title">
						<span class="icon-help" aria-hidden="true"></span>
						<?php echo Text::_('COM_GPTRANSLATE_ABOUT');?>
					</h4>
		      	</div>
	    	</div>
		    <div id="gptranslate_status" data-bs-parent="#gptranslate_accordion_cpanel" class="card-body card-block card-block-whitebg accordion-body collapse">
		 		<div class="accordion-inner">
					<div class="single_container">
				 		<label class="badge bg-warning"><?php echo Text::_('COM_GPTRANSLATE_CURRENT_VERSION') . $this->currentVersion;?></label>
			 		</div>
			 		
			 		<div class="single_container">
				 		<label class="badge bg-primary"><?php echo Text::_('COM_GPTRANSLATE_AUTHOR_COMPONENT');?></label>
			 		</div>
			 		
			 		<div class="single_container">
				 		<label class="badge bg-primary"><?php echo Text::_('COM_GPTRANSLATE_SUPPORTLINK');?></label>
			 		</div>
			 		
			 		<div class="single_container">
				 		<label class="badge bg-primary"><?php echo Text::_('COM_GPTRANSLATE_DEMOLINK');?></label>
			 		</div>
				</div>
		    </div>
	 	</div>
	</div>
</div>
<form name="adminForm" id="adminForm" action="index.php">
	<input type="hidden" name="option" value="<?php echo $this->option;?>"/>
	<input type="hidden" name="task" value=""/>
</form>