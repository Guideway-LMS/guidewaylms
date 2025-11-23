<?php
defined('_JEXEC') or die;

if (empty($displayData['items'])) {
    echo '<p>Nenhum aviso publicado.</p>';
    return;
}
?>

<ul class="splms-announcement-list">
    <?php foreach ($displayData['items'] as $item): ?>
        <li class="splms-announcement-item">
            <h4><?php echo $item->title; ?></h4>
            <small>
                Por: <?php echo $item->author_name; ?> —
                <?php echo JHtml::_('date', $item->created_at, JText::_('DATE_FORMAT_LC3')); ?>
            </small>

            <div class="splms-announcement-message">
                <?php echo $item->message; ?>
            </div>
        </li>
    <?php endforeach; ?>
</ul>
