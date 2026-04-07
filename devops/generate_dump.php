<?php
require_once __DIR__ . '/auth.php';

/**
 * Script de Geração de Dump do Banco de Dados
 * Usa PHP PDO puro para gerar o dump SQL (não depende de mysqldump externo)
 */

// define('_JEXEC', 1);
// define('JPATH_BASE', dirname(__DIR__));

// Define o fuso horário padrão para garantir que o timestamp do dump e nome do arquivo estejam corretos
date_default_timezone_set('America/Sao_Paulo');

// Configurações do Banco (usar 'mariadb' que é o nome do serviço Docker na rede interna)
$dbHost = 'mariadb';
$dbPort = '3306';
$dbUser = 'root';
$dbPass = 'us35#w3(b)%';
$dbName = 'guideway_lms_db';

// Configurações do Arquivo
$usernameRaw = isset($_POST['username']) ? $_POST['username'] : 'dev';
$username = preg_replace('/[^a-zA-Z0-9_]/', '', strtolower($usernameRaw));
if (empty($username)) $username = 'dev';

$date = date('dmy_His'); // Adicionado His para melhor singularidade quando existem múltiplos dumps acontecendo
$filename = "{$username}_{$date}.sql";
$outputDir = JPATH_BASE . '/devops/database/_dumps';
$outputFile = $outputDir . '/' . $filename;

// Garante que a pasta existe
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

header('Content-Type: application/json');

try {
    // Conectar ao banco
    $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // Iniciar arquivo de dump
    $dump = "-- Guideway LMS Database Dump\n";
    $dump .= "-- Gerado em: " . date('Y-m-d H:i:s') . "\n";
    $dump .= "-- Autor do Dump: {$usernameRaw}\n";
    $dump .= "-- Host: {$dbHost}:{$dbPort}\n";
    $dump .= "-- Database: {$dbName}\n\n";
    $dump .= "SET FOREIGN_KEY_CHECKS=0;\n";
    $dump .= "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n";
    $dump .= "SET time_zone = '+00:00';\n\n";

    // Listar todas as tabelas
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $totalTables = count($tables);
    $processedTables = 0;

    foreach ($tables as $table) {
        // Estrutura da tabela
        $createTable = $pdo->query("SHOW CREATE TABLE `{$table}`")->fetch();
        $dump .= "-- --------------------------------------------------------\n";
        $dump .= "-- Estrutura da tabela `{$table}`\n";
        $dump .= "-- --------------------------------------------------------\n\n";
        $dump .= "DROP TABLE IF EXISTS `{$table}`;\n";
        $dump .= $createTable['Create Table'] . ";\n\n";

        // Dados da tabela
        $rows = $pdo->query("SELECT * FROM `{$table}`")->fetchAll();
        
        if (count($rows) > 0) {
            $dump .= "-- Dados da tabela `{$table}`\n\n";
            
            // Pegar nomes das colunas
            $columns = array_keys($rows[0]);
            $columnList = '`' . implode('`, `', $columns) . '`';
            
            // Gerar INSERTs em lotes para performance
            $batchSize = 100;
            $batches = array_chunk($rows, $batchSize);
            
            foreach ($batches as $batch) {
                $values = [];
                foreach ($batch as $row) {
                    $rowValues = [];
                    foreach ($row as $value) {
                        if ($value === null) {
                            $rowValues[] = 'NULL';
                        } else {
                            $rowValues[] = $pdo->quote($value);
                        }
                    }
                    $values[] = '(' . implode(', ', $rowValues) . ')';
                }
                $dump .= "INSERT INTO `{$table}` ({$columnList}) VALUES\n" . implode(",\n", $values) . ";\n\n";
            }
        }
        
        $processedTables++;
    }

    $dump .= "SET FOREIGN_KEY_CHECKS=1;\n";

    // Salvar arquivo
    $bytesWritten = file_put_contents($outputFile, $dump);

    if ($bytesWritten === false) {
        throw new Exception("Erro ao escrever arquivo");
    }

    $fileSize = filesize($outputFile);
    $sizeFormatted = $fileSize > 1048576 
        ? round($fileSize / 1048576, 2) . ' MB'
        : round($fileSize / 1024, 2) . ' KB';

    echo json_encode([
        'success' => true,
        'message' => "Dump gerado com sucesso: {$filename}",
        'file' => $filename,
        'size' => $sizeFormatted,
        'tables' => $totalTables
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => "Erro de conexão com o banco de dados.",
        'debug' => $e->getMessage()
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => "Erro ao gerar dump.",
        'debug' => $e->getMessage()
    ]);
}
