<?php
/**
* @package com_splms
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2024 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/


// Sem Acesso Direto
defined ('_JEXEC') or die('Acesso Restrito');

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
		// Atribuir dados à view
		$this->item 	= $this->get('Item');
		$app 			= Factory::getApplication();
		$this->params 	= $app->getParams();
		$menus 			= Factory::getApplication()->getMenu();
		$menu 			= $menus->getActive();

		// Importar helper do componente Joomla
		// Obter parâmetros do componente
		$this->lmsParams = ComponentHelper::getParams('com_splms');
		// Carregar model de Lições
		BaseDatabaseModel::addIncludePath(JPATH_SITE.'/components/com_splms/models');

		// Verificar erros.
		if (count($errors = $this->get('Errors'))) {
			throw new \Exception(implode("\n", $errors), 500);
			return false;
		}

		// Carregar Models de cursos e lições
		$quiz_model  	= BaseDatabaseModel::getInstance( 'Quizquestions', 'SplmsModel' );
		$courses_model 	= BaseDatabaseModel::getInstance( 'Courses', 'SplmsModel' );
		
		// Obter ID do usuário logado
		$user = Factory::getUser();
		$userId = $user->id;
		// Visitante não tem acesso
		if($user->guest) {
			echo '<p class="alert alert-danger">' . Text::_('COM_SPLMS_QUIZ_LOGIN') . '</p>';
			return;	
		}

		$this->isAuthorised = $courses_model->getIsbuycourse($userId, $this->item->course_id);
		$this->courese  	= $courses_model->getCourse($this->item->course_id);
		// Verificar autorização ou quiz gratuito
		if(!$this->isAuthorised && $this->item->quiz_type > 0) {
			$output  = '<div class="alert alert-warning">';
			$output .= '<p>' . Text::_('COM_SPLMS_QUIZ_NOT_PREMITTED') .'</p>';
			$output .= '<a href="' . $this->courese->url . '">' . $this->courese->title .'</a>';
			$output .= '</div>';
			echo $output;

			return;	
		}

		// Se o quiz já foi realizado
		$db = Factory::getDbo();
		
		// GUIDEWAY CUSTOM: Obter o número de tentativas e o limite
		$max_attempts = isset($this->item->max_attempts) ? (int)$this->item->max_attempts : 1; 
		$attempts_count = $quiz_model->getQuizAttempts($user->id, $this->item->id);
		
		// Bloqueia se o aluno já atingiu o limite (max_attempts > 0 e tentativas >= max_attempts)
		if ($max_attempts > 0 && $attempts_count >= $max_attempts) {
			// Bloqueado: pegar o melhor resultado para exibição
			$this->best_result = $quiz_model->getBestQuizResult($user->id, $this->item->id);
			
			if(!empty($this->best_result)) {
				$qrPoint = (int)$this->best_result->point;
				$qrTotal = (int)$this->best_result->total_marks;
				$qrPercent = ($qrTotal > 0) ? round(($qrPoint / $qrTotal) * 100) : 0;
				
				// Obter nota de corte da lição, se disponível
				$lessonId = $app->input->getInt('lesson_id', 0);
				$qrPassingScore = 0;
				if ($lessonId > 0) {
					$psQuery = $db->getQuery(true)
						->select('passing_score')
						->from('#__splms_lessons')
						->where('id = ' . (int)$lessonId);
					$db->setQuery($psQuery);
					$qrPassingScore = (int)$db->loadResult();
				}
				// Fallback: buscar pelo quiz_id
				if ($qrPassingScore <= 0 && !empty($this->item->id)) {
					$psQuery2 = $db->getQuery(true)
						->select('passing_score')
						->from('#__splms_lessons')
						->where('quiz_id = ' . (int)$this->item->id)
						->where('published = 1');
					$db->setQuery($psQuery2, 0, 1);
					$qrPassingScore = (int)$db->loadResult();
				}
				// Default: 70%
				if ($qrPassingScore <= 0) {
					$qrPassingScore = 70;
				}
				$qrPassed = ($qrPercent >= $qrPassingScore);
				$qrColor = $qrPassed ? '#22c55e' : '#ef4444';
				$qrIcon = $qrPassed ? 'fa-check-circle' : 'fa-times-circle';
				$qrTitle = $qrPassed ? 'Quiz Concluído!' : 'Desempenho Final';
				$qrMessage = $qrPassed ? 'Parabéns! Você alcançou a nota necessária.' : 'Você não atingiu a nota mínima de ' . $qrPassingScore . '%.';
				
				// URL de retorno ao curso
				$courseUrl = !empty($this->courese->url) ? $this->courese->url : Uri::root();
				
				$doc = Factory::getDocument();
				$doc->addStyleDeclaration('
					.quiz-completed-card {
						max-width: 600px; margin: 60px auto; text-align: center; padding: 50px 40px;
						background: var(--card-bg, #fff); border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.08);
						border: 1px solid var(--border-color, #f0f0f0);
						color: var(--body-text, #333333);
					}
					.quiz-completed-icon { font-size: 56px; display: block; margin-bottom: 15px; }
					.quiz-completed-title { font-size: 24px; font-weight: 700; margin-bottom: 10px; }
					.quiz-completed-score { font-size: 4rem; font-weight: 800; margin: 20px 0; line-height: 1; }
					.quiz-completed-detail { color: var(--body-text, #64748b); font-size: 16px; margin-bottom: 5px; opacity: 0.8; }
					.quiz-completed-message { color: var(--body-text, #94a3b8); font-size: 14px; margin-top: 15px; opacity: 0.7; }
					.quiz-limit-warning { background: rgba(234, 179, 8, 0.1); color: #eab308; padding: 10px; border-radius: 8px; font-size: 14px; display: inline-block; margin-top: 15px; font-weight: 500; border: 1px solid rgba(234, 179, 8, 0.2); }
					.quiz-completed-actions { margin-top: 30px; display: flex; gap: 15px; justify-content: center; flex-wrap: wrap; }
					.quiz-btn-back { padding: 14px 35px; border-radius: 10px; font-weight: 700; font-size: 16px; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: all 0.2s; }
					.quiz-btn-back:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.1); text-decoration: none; color: #fff; }
					.quiz-btn-primary { background: #3b82f6; color: #fff; border: none; }
					.quiz-btn-primary:hover { background: #2563eb; color: #fff; }
					.quiz-score-bar-track { width: 80%; max-width: 300px; height: 10px; background: var(--border-color, #e2e8f0); border-radius: 5px; margin: 10px auto; overflow: hidden; }
					.quiz-score-bar-fill { height: 100%; border-radius: 5px; transition: width 0.5s ease; }
				');
				
				echo '<div id="splms" class="splms view-splms-quiz">';
				echo '<div class="quiz-completed-card">';
				echo '  <i class="fa ' . $qrIcon . ' quiz-completed-icon" style="color: ' . $qrColor . ' !important;"></i>';
				echo '  <h3 class="quiz-completed-title" style="color: ' . $qrColor . ' !important;">' . $qrTitle . '</h3>';
				echo '  <div class="quiz-completed-score" style="color: ' . $qrColor . ' !important;">' . $qrPercent . '%</div>';
				echo '  <div class="quiz-score-bar-track"><div class="quiz-score-bar-fill" style="width: ' . $qrPercent . '%; background: ' . $qrColor . ' !important;"></div></div>';
				echo '  <p class="quiz-completed-detail">Maior pontuação: <strong>' . $qrPoint . '</strong> de <strong>' . $qrTotal . '</strong> (' . $attempts_count . ' tentativas)</p>';
				if ($qrPassingScore > 0) {
					echo '  <p class="quiz-completed-detail" style="font-size: 14px;">Nota mínima: <strong style="color: #f59e0b;">' . $qrPassingScore . '%</strong></p>';
				}
				echo '  <p class="quiz-completed-message">' . $qrMessage . '</p>';
				echo '  <div class="quiz-limit-warning">Você atingiu o limite de tentativas deste quiz.</div>';
				echo '  <div class="quiz-completed-actions">';
				echo '    <a href="' . $courseUrl . '" class="quiz-btn-back quiz-btn-primary"><i class="fa fa-arrow-left"></i> Voltar ao Curso</a>';
				echo '  </div>';
				echo '</div>';
				echo '</div>';
				return;	
			}
		}

		// Se o aluno ainda tem tentativas, mostrar o quiz normalmente.
		// Vamos também passar a informação de tentativas restantes para o card e JS
		$attempts_left = ($max_attempts > 0) ? ($max_attempts - $attempts_count) : -1;
		$this->attempts_info = array(
			'count' => $attempts_count,
			'max' => $max_attempts,
			'left' => $attempts_left
		);

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
		<?php
		// GUIDEWAY CUSTOM: Obter Nota de Corte para Lógica JS
		$lessonId = $app->input->getInt('lesson_id', 0);
		$jsPassingScore = 0;
		$db = Factory::getDbo();
		
		// 1. Buscar pelo lesson_id
		if ($lessonId > 0) {
			$psQuery = $db->getQuery(true)
				->select('passing_score')
				->from('#__splms_lessons')
				->where('id = ' . (int)$lessonId);
			$db->setQuery($psQuery);
			$jsPassingScore = (int)$db->loadResult();
		}
		
		// 2. Fallback: buscar pelo quiz_id
		if ($jsPassingScore <= 0 && !empty($this->item->id)) {
			$psQuery2 = $db->getQuery(true)
				->select('passing_score')
				->from('#__splms_lessons')
				->where('quiz_id = ' . (int)$this->item->id)
				->where('published = 1');
			$db->setQuery($psQuery2, 0, 1);
			$jsPassingScore = (int)$db->loadResult();
		}
		
		// 3. Padrão: 70% se nada configurado
		if ($jsPassingScore <= 0) {
			$jsPassingScore = 70;
		}
		?>

		
		<?php
		// GUIDEWAY CUSTOM: CSS para Card de Resultado JS
		$doc = Factory::getDocument();
		$doc->addStyleDeclaration('
			.quiz-result-card {
				text-align: center; padding: 40px; background: var(--card-bg, #fff); border-radius: 20px;
				box-shadow: 0 10px 40px rgba(0,0,0,0.08); border: 1px solid var(--border-color, #f0f0f0);
				max-width: 500px; margin: 40px auto;
				color: var(--body-text, #333333);
			}
			.circular-progress {
				position: relative; height: 160px; width: 160px; border-radius: 50%;
				display: grid; place-items: center; margin: 0 auto 25px auto;
			}
			.circular-progress:before {
				content: ""; position: absolute; height: 84%; width: 84%;
				background-color: var(--card-bg, #ffffff); border-radius: 50%;
			}
			.inner-circle { position: relative; font-family: sans-serif; }
			.score-percent { font-size: 3rem; font-weight: 800; display: block; line-height: 1; margin-bottom: 5px; }
			.score-fraction { font-size: 0.9rem; color: var(--body-text, #64748b); opacity: 0.8; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
			.result-submessage { color: var(--body-text, #64748b); opacity: 0.8; margin-bottom: 25px; font-size: 15px; }
			.btn-restart { padding: 12px 30px; border-radius: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; font-size: 14px; box-shadow: 0 4px 6px rgba(59, 130, 246, 0.2); transition: transform 0.2s; color: #fff;}
			.btn-restart:hover { transform: translateY(-2px); box-shadow: 0 6px 8px rgba(59, 130, 246, 0.3); }
		');
		?>

		<script type="text/javascript">

			jQuery(function($) {

			// IIFE para encapsular variáveis e evitar poluição do escopo global
			(function() {
				"use strict";
			
			// GUIDEWAY CUSTOM: Nota de Corte vinda do PHP
			var passingScore = <?php echo $jsPassingScore; ?>;
			var maxAttempts = <?php echo $this->attempts_info['max']; ?>;
			var currentAttempts = <?php echo $this->attempts_info['count']; ?>;
			var attemptsLeft = <?php echo $this->attempts_info['left']; ?>;

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

			// Variáveis encapsuladas no escopo da IIFE
			var currentQuestion = 0;
			var correctAnswers = 0;
			var quizOver = false;

			$(document).ready(function () {

			    // Exibir a primeira pergunta
			    displayCurrentQuestion();
			    $(this).find(".quizMessage").hide();

			    // Ao clicar em próximo, exibir a próxima pergunta
			    $(this).find(".nextButton").on("click", function () {
			        if (!quizOver) {

			            var value = $("input[type='radio']:checked").val();

			            if (value == undefined) {
			                $(document).find(".quizMessage").text(Joomla.Text._('COM_SPLMS_QUIZ_SELECT_ANSWER'));
			                $(document).find(".quizMessage").show();
			            } else {
                // Ocultar mensagem de erro se existir
                $(document).find(".quizMessage").hide();

			                if (value == questions[currentQuestion].correctAnswer) {
			                    correctAnswers++;
			                }

			                currentQuestion++; // Já exibimos a primeira pergunta no DOM ready
			                if (currentQuestion < questions.length) {
			                    displayCurrentQuestion();
			                } else {
			                	insertScore();
			                    // Alterar o botão para perguntar se o usuário quer jogar novamente
			                    $('.countdown-wrapper').hide();
			                    $(".quizContainer .nextButton").hide();
			                    //$(".quizContainer .nextButton").text("Start Again?");
			                    $(".quizContainer .nextButton").addClass("playagain");

							    $(".nextButton").on( "click", function() {	
									location.reload(true);
							});

								quizOver = true;
			                }
			            }
			        } else {
            quizOver = false;
            resetQuiz();
            hideScore();
        }
			    });

			});

			// Exibe a pergunta atual e as escolhas
			function displayCurrentQuestion() {
			    var question = questions[currentQuestion].question;
			    var questionClass = $(document).find(".quizContainer .ques-ans-wrapper > .question");
			    var choiceList = $(document).find(".quizContainer .ques-ans-wrapper > .choiceList");
			    var numChoices = questions[currentQuestion].choices.length;

			    $(document).find(".lms-result-wrapper > .result").removeClass('active');
			    $(document).find(".quizContainer #countdown").show();
			    $(document).find(".nextButton").removeClass("playagain");
			    $(document).find(".quizContainer .ques-ans-wrapper").show();
				$('.countdown-wrapper').show();

			    // Definir o texto da pergunta atual
			    $(questionClass).text(question);

			    // Remover todos os elementos <li> atuais (se houver)
			    $(choiceList).find("li").remove();

			    var choice;
			    for (var i = 0; i < numChoices; i++) {
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
				$(document).find(".before-start-quiz").hide();
			    
			    var percentage = Math.round((correctAnswers / questions.length) * 100);
			    
			    // GUIDEWAY CUSTOM: Lógica de Aprovação/Reprovação
			    var passed = (percentage >= passingScore);
			    
			    var color = passed ? '#22c55e' : '#ef4444'; 
			    var secondaryColor = passed ? 'rgba(226,232,240,0.3)' : 'rgba(254,226,226,0.3)';
			    
			    var message = passed ? "Excelente!" : "Nota Insuficiente";
			    var subMessage = passed 
			        ? "Você domina este assunto." 
			        : "Você não atingiu a nota mínima de " + passingScore + "%.";
			    
			    var btnText = passed ? "Voltar ao Curso" : "Tentar Novamente";
			    var btnAction = passed ? 'href="<?php echo $this->courese->url; ?>"' : 'href="javascript:location.reload(true)"';
			    
				// Se atingiu o limite agora, force a volta ao curso
				if (!passed && attemptsLeft === 0) {
					btnText = "Voltar ao Curso";
					btnAction = 'href="<?php echo $this->courese->url; ?>"';
					subMessage = "Você não atingiu a nota mínima de " + passingScore + "%. Você esgotou suas tentativas.";
				}

			    var html = '<div class="quiz-result-card" role="alert" aria-live="polite">' +
			               '<div class="circular-progress" style="background: conic-gradient(' + color + ' ' + percentage + '%, ' + secondaryColor + ' ' + percentage + '%);">' +
			                   '<div class="inner-circle">' +
			                       '<span class="score-percent" style="color: ' + color + ' !important;">' + percentage + '%</span>' +
			                       '<span class="score-fraction">Acertos: ' + correctAnswers + ' de ' + questions.length + '</span>' +
			                   '</div>' +
			               '</div>' +
			               '<h3 style="color: ' + color + ' !important;">' + message + '</h3>' +
			               '<p class="result-submessage">' + subMessage + '</p>' +
			               '<a class="btn btn-primary btn-lg btn-restart" ' + btnAction + '>' + btnText + '</a>' +
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

					    	insertScore();
					    	
					    	quizOver = true;
						};
					}
				});

			}) // FIM:: onclick iniciar contagem regressiva


			// Ajax inserir dados do formulário
			function insertScore() {
				jQuery(function($) {

				        var request = {
				            'option' : 'com_splms',
				            'task' : 'quizquestions.submit_result',
				            'format' : 'raw',
				            'data'   : {
				            	user_id: <?php echo $userId; ?>,
				            	quiz_id: <?php echo $this->item->id; ?>,
				            	course_id: <?php echo $this->item->course_id; ?>,
                                lesson_id: <?php echo $app->input->getInt('lesson_id', 0); ?>,
				            	total_marks: questions.length,
				            	q_result: correctAnswers,
				            }
				        };

				        $.ajax({
				            type   : 'POST',
				            url    : '<?php echo Uri::base(); ?>index.php',
				            data   : request,
				            dataType: 'json',
				            success: function (result) {
				            	if(result && result.success !== false){
									displayScore();
				            	} else {
				            		displayScore();
				            	}
				            },
				            error: function(xhr, status, error) {
				            	console.error('Erro ao salvar resultado:', error, xhr.responseText);
				            	displayScore();
				            }
				        });

				        return false;
				       
				    //});
				});
			}

			})(); // Fim da IIFE

			});

			</script>
		<?php 

		// Gerar Metadados do Item
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