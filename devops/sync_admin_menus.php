<?php
require_once __DIR__ . '/auth.php';

/**
 * Sincronização de Menus - DevOps Guideway LMS
 */
// define('_JEXEC', 1);
// define('JPATH_BASE', dirname(__DIR__));
require_once JPATH_BASE . '/includes/defines.php';
require_once JPATH_BASE . '/includes/framework.php';

use Joomla\CMS\Factory;

$db = Factory::getDbo();
$results = []; // Logs
$menuItems = []; // Itens de interface

// 1. Obter Menu Principal (Pai)
$query = $db->getQuery(true)
    ->select('id, path')
    ->from('#__menu')
    ->where('client_id=1')
    ->where('link LIKE ' . $db->quote('%option=com_splms%'))
    ->where('level=1');
$db->setQuery($query);
$parent = $db->loadObject();

if (!$parent) {
    die("Menu raiz do SP LMS não encontrado no banco de dados.");
}

$parentId = $parent->id;
$parentPath = $parent->path;

// Obter o ID do Componente
$query->clear()->select('extension_id')->from('#__extensions')->where('element = "com_splms"');
$db->setQuery($query);
$componentId = (int)$db->loadResult();

$xmlPath = JPATH_BASE . '/administrator/components/com_splms/splms.xml';
if (!file_exists($xmlPath)) {
    die("Arquivo splms.xml não encontrado. Verifique a base de dados.");
}

// 2. Sincronizar a partir do XML (Leitura Inicial Passiva)
$xml = simplexml_load_file($xmlPath);
$submenuItems = $xml->administration->submenu->menu;

// Carregar pacote de tradução do Joomla para não exibir as chaves XML nuas
$lang = \Joomla\CMS\Factory::getLanguage();
$lang->load('com_splms', JPATH_BASE . '/administrator');

foreach ($submenuItems as $item) {
    $link = (string) $item['link'];
    if (strpos($link, 'index.php?') !== 0) {
        $link = 'index.php?' . ltrim($link, '/');
    }
    $title = trim((string) $item);
    $displayTitle = \Joomla\CMS\Language\Text::_($title);
    
    $menuItems[] = [
        'title' => $title,
        'displayTitle' => $displayTitle,
        'link' => $link
    ];
}

// Função nativa para inserir menus avulsos
function insertMenuItem($db, $parentId, $parentPath, $componentId, $title, $link) {
    // Iniciar atualização manual da árvore de menus (nested set)
    $res = $db->setQuery("SELECT rgt FROM #__menu WHERE id = " . (int)$parentId . " FOR UPDATE")->loadResult();
    if (!$res) throw new Exception("Parent RGT error");
    $parentRgt = (int)$res;
    
    $db->setQuery("UPDATE #__menu SET rgt = rgt + 2 WHERE rgt >= " . $parentRgt)->execute();
    $db->setQuery("UPDATE #__menu SET lft = lft + 2 WHERE lft >= " . $parentRgt)->execute();
    
    $aliasStr = strtolower(str_replace([' ', 'ó', 'ú', 'á', 'é', 'í', 'ã', 'õ', 'ç'], ['-', 'o', 'u', 'a', 'e', 'i', 'a', 'o', 'c'], $title));
    $alias = preg_replace('/[^a-z0-9\-]/', '', $aliasStr);
    $path = $parentPath . '/' . $alias;
    
    $insertQuery = $db->getQuery(true);
    $cols = ['menutype','title','alias','path','link','type','published','parent_id','level','component_id','access','img','params','lft','rgt','language','client_id'];
    $vals = [
        $db->quote('main'), $db->quote($title), $db->quote($alias), $db->quote($path), $db->quote($link),
        $db->quote('component'), 1, (int)$parentId, 2, $componentId, 1,
        $db->quote('class:component'), $db->quote('{}'), $parentRgt, $parentRgt + 1, $db->quote('*'), 1
    ];
    
    $insertQuery->insert('#__menu')->columns($db->quoteName($cols))->values(implode(',', $vals));
    $db->setQuery($insertQuery)->execute();
}

$actionTaken = false;

