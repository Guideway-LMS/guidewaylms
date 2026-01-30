<?php

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;
/**
* @package com_splms
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2024 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

// No Direct Access
defined ('_JEXEC') or die('Resticted Aceess');

$doc = Factory::getDocument();
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
if(SplmsHelper::getJoomlaVersion() < 4)
{
  HTMLHelper::_('formbehavior.chosen', 'select', null, array('disable_search_threshold' => 0 ));
}

$doc->addStylesheet(Uri::base(true) . '/components/com_speasyimagegallery/assets/css/font-awesome.min.css');
$doc->addStylesheet(Uri::base(true) . '/components/com_speasyimagegallery/assets/css/style.css');
$doc->addScript(Uri::base(true) . '/components/com_speasyimagegallery/assets/js/validation.js');
$doc->addScript(Uri::base(true) . '/components/com_speasyimagegallery/assets/js/script.js');

// Add AI Toolbar Assets
$doc->addStylesheet(Uri::base(true) . '/components/com_splms/assets/css/tolbar_ai.css?v=' . time());
// Script for "Organize Description" (Refine/Summarize)
$doc->addScript(Uri::base(true) . '/components/com_splms/assets/js/guideway_ai.js');
// Script for "Create Description" (Chat/PDF)
$doc->addScript(Uri::base(true) . '/components/com_splms/assets/js/guideway_ai_chat.js');

$rowClass = SplmsHelper::getJoomlaVersion() < 4 ? 'row-fluid' : 'row';
$colClass = SplmsHelper::getJoomlaVersion() < 4 ? 'span' : 'col-lg-';

// Permission Check Helper
$user = Factory::getUser();
$canGenerate = $user->authorise('ai.generate', 'com_splms');
$canRefine   = $user->authorise('ai.refine',   'com_splms');
?>
<form action="<?php echo Route::_('index.php?option=com_splms&layout=edit&id=' . (int) $this->item->id); ?>"
  method="post" name="adminForm" id="adminForm" class="form-validate">
  <div class="form-horizontal">
    <div class="<?php echo $rowClass;?>">
      <div class="<?php echo $colClass;?>9">
        <?php 
        $fields = $this->form->getFieldset('basic');
        foreach ($fields as $field) :
            // Inject AI Toolbars before Description
            if ($field->fieldname === 'description') :
                
                // 1. "Create Description with AI" (Chat/PDF) - Requires ai.generate
                if ($canGenerate) :
                    $toolbarChatPath = JPATH_COMPONENT_ADMINISTRATOR . '/views/lesson/tmpl/toolbar_ai_chat.php';
                    if (file_exists($toolbarChatPath)) {
                        include $toolbarChatPath;
                    }
                    ?>
                    <!-- Hide the "Generate Quiz" button inside the toolbar since we have one below -->
                    <style>
                        #gw-ai-quiz-settings-btn, #gw-ai-quiz-dropdown { display: none !important; }
                    </style>
                    <?php
                endif;

                // 2. "Organize Description with AI" (Grammar/Summary) - Requires ai.refine
                if ($canRefine) :
                    $toolbarRefinePath = JPATH_COMPONENT_ADMINISTRATOR . '/views/lesson/tmpl/toolbar_ai.php';
                    if (file_exists($toolbarRefinePath)) {
                        include $toolbarRefinePath;
                    }
                endif;

            endif;
            
            if ($field->fieldname === 'quiz_type') :
        ?>
            <!-- AI Generation Button & Start Controls -->
            <div class="control-group ai-controls-container">
                
                <!-- Left: Status Indication (PDF & Description) -->
                <div class="ai-status-section">
                     <div class="ai-status-row">
                        <!-- PDF Status -->
                         <div class="ai-status-item">
                            <span class="ai-font-status">FONTE DE<br>INFORMAÇÕES:</span>
                        </div>
                        <div class="ai-status-item">
                            <span id="ai-pdf-status-icon" class="ai-status-icon no-pdf icon-remove"></span>
                            <span class="ai-status-label">PDF</span>
                        </div>
                        <!-- Description Status -->
                        <div class="ai-status-item">
                            <span id="ai-desc-status-icon" class="ai-status-icon no-pdf icon-remove"></span>
                            <span class="ai-status-label">DESCRIÇÃO</span>
                        </div>
                    </div>
                </div>

                <!-- Center: Configs -->
                <div class="ai-config-section">
                    
                    <!-- Difficulty -->
                    <div class="control-item">
                        <label for="ai-difficulty" class="ai-control-label">
                            <?php echo \Joomla\CMS\Language\Text::_('Dificuldade'); ?>
                        </label>
                        <select id="ai-difficulty" class="input-medium ai-select-rounded gw-mb-0">
                            <option value="facil"><?php echo \Joomla\CMS\Language\Text::_('Fácil'); ?></option>
                            <option value="medio" selected><?php echo \Joomla\CMS\Language\Text::_('Médio'); ?></option>
                            <option value="dificil"><?php echo \Joomla\CMS\Language\Text::_('Difícil'); ?></option>
                        </select>
                    </div>

                    <!-- Question Count -->
                    <div class="control-item">
                         <label for="ai-qcount" class="ai-control-label">
                            <?php echo \Joomla\CMS\Language\Text::_('Quantidade'); ?>
                        </label>
                        <input type="number" id="ai-qcount" class="input-mini ai-input-rounded gw-mb-0" value="5" min="1" max="20" list="ai-qcount-list">
                        <datalist id="ai-qcount-list">
                            <option value="5">
                            <option value="10">
                            <option value="15">
                            <option value="20">
                        </datalist>
                    </div>

                </div>

                <!-- Right: Action -->
                <div class="ai-action-section">
                    <div class="ai-action-wrapper">
                        <span id="ai-loading" class="ai-loading-spinner">
                            <span class="icon-spinner icon-spin" style="font-size: 18px;"></span>
                        </span>
                        
                        <button type="button" class="btn btn-primary btn-large" id="btn-generate-ai">
                            <span class="icon-magic" aria-hidden="true"></span>
                            <?php echo \Joomla\CMS\Language\Text::_('Gerar Quizzes'); ?>
                        </button>
                    </div>
                    <div id="ai-message" class="ai-message"></div>
                </div>
            </div>

            <?php echo $field->renderField(); ?>

            <script>
            document.addEventListener('DOMContentLoaded', function() {
                const btnGenerate = document.getElementById('btn-generate-ai');
                const loading = document.getElementById('ai-loading');
                const messageDiv = document.getElementById('ai-message');
                
                // New Inputs
                const difficultyInput = document.getElementById('ai-difficulty');
                const qCountInput     = document.getElementById('ai-qcount');
                const pdfStatusIcon   = document.getElementById('ai-pdf-status-icon');
                const descStatusIcon  = document.getElementById('ai-desc-status-icon');
                
                // 1. PDF Status Monitoring
                const updatePDFStatus = () => {
                   const pdfInput = document.getElementById('gw-ai-file'); // Input in toolbar
                   
                   // Reset classes
                   pdfStatusIcon.className = 'ai-status-icon';
                   pdfStatusIcon.style.color = ''; // Clear inline styles

                   if (pdfInput && pdfInput.files && pdfInput.files.length > 0) {
                       pdfStatusIcon.classList.add('icon-ok', 'has-pdf');
                   } else {
                       pdfStatusIcon.classList.add('icon-remove', 'no-pdf');
                   }
                };
                
                // 2. Description Status Monitoring
                const updateDescStatus = () => {
                    let text = '';
                    try {
                        if (typeof JoomlaEditor !== 'undefined' && JoomlaEditor.get) {
                            const editor = JoomlaEditor.get('jform_description');
                            if (editor) text = editor.getValue();
                        }
                        else if (Joomla && Joomla.editors && Joomla.editors.instances && Joomla.editors.instances['jform_description']) {
                            text = Joomla.editors.instances['jform_description'].getValue();
                        }
                    } catch (e) { }

                    if (!text) {
                        const textarea = document.getElementById('jform_description');
                        if (textarea) text = textarea.value;
                    }
                    
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = text;
                    const plainText = tempDiv.textContent || tempDiv.innerText || '';

                    // Reset
                    descStatusIcon.className = 'ai-status-icon';
                    descStatusIcon.style.color = ''; // Clear inline styles

                    if (plainText.trim().length > 0) {
                        descStatusIcon.classList.add('icon-ok', 'has-pdf');
                    } else {
                        descStatusIcon.classList.add('icon-remove', 'no-pdf');
                    }
                };

                // Listeners
                // Toolbar PDF
                document.body.addEventListener('change', function(e) {
                    if (e.target && e.target.id === 'gw-ai-file') {
                        updatePDFStatus();
                    }
                });

                // Description Input
                const textarea = document.getElementById('jform_description');
                if (textarea) {
                    textarea.addEventListener('input', updateDescStatus);
                    textarea.addEventListener('change', updateDescStatus);
                    textarea.addEventListener('blur', updateDescStatus);
                }
                
                // Poll for Editor changes
                setInterval(updateDescStatus, 2000);

                // Initial check
                updatePDFStatus();
                setTimeout(updateDescStatus, 1500);

                // Attach listener to the toolbar file input (polling or event if possible)
                // Since the toolbar might be loaded dynamically or in a different scope, we'll try to find it.
                // MutationObserver or simple event delegation on document body for 'change'
                document.body.addEventListener('change', function(e) {
                    if (e.target && e.target.id === 'gw-ai-file') {
                        updatePDFStatus();
                    }
                });
                
                // Initial check
                updatePDFStatus();

                if (!btnGenerate) return;

                btnGenerate.addEventListener('click', function() {
                    let text = '';
                    
                    // Tratamento robusto para obter conteúdo do editor (J3 e J4/5)
                    try {
                        if (typeof JoomlaEditor !== 'undefined' && JoomlaEditor.get) {
                            const editor = JoomlaEditor.get('jform_description');
                            if (editor) text = editor.getValue();
                        } 
                        else if (Joomla && Joomla.editors && Joomla.editors.instances && Joomla.editors.instances['jform_description']) {
                            text = Joomla.editors.instances['jform_description'].getValue();
                        }
                    } catch (e) {
                        console.warn('Erro ao acessar API do editor:', e);
                    }

                    if (!text) {
                        const textarea = document.getElementById('jform_description');
                        if (textarea) text = textarea.value;
                    }

                    // Limpeza básica
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = text;
                    const plainText = tempDiv.textContent || tempDiv.innerText || '';

                    // Check PDF again
                    const pdfInput = document.getElementById('gw-ai-file');
                    const hasFile = pdfInput && pdfInput.files.length > 0;

                    if (!plainText.trim() && !hasFile) {
                        alert('Por favor, preencha o campo Descrição OU anexe um PDF na barra de IA para gerar perguntas.');
                        return;
                    }

                    // UI Loading
                    btnGenerate.disabled = true;
                    loading.style.display = 'inline-block';
                    messageDiv.innerHTML = '';

                    // Requisição
                    const data = new FormData();
                    data.append('option', 'com_splms');
                    data.append('task', 'quizquestion.generateAI');
                    data.append('text', plainText);
                    
                    // Add Config Params
                    data.append('difficulty', difficultyInput.value);
                    data.append('count', qCountInput.value);

                    if (hasFile) {
                        data.append('gw_ai_file', pdfInput.files[0]);
                    }
                    data.append('<?php echo \Joomla\CMS\Session\Session::getFormToken(); ?>', '1');

                    fetch('index.php', {
                        method: 'POST',
                        body: data
                    })
                    .then(response => response.json())
                    .then(res => {
                        if (res.success) {
                            populateQuestions(res.data);
                            messageDiv.innerHTML = '<span class="text-success">Questões geradas com sucesso!</span>';
                        } else {
                            messageDiv.innerHTML = '<span class="text-error">Erro: ' + (res.message || 'Desconhecido') + '</span>';
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        messageDiv.innerHTML = '<span class="text-error">Erro na requisição.</span>';
                    })
                    .finally(() => {
                        btnGenerate.disabled = false;
                        loading.style.display = 'none';
                    });
                });

                function populateQuestions(questions) {
                    const potentialButtons = document.querySelectorAll(
                        '.subform-repeatable-add, .group-add, button.btn-success.subform-add, button[data-action="add"]'
                    );
                    
                    let addBtn = null;
                    if (potentialButtons.length > 0) {
                        if (potentialButtons.length === 1) {
                            addBtn = potentialButtons[0];
                        } else {
                            const container = document.getElementById('jform_list_answers');
                            if (container) {
                                addBtn = container.querySelector('.subform-repeatable-add, .group-add');
                            }
                            if (!addBtn) addBtn = potentialButtons[potentialButtons.length -1];
                        }
                    }

                    if (!addBtn) {
                         alert('Erro CRÍTICO: Botão de adicionar ("+") não encontrado no documento.');
                         return;
                    }
                    
                    const wrapper = addBtn.closest('table') || addBtn.closest('.subform-repeatable-wrapper') || addBtn.parentElement.parentElement;
                    
                    let index = 0;

                    function processNext() {
                        if (index >= questions.length) return;

                        const q = questions[index];
                        addBtn.click();
                        
                        setTimeout(() => {
                             let groups = [];
                             if (wrapper) {
                                 groups = wrapper.querySelectorAll('.subform-repeatable-group, tr.subform-repeatable-group, .subform-repeatable-item');
                             } else {
                                 groups = document.querySelectorAll('.subform-repeatable-group, tr.subform-repeatable-group');
                             }
                             
                             const lastGroup = groups[groups.length - 1]; 
                             
                             if (lastGroup) {
                                 const setVal = (fieldNamePart, value) => {
                                     const input = lastGroup.querySelector(`[name*="[${fieldNamePart}]"]`);
                                     if (input) {
                                         input.value = value;
                                         input.dispatchEvent(new Event('change')); 
                                     }
                                 };

                                 setVal('qes_title', q.question);
                                 setVal('ans_one', q.options[0] || '');
                                 setVal('ans_two', q.options[1] || '');
                                 setVal('ans_three', q.options[2] || '');
                                 setVal('ans_four', q.options[3] || '');
                                 setVal('right_ans', q.correct_answer); 
                             }

                             index++;
                             processNext();
                        }, 500); 
                    }

                    processNext();
                }
            });
            </script>
        <?php
            else :
                echo $field->renderField();
            endif;
        endforeach; 
        ?>
      </div>

      <div class="<?php echo $colClass;?>3">
        <fieldset class="form-vertical">
          <div class="control-group">
            <?php if (Factory::getUser()->authorise('ai.generate', 'com_splms')) : ?>
            <button type="button" class="btn btn-success btn-large btn-block" onclick="openAiQuizModal()">
              ✨ <?php echo JText::_('Generate with AI'); ?>
            </button>
            <?php endif; ?>
          </div>
          <?php echo $this->form->renderFieldset('sidebar'); ?>
        </fieldset>
      </div>
    </div>

  </div>

  <input type="hidden" name="task" value="course.edit" />
  <?php echo HTMLHelper::_('form.token'); ?>
</form>

<!-- AI Quiz Generator Modal -->
<?php if (Factory::getUser()->authorise('ai.generate', 'com_splms')) : ?>
<?php if (SplmsHelper::getJoomlaVersion() < 4) : ?>
    <!-- Joomla 3 / Bootstrap 2 Modal -->
    <div id="aiQuizModal" class="modal hide fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-header">
            <button type="button" class="close" onclick="closeAiQuizModal()" aria-hidden="true">×</button>
            <h3><?php echo JText::_('Generate Quiz with AI'); ?></h3>
        </div>
        <div class="modal-body">
            <?php echo $this->loadTemplate('ai_form'); ?>
        </div>
        <div class="modal-footer">
            <button class="btn" onclick="closeAiQuizModal()" aria-hidden="true">Close</button>
            <button class="btn btn-primary" onclick="generateAiQuiz()">Generate</button>
        </div>
    </div>
<?php else : ?>
    <!-- Joomla 4/5 / Bootstrap 5 Modal -->
    <div id="aiQuizModal" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo JText::_('Generate Quiz with AI'); ?></h5>
                    <button type="button" class="btn-close" onclick="closeAiQuizModal()" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <?php echo $this->loadTemplate('ai_form'); ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeAiQuizModal()">Close</button>
                    <button type="button" class="btn btn-primary" onclick="generateAiQuiz()">Generate</button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php endif; ?>

<script>
    // Button is now embedded directly in the HTML.
    
    function openAiQuizModal() {
        if (window.jQuery && jQuery('#aiQuizModal').modal) {
            jQuery('#aiQuizModal').modal('show');
        } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            var myModal = new bootstrap.Modal(document.getElementById('aiQuizModal'));
            myModal.show();
        } else {
            // Fallback for some J4/J5 setups where jQuery is present but .modal is separate, 
            // or if bootstrap global is not exposed directly.
            // Try simpler class manipulation if all else fails (rarely works well for modals but better than nothing)
            var el = document.getElementById('aiQuizModal');
            el.classList.add('show');
            el.style.display = 'block';
            
            // Add backdrop manually if needed? Let's hope one of the above works.
            console.warn('Could not detect Bootstrap Modal API. Attempting manual show.');
        }
    }

    function closeAiQuizModal() {
        var el = document.getElementById('aiQuizModal');
        
        // Try Bootstrap 5
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            var modal = bootstrap.Modal.getInstance(el);
            if (modal) {
                modal.hide();
            } else {
                 // Try creating a new instance to hide it
                 try { new bootstrap.Modal(el).hide(); } catch(e){}
            }
        }
        
        // Try jQuery / Bootstrap 4
        if (window.jQuery && jQuery('#aiQuizModal').modal) {
            jQuery('#aiQuizModal').modal('hide');
        }

        // Force manual cleanup (Fallbacks)
        el.classList.remove('show');
        el.style.display = 'none';
        
        // Remove backdrop if it persists
        var backdrops = document.getElementsByClassName('modal-backdrop');
        while(backdrops.length > 0){
            backdrops[0].parentNode.removeChild(backdrops[0]);
        }
        document.body.classList.remove('modal-open');
    }

    function generateAiQuiz() {
        var topic = document.getElementById('ai_topic').value;
        var description = document.getElementById('ai_description').value;
        var difficulty = document.getElementById('ai_difficulty').value;
        var count = document.getElementById('ai_count').value;
        var durationMinutes = document.getElementById('ai_duration').value;
        var fileInput = document.getElementById('ai_file');
        var file = fileInput.files[0];

        if (!topic && !file) {
            alert('Please enter a topic or upload a document');
            return;
        }

        // Auto-fill duration (convert minutes to seconds)
        if (durationMinutes) {
            var seconds = parseInt(durationMinutes) * 60;
            // Try to find the duration field. Standard ID often like jform_duration
            var durationField = document.getElementById('jform_duration');
            if(durationField) {
                 durationField.value = seconds;
            }
        }

        document.getElementById('ai_status').style.display = 'block';
        
        var url = 'index.php?option=com_splms&task=ai.generateQuiz&format=json';
        
        var formData = new FormData();
        formData.append('topic', topic);
        formData.append('description', description);
        formData.append('difficulty', difficulty);
        formData.append('count', count);
        if (file) {
            formData.append('file', file);
        }

        jQuery.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false, // Required for FormData
            contentType: false, // Required for FormData
            success: function(response) {
                document.getElementById('ai_status').style.display = 'none';
                try {
                   var res = JSON.parse(response);
                } catch(e) {
                   var res = response;
                }
                
                if (typeof res === 'string') {
                    res = JSON.parse(res);
                }

                if (res.success && res.data) {
                    populateQuizForm(res.data);
                    closeAiQuizModal();
                } else {
                    alert('Error: ' + (res.message || 'Unknown error'));
                }
            },
            error: function(xhr, status, error) {
                document.getElementById('ai_status').style.display = 'none';
                console.error("AI Request Failed:", status, error, xhr.responseText);
                try {
                    // Try to parse the response as JSON even if it failed (sometimes custom 403s return JSON)
                    var res = JSON.parse(xhr.responseText);
                    if (res && res.message) {
                        alert('Erro (Server): ' + res.message);
                        return;
                    }
                } catch(e) {}
                
                alert('Falha na requisição: ' + error + ' (Status: ' + xhr.status + '). Verifique se você tem permissão ou se ocorreu um erro no servidor.');
            }
        });
    }

    function populateQuizForm(data) {
        console.log("Populating Quiz Form with data:", data);

        // Populate Title
        if (document.getElementById('jform_title')) {
            document.getElementById('jform_title').value = data.title;
        }

        // Populate Description
        if (window.Joomla && Joomla.editors && Joomla.editors.instances) {
             try {
                 if (Joomla.editors.instances['jform_description']) {
                    Joomla.editors.instances['jform_description'].setValue(data.description);
                 } else {
                    // Fallback for some editors or if instance name differs
                    var editorKeys = Object.keys(Joomla.editors.instances);
                    if (editorKeys.length > 0) {
                        Joomla.editors.instances[editorKeys[0]].setValue(data.description);
                    }
                 }
             } catch (e) {
                 console.warn('Could not set editor content directly', e);
             }
        }
        
        // Populate Questions subform
        // Heuristic: Find the button that adds to this subform
        var addBtn = document.querySelector('.subform-repeatable-group-add');
        
        if (!addBtn) {
            // Try explicit ID-based selectors (Joomla standard naming)
            addBtn = document.getElementById('jform_list_answers_add');
        }
        if (!addBtn) {
             // Try Joomla 4/5 specific custom elements or classes
             addBtn = document.querySelector('joomla-field-subform .button-add');
        }
        if (!addBtn) {
             // Try searching by data attributes
             addBtn = document.querySelector('button[data-action="add"][data-group="list_answers"]'); 
        }
        if (!addBtn) {
            // Try finding ANY button inside the field container
            var container = document.getElementById('jform_list_answers');
            if (container) {
                addBtn = container.querySelector('button.btn-success'); // Usually green
                if (!addBtn) addBtn = container.querySelector('button.btn-primary');
                if (!addBtn) addBtn = container.querySelector('.group-add-button'); // J3 Legacy
            }
        }
        if (!addBtn) {
            // Fallback: Look for a button with a predictable icon or class near the label
            // This is a "Hail Mary" pass
            var fieldLabel = document.querySelector('label[for="jform_list_answers"]');
            if (fieldLabel) {
                 // The button might be in the control div next to it
                 var controlDiv = fieldLabel.closest('.control-group, .mb-3').querySelector('.controls, .col-sm-9');
                 if (controlDiv) {
                     addBtn = controlDiv.querySelector('button');
                 }
            }
        }

        if (!addBtn) {
            console.error("Add button for subform 'jform_list_answers' not found! HTML dump of potential container:", document.getElementById('jform_list_answers'));
            alert("Erro: Não foi possível encontrar o botão para adicionar perguntas (Selector failure). Verifique o console.");
            return;
        }

        // Process questions sequentially to ensure DOM updates
        processQuestionsSequentially(data.questions, 0, addBtn);
    }

    function processQuestionsSequentially(questions, index, addBtn) {
        if (index >= questions.length) {
            console.log("All questions populated.");
            return;
        }

        var q = questions[index];
        var initialGroupsCount = document.querySelectorAll('.subform-repeatable-group').length;

        // Click Add
        addBtn.click();

        // Wait for the new group to appear
        waitForNewGroup(initialGroupsCount, function(newGroup) {
            console.log("New group found for question " + (index + 1), newGroup);
            
            // Populate fields in the new group
            var inputs = newGroup.querySelectorAll('input, select, textarea');
            inputs.forEach(function(input) {
                if (input.name.indexOf('[qes_title]') > -1) input.value = q.title;
                if (input.name.indexOf('[ans_one]') > -1) input.value = q.ans_one;
                if (input.name.indexOf('[ans_two]') > -1) input.value = q.ans_two;
                if (input.name.indexOf('[ans_three]') > -1) input.value = q.ans_three;
                if (input.name.indexOf('[ans_four]') > -1) input.value = q.ans_four;
                if (input.name.indexOf('[right_ans]') > -1) {
                    input.value = q.right_ans;
                    // Trigger change for libraries like Chosen/Select2
                    var event = new Event('change', { bubbles: true });
                    input.dispatchEvent(event);
                    if (window.jQuery) { jQuery(input).trigger('liszt:updated').trigger('chosen:updated'); }
                }
            });

            // Process next question
            processQuestionsSequentially(questions, index + 1, addBtn);
        });
    }

    function waitForNewGroup(initialCount, callback, attempts) {
        attempts = attempts || 0;
        var groups = document.querySelectorAll('.subform-repeatable-group');
        
        if (groups.length > initialCount) {
            // Found it! Return the last one
            callback(groups[groups.length - 1]);
        } else {
            if (attempts < 20) { // Try for ~2 seconds
                setTimeout(function() {
                    waitForNewGroup(initialCount, callback, attempts + 1);
                }, 100);
            } else {
                console.error("Timeout waiting for new subform group.");
            }
        }
    }
</script>
