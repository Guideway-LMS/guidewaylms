<?php
/**
* @package com_splms
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2024 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\HTML\HTMLHelper;

$doc = Factory::getDocument();
HTMLHelper::_('behavior.formvalidator');
HTMLHelper::_('behavior.keepalive');
if(SplmsHelper::getJoomlaVersion() < 4)
{
  HTMLHelper::_('formbehavior.chosen', 'select', null, array('disable_search_threshold' => 0 ));
}

$rowClass = SplmsHelper::getJoomlaVersion() < 4 ? 'row-fluid' : 'row';
$colClass = SplmsHelper::getJoomlaVersion() < 4 ? 'span' : 'col-lg-';

// Injetando CSS do Arquiteto IA desenvolvido em DarkMode/Glassmorphism sem quebrar bootstrap global
$doc->addStyleSheet(Uri::root(true) . '/administrator/components/com_splms/assets/css/admin-course-architect.css?v=' . rand());

// Load AI Toolbars JS Dependencies (used in Lesson and Course)
$doc->addScript(Uri::root(true) . '/administrator/components/com_splms/assets/js/notifications.js?v=' . rand());
$doc->addScript(Uri::root(true) . '/administrator/components/com_splms/assets/js/guideway_ai.js?v=' . rand());
$doc->addScript(Uri::root(true) . '/administrator/components/com_splms/assets/js/guideway_ai_chat.js?v=' . rand());

// Load AI Toolbars CSS (Importante para a colorização das dropzones)
$doc->addStyleSheet(Uri::root(true) . '/administrator/components/com_splms/assets/css/tolbar_ai.css?v=' . rand());
?>
<style>
/* Admin Cover Creator Styles */
.splms-cover-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 5px 15px rgba(0,0,0,0.1) !important;
}
#splms-cover-loading {
    display: flex;
    justify-content: center;
    padding: 2rem;
}
</style>
<script src="<?php echo Uri::root(true); ?>/administrator/components/com_splms/assets/js/admin-cover-creator.js?v=<?php echo rand(); ?>" defer></script>

<form action="<?php echo Route::_('index.php?option=com_splms&layout=edit&id=' . (int) $this->item->id); ?>"
  method="post" name="adminForm" id="adminForm" class="form-validate">

  <!-- Toolbar Extra Actions -->
  <div class="btn-toolbar mb-3" role="toolbar">
      <!-- Feature: Cover Creator (Available to All) -->
      <button type="button" class="btn btn-primary" id="splms-cover-trigger-btn" 
              data-bs-toggle="modal" data-bs-target="#splmsAdminCoverModal"
              data-toggle="modal" data-target="#splmsAdminCoverModal">
          <span class="icon-images" aria-hidden="true"></span> Buscar Capa Online (Pexels)
      </button>

      <!-- Feature: AI Architect (Restricted) -->
      <?php if (Factory::getUser()->authorise('ai.architect', 'com_splms')) : ?>
      <button type="button" class="btn btn-success ms-2 ml-2" id="splms-ai-trigger-btn" 
              data-bs-toggle="modal" data-bs-target="#splmsAiArchitectModal"
              data-toggle="modal" data-target="#splmsAiArchitectModal">
          <span class="icon-magic" aria-hidden="true"></span> Arquiteto IA
      </button>
      <?php endif; ?>
  </div>

  <div class="form-horizontal">
    <div class="<?php echo $rowClass;?>">
      <div class="<?php echo $colClass;?>9">
        <?php 
          // Campos Básicos de Abertura
          echo $this->form->renderField('title');
          echo $this->form->renderField('alias');
          echo $this->form->renderField('coursecategory_id');
          echo $this->form->renderField('short_description');
          
          // === Barras de Ferramentas IA Adicionadas do Módulo Lesson ===
          if (Factory::getUser()->authorise('ai.generate', 'com_splms')) {
              $aiContext = 'course';
              $hideGenerateQuestions = true;
              include JPATH_COMPONENT_ADMINISTRATOR . '/views/lesson/tmpl/toolbar_ai_chat.php';
          }

          if (Factory::getUser()->authorise('ai.refine', 'com_splms')) {
              include JPATH_COMPONENT_ADMINISTRATOR . '/views/lesson/tmpl/toolbar_ai.php';
          }
          
          // Campo Editor HTML onde as tools manipulam
          echo $this->form->renderField('description');

          // Restante dos campos do Fieldset "basic" originais do modelo Course:
          echo $this->form->renderField('image');
          echo $this->form->renderField('video_url');
          echo $this->form->renderField('ref_url');
          echo $this->form->renderField('spacer1');
          echo $this->form->renderField('course_schedules');
          echo $this->form->renderField('course_infos');
        ?>
      </div>
      <div class="<?php echo $colClass;?>3">
        <fieldset class="form-vertical">
          <?php echo $this->form->renderFieldset('sidebar'); ?>
        </fieldset>
      </div>
    </div>
  </div>

  <input type="hidden" name="task" value="" />
  <?php echo HTMLHelper::_('form.token'); ?>
