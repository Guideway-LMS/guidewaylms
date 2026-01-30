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

$rowClass = SplmsHelper::getJoomlaVersion() < 4 ? 'row-fluid' : 'row';
$colClass = SplmsHelper::getJoomlaVersion() < 4 ? 'span' : 'col-lg-';
?>
<form action="<?php echo Route::_('index.php?option=com_splms&layout=edit&id=' . (int) $this->item->id); ?>"
  method="post" name="adminForm" id="adminForm" class="form-validate">
  <div class="form-horizontal">
    <div class="<?php echo $rowClass;?>">
      <div class="<?php echo $colClass;?>9">
        <?php echo $this->form->renderFieldset('basic'); ?>
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
