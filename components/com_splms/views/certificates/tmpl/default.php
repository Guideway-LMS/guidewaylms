<?php
/**
* @package com_splms
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2024 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

// No Direct Access
defined ('_JEXEC') or die('Resticted Aceess');

// Get Columns (forçando layout mais equilibrado)
$columns = 2;

?>

<div id="splms" class="splms view-splms-certificates splms-certificxate-list">
	<div class="splms-row" style="display:flex; flex-wrap:wrap;">
		<?php if(count($this->items)) { ?>

		<!-- Column -->
		<?php foreach ($this->items as $item) { ?>
<div class="splms-col-md-4 splms-col-sm-6 splms-col-12"
     style="display:flex;">
    
    <div style="
    background: #F6F7FB;
    border: 1px solid rgba(0,0,0,0.06);
    padding: 20px;
    border-radius: 14px;
    box-shadow: 0 6px 18px rgba(0,0,0,0.06);
    margin-bottom: 20px;
    min-height: 240px;
    height: 100%;
    display: flex;
    flex-direction: column;
">
    <h4 style="
        margin: 0 0 12px 0;
        font-size: 20px;
        font-weight: 700;
        line-height: 1.2;
        color: #1f2937;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    ">
        🎓 <?php echo htmlspecialchars($item->course_title ?? ('Curso ID: ' . $item->course_id)); ?>
    </h4>

    <div style="color:#374151; font-size:14px; line-height:1.4;">
        <p style="margin:0 0 8px 0;"><strong>Aluno:</strong> <?php echo htmlspecialchars($item->name); ?></p>

        <p style="margin:0 0 8px 0;"><strong>Nº Certificado:</strong> <?php echo htmlspecialchars($item->certificate_no); ?></p>

        <p style="margin:0;"><strong>Data de Emissão:</strong>
            <?php echo !empty($item->issue_date) ? date('d/m/Y', strtotime($item->issue_date)) : '-'; ?>
        </p>
    </div>

    <div style="margin-top:auto; padding-top:16px;">
        <a class="btn btn-primary" style="padding:10px 14px; border-radius:10px;" href="<?php echo $item->url; ?>">
            Ver Certificado
        </a>
    </div>
</div>

<?php } ?>
<?php } ?>
	</div>
</div>

<!-- BEGIN:: Pagination -->
<?php if ($this->params->get('hide_pagination') == 0) { ?>
<?php if ($this->pagination->pagesTotal > 1) { ?>
<div class="pagination">
	<?php echo $this->pagination->getPagesLinks(); ?>
</div>
<?php } ?>
<?php } ?>
<!-- END:: Pagination -->