</form>

<script src="<?php echo Uri::root(true); ?>/administrator/components/com_splms/assets/js/admin-course-architect.js?v=<?php echo rand(); ?>" defer></script>

<!-- Modal Admin Cover Creator -->
<div class="modal fade" id="splmsAdminCoverModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Criador de Capas (Busca de Imagens)</h5>
                <button type="button" class="btn-close close" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-9">
                        <div class="input-group">
                            <input type="text" id="splms-cover-search-input" class="form-control" placeholder="Buscar imagens (ex: tecnologia, educação)...">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="button" id="splms-cover-search-btn">
                                    <span class="icon-search"></span> Buscar
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 text-end text-right">
                        <small class="text-muted">Imagens via Pexels API</small>
                    </div>
                </div>
                
                <div id="splms-cover-results" class="container-fluid">
                    <div class="text-center text-muted py-5">
                        <p>Digite um termo e clique em buscar.</p>
                    </div>
                </div>
                
                <div id="splms-cover-loading" class="d-none">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Carregando...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal AI Architect -->
<div class="modal fade" id="splmsAiArchitectModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title"><i class="icon-cube"></i> Arquiteto de Cursos com IA</h5>
                <button type="button" class="btn-close close text-white" data-bs-dismiss="modal" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 border-end">
                        <h6 class="text-uppercase text-muted mb-3">Configuração</h6>
                        <div class="mb-3">
                            <label class="form-label">Link do YouTube (Opcional)</label>
                            <div class="input-group">
                                <input type="url" id="ai_youtube_link" class="form-control" placeholder="https://youtube.com/watch?v=...">
                                <button class="btn btn-secondary" type="button" id="splms-ai-youtube-btn">
                                    <span class="icon-link"></span> Inserir Link
                                </button>
                            </div>
                            <small class="text-muted d-block mt-1">Extrai o texto do vídeo para basear o conteúdo.</small>
                            <div id="ai_youtube_status" class="mt-2 text-success" style="display: none;">
                                <i class="icon-checkmark"></i> Contexto do vídeo carregado!
                            </div>
                            <textarea id="ai_youtube_context" style="display:none;"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tópico do Curso</label>
                            <input type="text" id="ai_topic" class="form-control" placeholder="Ex: Marketing Digital Avançado">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Público-Alvo</label>
                            <input type="text" id="ai_audience" class="form-control" placeholder="Ex: Empresários, Estudantes">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Objetivos de Aprendizado</label>
                            <textarea id="ai_objectives" class="form-control" rows="3" placeholder="Ex: Aprender SEO, Google Ads..."></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Idioma</label>
                            <select id="ai_language" class="form-select form-control">
                                <option value="Portuguese">Português</option>
                                <option value="English">Inglês</option>
                                <option value="Spanish">Espanhol</option>
                            </select>
                        </div>
                        <button type="button" id="splms-ai-generate-btn" class="btn btn-primary w-100">
                            <i class="icon-magic"></i> Gerar Estrutura
                        </button>
                        <div id="splms-ai-loading" class="text-center mt-3 d-none">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="small text-muted mt-2">A IA está pensando...</p>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <h6 class="text-uppercase text-muted mb-3">Pré-visualização da Estrutura</h6>
                        <div id="splms-ai-preview-content" class="p-3 bg-light rounded" style="min-height: 300px;">
                            <p class="text-center text-muted mt-5">Configure e clique em "Gerar" para ver a mágica acontecer.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer" id="splms-ai-results">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Fechar</button>
                <button type="button" id="splms-ai-apply-btn" class="btn btn-success">
                    <i class="icon-checkmark"></i> Aplicar ao Curso
                </button>
                <button type="button" id="splms-ai-download-doc" class="btn btn-primary">
                    <i class="icon-download"></i> Baixar DOCX
                </button>
            </div>
        </div>
    </div>
</div>
