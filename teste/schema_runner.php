<?php
/**
 * Schema Runner - Guideway LMS
 * Recebe instruções SQL do Dashboard e as executa e/ou salva como arquivo em database/updates/
 */
define('_JEXEC', 1);
define('JPATH_BASE', dirname(__DIR__));

header('Content-Type: application/json');

try {
    // Read from Joomla configuration.php
    $configFile = JPATH_BASE . '/configuration.php';
    if (!file_exists($configFile)) {
        throw new Exception("configuration.php não encontrado.");
    }
    require_once $configFile;
    $config = new JConfig();

    $dbHost = $config->host;
    $dbUser = $config->user;
    $dbPass = $config->password;
    $dbName = $config->db;
    $prefix = $config->dbprefix;

    // Connect via PDO
    $dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    $action = $_POST['action'] ?? '';
    $sqlRaw = $_POST['sql'] ?? '';
    $title = $_POST['title'] ?? 'update';
    $saveOnly = isset($_POST['save_only']) && $_POST['save_only'] === '1';

    if (empty($sqlRaw)) {
        throw new Exception("O comando SQL não pode estar vazio.");
    }

    // Replace generic #__ with actual prefix
    $sql = str_replace('#__', $prefix, $sqlRaw);

    // 1. Run SQL (if requested)
    if (!$saveOnly) {
        $pdo->beginTransaction();
        try {
            // Can be multiple queries separated by semicolons
            $pdo->exec($sql);
            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            throw new Exception("Erro ao executar SQL no banco: " . $e->getMessage());
        }
    }

    // 2. Save File (if manual action)
    $fileName = null;
    $message = "Operação concluída com sucesso!";

    if ($action === 'manual') {
        $updatesDir = JPATH_BASE . '/database/updates';
        if (!is_dir($updatesDir)) {
            mkdir($updatesDir, 0755, true);
        }

        // Sanitize title
        $titleClean = preg_replace('/[^a-zA-Z0-9_]/', '_', strtolower($title));
        $dateStr = date('Ymd_His');
        $fileName = "{$dateStr}_{$titleClean}.sql";
        $filePath = $updatesDir . '/' . $fileName;

        $fileContent = "-- Guideway LMS Database Update\n";
        $fileContent .= "-- Assunto: " . htmlspecialchars($title) . "\n";
        $fileContent .= "-- Data: " . date('Y-m-d H:i:s') . "\n\n";
        // Revert any specific prefix back to #__ for portability in the Git repo
        $portableSql = str_replace($prefix, '#__', $sql);
        $fileContent .= $portableSql . ";\n";

        if (file_put_contents($filePath, $fileContent) === false) {
            throw new Exception("SQL executado, mas falha ao salvar o arquivo em database/updates/.");
        }

        $message = $saveOnly 
            ? "Script '$fileName' salvo com sucesso na pasta database/updates/."
            : "Script executado no banco e salvo como '$fileName'.";
    } elseif ($action === 'auto') {
        $message = "Sincronização automática aplicada com sucesso no MariaDB!";
    }

    echo json_encode([
        'success' => true,
        'message' => $message,
        'file' => $fileName
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => "Erro de Banco de Dados: " . $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
