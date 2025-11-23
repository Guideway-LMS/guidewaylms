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
        background: linear-gradient(135deg, #e7e5dbff 0%, #d1d4bfff 100%);
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 20px;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.2);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    
    .splms-announcement-item::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #f093fb 0%, #f5576c 100%);
    }
    
    .splms-announcement-item:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 40px rgba(102, 126, 234, 0.3);
    }
    
    .splms-announcement-item h4 {
        color: #000000ff;
        font-size: 1.5rem;
        font-weight: 700;
        margin: 0 0 12px 0;
        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    
    .splms-announcement-item small {
        display: block;
        color: rgba(0, 0, 0, 0.85);
        font-size: 0.9rem;
        margin-bottom: 16px;
        padding-bottom: 12px;
        border-bottom: 1px solid rgba(190, 23, 23, 0.94);
    }
    
    .splms-announcement-message {
        color: #000000ff;
        font-size: 1rem;
        line-height: 1.7;
        background: rgba(255, 255, 255, 0.67);
        padding: 16px;
        border-radius: 12px;
        backdrop-filter: blur(10px);
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
