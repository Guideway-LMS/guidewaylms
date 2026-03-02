<?php
/**
 * @package com_splms
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2024 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

// No Direct Access
defined ('_JEXEC') or die('Resticted Aceess');

use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

?>

<div id="splms" class="splms view-splms-quiz">

	<div class="splms-row">
		<div class="splms-col-sm-12">

			<div class="before-start-quiz">
				<div class="quiz-content">
					<div class="quiz-icon-wrapper" style="font-size: 4rem; color: #3b82f6; margin-bottom: 20px;">
						<i class="fa fa-file-text-o"></i>
					</div>
					<h3><?php echo $this->item->title; ?></h3>
					<p><?php echo $this->item->description; ?></p>
					<?php if (isset($this->attempts_info) && $this->attempts_info['max'] > 0): ?>
						<p class="alert alert-info">
							<strong>Tentativas:</strong> Você tem <?php echo $this->attempts_info['left']; ?> tentativa(s) restante(s) de um máximo de <?php echo $this->attempts_info['max']; ?>.
						</p>
					<?php endif; ?>
				</div>
				<button class="btn btn-primary startQuiz"><?php echo Text::_('COM_SPMS_START_QUIZ'); ?></button>
				<a href="<?php echo Uri::root(); ?>" class="btn btn-default cancelQuiz">
					<?php echo Text::_('COM_SPMS_CENCEL_QUIZ'); ?>
				</a>
			</div>

			<div class="quizContainer">
				<div class="countdown-wrapper">
					<span id="timer"><i class="fa fa-clock-o"></i></span><span id="countdown"></span> 
				</div>
				<div class="quizMessage"></div>
				<div class="ques-ans-wrapper">
					<h3 class="question"></h3>
					<ul class="choiceList list-unstyled"></ul>
				</div>
				
				<button class="btn btn-primary nextButton"><?php echo Text::_('COM_SPLMS_NEXT_QUIZ')?></button>
				<div class="response"></div>
				<br>
			</div>
		</div>
	</div> <!-- /.splms-row -->

	<div class="lms-result-wrapper">
		<div class="result"></div>
	</div>
	
</div> <!-- /.splms -->