// 3. Processamento de Ações de Sincronização (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['sync_item'])) {
        $targetLink = $_POST['target_link'];
        $targetTitle = $_POST['target_title'];
        
        try {
            insertMenuItem($db, $parentId, $parentPath, $componentId, $targetTitle, $targetLink);
            $results[] = ['title' => $targetTitle, 'status' => 'ok', 'msg' => 'Adicionado individualmente com sucesso!'];
            $actionTaken = true;
        } catch (Exception $e) {
            $results[] = ['title' => $targetTitle, 'status' => 'error', 'msg' => 'Falha: ' . $e->getMessage()];
        }
    } elseif (isset($_POST['sync_all'])) {
        // Varre todos para encontrar ausentes
        foreach ($menuItems as $item) {
            $query->clear()->select('id')->from('#__menu')->where('client_id=1')->where('parent_id='.(int)$parentId)->where('link='.$db->quote($item['link']));
            $db->setQuery($query);
            if (!$db->loadResult()) {
                try {
                    insertMenuItem($db, $parentId, $parentPath, $componentId, $item['title'], $item['link']);
                    $results[] = ['title' => $item['title'], 'status' => 'ok', 'msg' => 'Injetado no banco com sucesso!'];
                    $actionTaken = true;
                } catch (Exception $e) {
                    $results[] = ['title' => $item['title'], 'status' => 'error', 'msg' => 'Falha: ' . $e->getMessage()];
                }
            }
        }
    }
    
    if ($actionTaken) {
        // 4. Corrigir Indentação e Ícones de todos pós modificações
        try {
            $sql = "UPDATE #__menu SET img = 'class:component', params = '{}' WHERE client_id = 1 AND parent_id = " . (int)$parentId;
            $db->setQuery($sql);
            $db->execute();
        } catch (Exception $e) {}
        
        // Limpar o cache do Joomla
        exec("rm -rf " . JPATH_BASE . "/cache/*");
        exec("rm -rf " . JPATH_BASE . "/administrator/cache/*");
    }
}

