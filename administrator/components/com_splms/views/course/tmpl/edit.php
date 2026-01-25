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
      <button type="button" class="btn btn-primary" id="splms-cover-trigger-btn" 
              data-bs-toggle="modal" data-bs-target="#splmsAdminCoverModal"
              data-toggle="modal" data-target="#splmsAdminCoverModal">
          <span class="icon-images" aria-hidden="true"></span> Buscar Capa Online (Pexels)
      </button>
  </div>

  <div class="form-horizontal">
    <div class="<?php echo $rowClass;?>">
      <div class="<?php echo $colClass;?>9">
        <?php echo $this->form->renderFieldset('basic'); ?>
      </div>
      <div class="<?php echo $colClass;?>3">
        <fieldset class="form-vertical">
          <?php echo $this->form->renderFieldset('sidebar'); ?>
        </fieldset>
      </div>
    </div>
  </div>

  <input type="hidden" name="task" value="course.edit" />
  <?php echo HTMLHelper::_('form.token'); ?>
</form>

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
