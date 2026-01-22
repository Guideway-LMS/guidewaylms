<?php
/**
 * Script de Diagnóstico - Verifica ambiente de execução
 */
header('Content-Type: application/json');

$diagnostics = [
    'php_user' => '',
    'mysqldump_path' => '',
    'mysqldump_version' => '',
    'test_connection' => '',
    'errors' => []
];

// 1. Usuário que está executando o PHP
$output = [];
exec('whoami 2>&1', $output);
$diagnostics['php_user'] = implode('', $output);

// 2. Caminho do mysqldump
$output = [];
exec('which mysqldump 2>&1', $output);
$diagnostics['mysqldump_path'] = implode('', $output);

// 3. Versão do mysqldump
$output = [];
exec('mysqldump --version 2>&1', $output);
$diagnostics['mysqldump_version'] = implode(' ', $output);

// 4. Teste de conexão simples ao banco
$output = [];
$returnVar = 0;
exec("mysqldump --column-statistics=0 --host=127.0.0.1 --port=3306 --user=root --password='us35#w3(b)%' --no-data guideway_lms_db 2>&1 | head -3", $output, $returnVar);
$diagnostics['test_connection'] = [
    'return_code' => $returnVar,
    'output' => $output
];

// 5. Permissão da pasta _dumps
$dumpDir = dirname(__DIR__) . '/_dumps';
$diagnostics['dump_dir'] = [
    'path' => $dumpDir,
    'exists' => is_dir($dumpDir),
    'writable' => is_writable($dumpDir)
];

echo json_encode($diagnostics, JSON_PRETTY_PRINT);