// 5. Rechecar o banco para montar a Tabela em Tempo Real
$allSynced = true;
foreach ($menuItems as &$item) {
    // Verificar se o menu já existe
    $query->clear()->select('id')->from('#__menu')->where('client_id=1')->where('parent_id='.(int)$parentId)->where('link='.$db->quote($item['link']));
    $db->setQuery($query);
    if ($db->loadResult()) {
        $item['exists'] = true;
    } else {
        $item['exists'] = false;
        $allSynced = false;
    }
}
unset($item);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sincronizar Menus - DevOps</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-primary: #0f172a;
            --bg-secondary: #1e293b;
            --bg-card: #1e293b;
            --border: #334155;
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --accent-green: #22c55e;
            --accent-red: #f43f5e;
            --accent-yellow: #eab308;
            --accent-blue: #3b82f6;
            --accent-orange: #f97316;
        }
        body { font-family: 'Inter', sans-serif; background: var(--bg-primary); color: var(--text-primary); margin: 0; padding: 40px; }
        .container { max-width: 900px; margin: 0 auto; background: var(--bg-secondary); border: 1px solid var(--border); border-radius: 12px; padding: 40px; }
        h1 { margin-top: 0; color: var(--text-primary); }
        .back-link { display: inline-block; margin-bottom: 20px; color: var(--accent-blue); text-decoration: none; font-weight: 500; }
        
        /* Alerts */
        .alert { padding: 16px 20px; border-radius: 10px; margin-bottom: 24px; display: flex; align-items: flex-start; gap: 12px; font-size: 14px; }
        .alert-success { background: rgba(34,197,94,0.1); border: 1px solid rgba(34,197,94,0.2); }
        .alert-title { font-weight: 700; margin-bottom: 4px; }
        .alert-text { color: var(--text-secondary); font-size: 13px; }

        /* Results Box */
        .result-box { margin-bottom: 30px; background: rgba(34, 197, 94, 0.05); padding: 20px; border-radius: 8px; border: 1px solid rgba(34, 197, 94, 0.2); }
        .res-item { padding: 10px; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 10px; }
        .res-item:last-child { border-bottom: none; }
        .status-ok { color: var(--accent-green); }
        .status-error { color: var(--accent-red); }

        /* Table */
        .data-table { width: 100%; border-collapse: collapse; margin: 16px 0; background: var(--bg-card); border-radius: 8px; overflow: hidden; border: 1px solid var(--border); }
        .data-table th { padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-secondary); border-bottom: 2px solid var(--border); background: rgba(0,0,0,0.2); }
        .data-table td { padding: 12px 16px; border-bottom: 1px solid var(--border); font-size: 14px; }
        .data-table tr:hover { background: rgba(255,255,255,0.02); }

        /* Buttons */
        .btn-action { background: linear-gradient(135deg, var(--accent-orange), #ea580c); border: none; color: white; padding: 12px 24px; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; display: inline-block; transition: transform 0.2s; }
        .btn-action:hover { transform: translateY(-2px); box-shadow: 0 4px 15px rgba(249,115,22,0.3); }
        
        .btn-sm { padding: 8px 16px; font-size: 12px; border-radius: 6px; }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-link">← Voltar para Dashboard</a>
        <h1>Sincronizar Menus do Admin (Joomla 5)</h1>
        <p style="color: var(--text-secondary); line-height: 1.6;">
            A interface reflete o que já existe de menus no banco de dados e o que há de novidade pendente no código (XML).
            Aplique itens individualmente ou aplique todos de uma vez se estiver precisando de uma reparação completa.
        </p>

        <?php if (!empty($results)): ?>
            <div class="result-box">
                <h3 style="margin-top: 0; margin-bottom: 10px; color: var(--accent-green);">📋 Resultado da Operação</h3>
                <?php foreach ($results as $res): ?>
                    <div class="res-item">
                        <?php if ($res['status'] === 'ok') echo '<span class="status-ok">✅</span>'; ?>
                        <?php if ($res['status'] === 'error') echo '<span class="status-error">❌</span>'; ?>
                        <div>
                            <strong><?php echo htmlspecialchars($res['title']); ?></strong>: 
                            <span style="color: var(--text-secondary);"><?php echo htmlspecialchars($res['msg']); ?></span>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div style="margin-top:15px;">
                    <p style="color:var(--text-secondary); font-size: 13px; margin: 0;"><em>Observação: o cache foi destravado. Aperte F5 na página central do seu Joomla para ver a inserção instantânea.</em></p>
                </div>
            </div>
        <?php endif; ?>

        <!-- Tabela Visual de Status -->
        <table class="data-table">
            <tr>
                <th>Item do Menu</th>
                <th>Link Base (XML)</th>
                <th style="text-align:center;">Status/Ação</th>
            </tr>
            <?php foreach ($menuItems as $item): 
                $rowBg = $item['exists'] ? 'rgba(34,197,94,0.06)' : 'rgba(244,63,94,0.06)';
            ?>
            <tr style="background: <?php echo $rowBg; ?>;">
                <td>
                    <strong><a href="../administrator/<?php echo htmlspecialchars($item['link']); ?>" target="_blank" style="color: inherit; text-decoration: none;" onmouseover="this.style.color='var(--accent-blue)'; this.style.textDecoration='underline';" onmouseout="this.style.color='inherit'; this.style.textDecoration='none';"><?php echo htmlspecialchars($item['displayTitle']); ?></a></strong>
                    <?php if($item['title'] !== $item['displayTitle']): ?>
                        <div style="font-size:10px; color:var(--text-muted); margin-top:2px; font-family:monospace;"><?php echo htmlspecialchars($item['title']); ?></div>
                    <?php endif; ?>
                </td>
                <td><code style="color: var(--text-secondary); font-size: 12px;"><?php echo htmlspecialchars($item['link']); ?></code></td>
                <td style="text-align:center;">
                    <?php if ($item['exists']): ?>
                        <span style="font-size: 18px;" title="Já existe no banco">✅</span>
                    <?php else: ?>
                        <form method="POST" style="margin: 0;">
                            <input type="hidden" name="sync_item" value="1">
                            <input type="hidden" name="target_link" value="<?php echo htmlspecialchars($item['link']); ?>">
                            <input type="hidden" name="target_title" value="<?php echo htmlspecialchars($item['title']); ?>">
                            <button type="submit" class="btn-action btn-sm">🚀 Aplicar</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>

        <!-- Ação Global -->
        <?php if ($allSynced): ?>
            <div class="alert alert-success" style="margin-top: 24px;">
                <div style="font-size: 24px;">🎉</div>
                <div>
                    <div class="alert-title" style="color: var(--accent-green);">Tudo Sincronizado</div>
                    <div class="alert-text">Todos os itens de menu programados no código já foram ativados com sucesso no seu banco de dados atual. Nenhuma ação necessária!</div>
                </div>
            </div>
        <?php else: ?>
            <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px dashed var(--border);">
                <form method="POST">
                    <input type="hidden" name="sync_all" value="1">
                    <button type="submit" class="btn-action" style="padding: 14px 32px; font-size: 16px;">🚀 Aplicar Todos Faltantes</button>
                </form>
                <p style="color: var(--text-secondary); font-size: 12px; margin-top: 10px;">Isso irá injetar as views ausentes de uma só vez respeitando o espaçamento do Joomla 5.</p>
            </div>
        <?php endif; ?>

    </div>
</body>
</html>
