<?php
defined('_JEXEC') or die;

$doc = JFactory::getDocument();
$doc->addStyleDeclaration('
    .splms-announcement-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    
    .splms-announcement-item {
        background: #ffffff;
        border: 1px solid #e1e4e8;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        transition: all 0.2s ease;
    }
    
    .splms-announcement-item:hover {
        border-color: #1a73e8;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    
    .splms-announcement-item h4 {
        color: #333;
        font-size: 1.2rem;
        font-weight: 600;
        margin: 0 0 8px 0;
    }
    
    .splms-announcement-item small {
        display: block;
        color: #666;
        font-size: 0.85rem;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .splms-announcement-message {
        color: #444;
        font-size: 0.95rem;
        line-height: 1.6;
        background: transparent;
        padding: 0;
        border-radius: 0;
    }
    
    .splms-announcement-message p {
        margin: 0;
        color: #000000ff;
    }
    
    /* Animação suave fade-in */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .splms-announcement-item {
        animation: fadeInUp 0.5s ease-out;
    }
    
    /* Estado "sem avisos" */
    .splms-no-announcements {
        text-align: center;
        padding: 40px;
        background: #f5f7fa;
        border-radius: 12px;
        color: #6b7280;
    }
');

if (empty($displayData['items'])) {
    echo '<div class="splms-no-announcements">';
    echo '<p>📢 Nenhum aviso publicado no momento.</p>';
    echo '</div>';
    return;
}
?>

<ul class="splms-announcement-list">
    <?php foreach ($displayData['items'] as $item): ?>
        <li class="splms-announcement-item">
            <h4><?php echo htmlspecialchars($item->title); ?></h4>
            <small>
                📝 Por: <?php echo htmlspecialchars($item->author_name); ?> — 
                📅 <?php echo JHtml::_('date', $item->created_at, JText::_('DATE_FORMAT_LC3')); ?>
            </small>

            <div class="splms-announcement-message">
                <?php echo $item->message; ?>
            </div>
        </li>
    <?php endforeach; ?>
</ul>
