<?php
/**
* @package com_splms
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2024 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/


// No Direct Access
defined ('_JEXEC') or die('Resticted Aceess');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Uri\Uri;
class SplmsViewQuizquestion extends HtmlView{

	protected $item;
	protected $params;

	function display($tpl = null) {
		// Assign data to the view
		$this->item 	= $this->get('Item');
		$app 			= Factory::getApplication();
		$this->params 	= $app->getParams();
		$menus 			= Factory::getApplication()->getMenu();
		$menu 			= $menus->getActive();

		// Import Joomla component helper
		//get Component Params
		$this->lmsParams = ComponentHelper::getParams('com_splms');
		// Load Lessons model
		BaseDatabaseModel::addIncludePath(JPATH_SITE.'/components/com_splms/models');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			throw new \Exception(implode("\n", $errors), 500);
			return false;
		}

		// Load courses & lesson Model
		$quiz_model  	= BaseDatabaseModel::getInstance( 'Quizquestions', 'SplmsModel' );
		$courses_model 	= BaseDatabaseModel::getInstance( 'Courses', 'SplmsModel' );
		
		// Get login user ID
		$user = Factory::getUser();
		$userId = $user->id;
		// guest doensn't access
		if($user->guest) {
			echo '<p class="alert alert-danger">' . Text::_('COM_SPLMS_QUIZ_LOGIN') . '</p>';
			return;	
		}

		$this->isAuthorised = $courses_model->getIsbuycourse($userId, $this->item->course_id);
		$this->courese  	= $courses_model->getCourse($this->item->course_id);
		// check authorised or free quiz
		if(!$this->isAuthorised && $this->item->quiz_type > 0) {
			$output  = '<div class="alert alert-warning">';
			$output .= '<p>' . Text::_('COM_SPLMS_QUIZ_NOT_PREMITTED') .'</p>';
			$output .= '<a href="' . $this->courese->url . '">' . $this->courese->title .'</a>';
			$output .= '</div>';
			echo $output;

			return;	
		}

		//if already given quiz
		$this->quiz_results = $quiz_model->getQuizById( $user->id, $this->item->id );
		if(!empty($this->quiz_results)) {
			echo '<p class="alert alert-danger">' . Text::_('COM_SPLMS_ALREADY_GIVEN_QUIZ') . '</p>';
			return;	
		}

		$list_answers = array();
		if(!empty($this->item->list_answers))
		{
			
			foreach($this->item->list_answers as $list_answer)
			{
				$list_answers[] = array(
					'qes_title' 	=> $list_answer['qes_title'],
					'ans_one' 		=> $list_answer['ans_one'],
					'ans_two' 		=> $list_answer['ans_two'],
					'ans_three' 	=> $list_answer['ans_three'],
					'ans_four' 		=> $list_answer['ans_four'],
					'right_ans' 	=> $list_answer['right_ans'],
				);
			}
		}

		?>
		
		<!-- Quiz Questions -->
		<script type="text/javascript">

			jQuery(function($) {

			$(".startQuiz").click(function(){
				$(document).find(".quizContainer").show();
				$(document).find(".before-start-quiz").hide();
			});

			var questions = [
			<?php foreach ($list_answers as $list_answer) {
				$qus_ans = '"' . trim($list_answer['ans_one']) . '", ' . '"' . trim($list_answer['ans_two']) . '", ' . '"' . trim($list_answer['ans_three']) . '", ' . '"' . trim($list_answer['ans_four']) . '", ';
			?>
			{
			    question: "<?php echo $list_answer['qes_title']; ?>",
			    choices: [<?php echo $qus_ans; ?>],
			    correctAnswer: <?php echo $list_answer['right_ans']; ?>
			},

			<?php } ?>

			];

			var currentQuestion = 0;
			var correctAnswers = 0;
			var quizOver = false;

			$(document).ready(function () {

			    // Display the first question
			    displayCurrentQuestion();
			    $(this).find(".quizMessage").hide();

			    // On clicking next, display the next question
			    $(this).find(".nextButton").on("click", function () {
			        if (!quizOver) {

			            value = $("input[type='radio']:checked").val();

			            if (value == undefined) {
			                $(document).find(".quizMessage").text(Joomla.Text._('COM_SPLMS_QUIZ_SELECT_ANSWER'));
			                $(document).find(".quizMessage").show();
			            } else {
			                // TODO: Remove any message -> not sure if this is efficient to call this each time....
			                $(document).find(".quizMessage").hide();

			                if (value == questions[currentQuestion].correctAnswer) {
			                    correctAnswers++;
			                }

			                currentQuestion++; // Since we have already displayed the first question on DOM ready
			                if (currentQuestion < questions.length) {
			                    displayCurrentQuestion();
			                } else {
			                	insertScore();
			                    // Change the text in the next button to ask if user wants to play again
			                    $('.countdown-wrapper').hide();
			                    $(".quizContainer .nextButton").hide();
			                    //$(".quizContainer .nextButton").text("Start Again?");
			                    $(".quizContainer .nextButton").addClass("playagain");

							    $(".nextButton").on( "click", function() {	
									location.reload(true);
									//console.log('clicked');
								});

								quizOver = true;
			                }
			            }
			        } else { // quiz is over and clicked the next button (which now displays 'Play Again?'
			            quizOver = false;
			            //$(document).find(".nextButton").text("Next Question > ");
			            resetQuiz();
			            //displayCurrentQuestion();
			            hideScore();
			        }
			    });

			});

			// This displays the current question AND the choices
			function displayCurrentQuestion() {

			    //console.log("In display current Question");
			    var question = questions[currentQuestion].question;
			    var questionClass = $(document).find(".quizContainer .ques-ans-wrapper > .question");
			    var choiceList = $(document).find(".quizContainer .ques-ans-wrapper > .choiceList");
			    var numChoices = questions[currentQuestion].choices.length;

			    $(document).find(".lms-result-wrapper > .result").removeClass('active');
			    $(document).find(".quizContainer #countdown").show();
			    $(document).find(".nextButton").removeClass("playagain");
			    $(document).find(".quizContainer .ques-ans-wrapper").show();
				$(document).find(".quizContainer .ques-ans-wrapper").show();
				$('.countdown-wrapper').show();

			    // Set the questionClass text to the current question
			    $(questionClass).text(question);

			    // Remove all current <li> elements (if any)
			    $(choiceList).find("li").remove();

			    var choice;
			    for (i = 0; i < numChoices; i++) {
			        choice = questions[currentQuestion].choices[i];
					var uniqueId = 'ans_' + currentQuestion + '_' + i;
			        $('<li><div class="radio"><input type="radio" id="' + uniqueId + '" value=' + i + ' name="dynradio" /><label for="' + uniqueId + '">' + choice + '</label></div></li>').appendTo(choiceList);
			    }
			}

			function resetQuiz() {
			    currentQuestion = 0;
			    correctAnswers = 0;
			    hideScore();
			}

			function displayScore() {
				$(document).find(".quizContainer").hide();
				$(document).find(".before-start-quiz").hide(); // Garantir que a tela inicial sumiu
			    
			    var percentage = Math.round((correctAnswers / questions.length) * 100);
			    var message = percentage >= 70 ? "Excelente!" : "Bom esforço!";
			    var subMessage = percentage >= 70 ? "Você domina este assunto." : "Continue estudando para melhorar.";
			    var icon = percentage >= 70 ? "fa-star" : "fa-refresh";
			    var iconColor = percentage >= 70 ? "#fab005" : "#3b82f6";
			    
			    var html = '<div class="quiz-result-card">' +
			               '<div class="circular-progress" style="background: conic-gradient(#3b82f6 ' + percentage + '%, #e2e8f0 ' + percentage + '%);">' +
			                   '<div class="inner-circle">' +
			                       '<span class="score-percent">' + percentage + '%</span>' +
			                       '<span class="score-fraction">Acertos: ' + correctAnswers + ' de ' + questions.length + '</span>' +
			                   '</div>' +
			               '</div>' +
			               '<h3>' + message + '</h3>' +
			               '<p class="result-submessage">' + subMessage + '</p>' +
			               '<a class="btn btn-primary btn-lg btn-restart" href="<?php echo $this->courese->url; ?>">Voltar ao Curso</a>' +
			               '</div>';

			    $(document).find(".lms-result-wrapper .result").html(html);
			    $(document).find(".lms-result-wrapper .result").show().addClass('active');
			    $("#countdown").stop(true);
			}

			function displayError() {
				$(document).find(".quizContainer .ques-ans-wrapper").hide();
			    $(document).find(".lms-result-wrapper > .result").text(Joomla.Text._('COM_SPLMS_QUIZ_ERROR'));
			    $(document).find(".lms-result-wrapper > .result").show().addClass('active');
			    $("#countdown").stop(true);
			}

			function hideScore() {
			    $(document).find(".lms-result-wrapper > .result").hide();
			}
			// Contagem Regressiva
			$(".startQuiz").click(function(){ 
				// Contagem regressiva
				jQuery("#countdown").countDown({
					startNumber: <?php echo $this->item->duration; ?>,
					callBack: function(me) {
						//displayScore();
						if (!$(".lms-result-wrapper .result").hasClass("active")) {
							$(".lms-result-wrapper > .result").text(Joomla.Text._('COM_SPLMS_QUIZ_TIME_UP') + correctAnswers + Joomla.Text._('COM_SPLMS_QUIZ_SCORE_OUT_OF') + questions.length);
							$(".quizContainer #countdown").hide();
							$(".quizContainer .countdown-wrapper").hide();
							$(".lms-result-wrapper > .result").show().addClass('active');
					    	$(".quizContainer .ques-ans-wrapper").hide();
					    	//$(".quizContainer .nextButton").text("Start Again?");
					    	$(".quizContainer .nextButton").hide();

					    	//resetQuiz();
					    	insertScore();
					    	
					    	quizOver = true;
					  //   	$(".nextButton").on( "click", function() {
							// 	location.reload(true);
							// 	//console.log('clicked');
							// });
						};
						//jQuery(me).text("All done! This is where you give the reward!").css("color", "#090");
					}
				});

			}) // END:: onclick start countdown


			//Ajax inserir dados do formulário
			function insertScore() {
				jQuery(function($) {

				    //$('.sppb-ajaxt-contact-form').on('submit', function(event) {
				        //event.preventDefault();

				        // var $self   = $(this);
				        // var value   = $(this).serializeArray();
				        var request = {
				            'option' : 'com_splms',
				            'controller' : 'quizquestions',
				            'task' : 'quizquestions.submit_result',
				            'data'   : {
				            	user_id: <?php echo $userId; ?>,
				            	quiz_id: <?php echo $this->item->id; ?>,
				            	course_id: <?php echo $this->item->course_id; ?>,
				            	total_marks: questions.length,
				            	q_result: correctAnswers,
				            }
				        };

				        $.ajax({
				            type   : 'POST',
				            data   : request,
				            success: function (response) {
				            	var result = $.parseJSON(response);

				            	if(result){
									displayScore();
				            	} else {
				            		displayError();
				            	}
				            	
				            }
				        });

				        return false;
				       
				    //});
				});
			}

			});

			</script>
		<?php 

		//Generate Item Meta
        $itemMeta               = array();
        $itemMeta['title']      = $this->item->title;
        $cleanText              = $this->item->description;
        $itemMeta['metadesc']   = HTMLHelper::_('string.truncate', OutputFilter::cleanText($cleanText), 155);
        if ($this->item->image) {
        	$itemMeta['image']      = Uri::base() . $this->item->image;
        }
        SplmsHelper::itemMeta($itemMeta);
		parent::display($tpl);

	}


}