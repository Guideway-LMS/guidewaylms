<?php
require_once __DIR__ . '/auth.php';

/**
 * Schema Checker - Guideway LMS
 * Compara o banco de dados atual com o dump mais recente para gerar sugestões.
 */
// define('_JEXEC', 1);
// define('JPATH_BASE', dirname(__DIR__));

header('Content-Type: application/json');

try {
    // Ler do configuration.php do Joomla
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

    // Conectar via PDO
    $dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    // 1. Achar o Dump Mais Recente
    $dumpDir = JPATH_BASE . '/devops/database/_dumps';
    $latestDumpFile = null;
    $latestTime = 0;

    if (is_dir($dumpDir)) {
        $files = scandir($dumpDir);
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'sql') {
                $filePath = $dumpDir . '/' . $file;
                
                // Ler as primeiras linhas para encontrar a data interna de geração
                // Isso é imune ao git pull alterando o filemtime
                $handle = @fopen($filePath, "r");
                $internalTime = 0;
                $isOfficialDump = false;

                if ($handle) {
                    for ($i = 0; $i < 5; $i++) {
                        $line = fgets($handle);
                        if ($line === false) break;
                        
                        // Exemplo: -- Gerado em: 2026-02-25 15:30:00
                        if (preg_match('/-- Gerado em: (\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})/', $line, $matches)) {
                            $internalTime = strtotime($matches[1]);
                            $isOfficialDump = true;
                            break;
                        }
                    }
                    fclose($handle);
                }
                
                // Ignorar completamente arquivos que não possuem o cabeçalho oficial de Dump
                if (!$isOfficialDump) {
                    continue;
                }

                if ($internalTime > $latestTime) {
                    $latestTime = $internalTime;
                    $latestDumpFile = $filePath;
                }
            }
        }
    }

    if (!$latestDumpFile) {
        throw new Exception("Nenhum dump encontrado na pasta devops/database/_dumps/.");
    }

    $dumpContent = file_get_contents($latestDumpFile);
    $dumpFileName = basename($latestDumpFile);

    // 2. Parsear o Dump
    $dumpSchema = [];
    $dumpCreates = [];
    
    // Buscar todos os blocos CREATE TABLE
    // Usamos um regex preg_match_all que pega desde `CREATE TABLE` até o final com `);` ou `ENGINE=...;`
    preg_match_all("/CREATE TABLE \`([^\`]+)\` \((.*?)\) ENGINE=.*?;/s", $dumpContent, $tableMatches);

    if (isset($tableMatches[1])) {
        foreach ($tableMatches[1] as $index => $tableName) {
            $rawColumnsBlock = $tableMatches[2][$index];
            $fullCreateStmt = $tableMatches[0][$index];
            
            // Normalizar prefixo para #__
            $normalizedName = str_replace($prefix, '#__', $tableName);
            
            $dumpCreates[$normalizedName] = $fullCreateStmt;
            $dumpSchema[$normalizedName] = [];
            
            // Parsear colunas do bloco
            $lines = explode("\n", $rawColumnsBlock);
            foreach ($lines as $line) {
                $line = trim($line);
                // Ignorar linhas vazias, keys, constraint etc., para checagem das colunas
                if (empty($line)) continue;
                if (preg_match("/^(PRIMARY KEY|KEY|UNIQUE KEY|CONSTRAINT|FULLTEXT|INDEX)\b/i", $line)) continue;
                
                // Match da coluna
                if (preg_match("/^\`([^\`]+)\`\s+([A-Za-z0-9_]+(?:\([^)]+\))?.*?)(?:,|$)/i", $line, $colMatch)) {
                    $colName = $colMatch[1];
                    $colDef = $colMatch[2]; // ex: "int(11) NOT NULL AUTO_INCREMENT"
                    $dumpSchema[$normalizedName][$colName] = [
                        'name' => $colName,
                        'def' => $colDef,
                        'line' => $line // para podermos extrair o statement ALTER inteiro, se precisar
                    ];
                }
            }
        }
    }

    // 3. Obter Schema do DB Ao Vivo (Local)
    $liveSchema = [];
    $liveTablesStmt = $pdo->query("SHOW TABLES");
    $liveTables = $liveTablesStmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($liveTables as $liveTable) {
        $normalizedName = str_replace($prefix, '#__', $liveTable);
        
        $liveSchema[$normalizedName] = [];
        $columnsStmt = $pdo->query("SHOW COLUMNS FROM `{$liveTable}`");
        $columns = $columnsStmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $col) {
            $liveSchema[$normalizedName][$col['Field']] = $col['Type'];
        }
    }

    // 4. Calcular o Diff
    // Só focaremos em relatar differences em tabelas do próprio Joomla (têm o prefixo)
    $diff = [
        'missing_tables' => [],      // No Dump mas não no Live
        'missing_columns' => [],     // No Dump mas não no Live
        'extra_tables' => [],        // No Live mas não no Dump (Apenas como Warning)
        'extra_columns' => []        // No Live mas não no Dump (Apenas como Warning)
    ];

    // Verificar o que falta no Live (Ações automáticas sugeridas)
    foreach ($dumpSchema as $dTable => $dCols) {
        if (!isset($liveSchema[$dTable])) {
            // Faltando tabela iteira no DB atual
            // Nós repassamos a declaração bruta de criação da tabela.
            
            // Precisa garantir que a sugestão reverta #__ para o prefixo atual?
            // O Runner aceita #__, então vamos dar a sugestão com #__
            $suggestedSql = str_replace($prefix, '#__', $dumpCreates[$dTable]);
            $diff['missing_tables'][] = [
                'table' => $dTable,
                'sql' => $suggestedSql
            ];
            continue;
        }

        // Se a tabela existe, vamos verificar as colunas
        foreach ($dCols as $colName => $colInfo) {
            if (!isset($liveSchema[$dTable][$colName])) {
                // Faltando coluna
                // Extrair `nome_coluna definição_coluna` -> ex: `lesson_format` varchar(30) NOT NULL
                $lineDef = rtrim($colInfo['line'], ','); // remove virgula final
                $suggestedSql = "ALTER TABLE `{$dTable}` ADD COLUMN {$lineDef};";
                
                $diff['missing_columns'][] = [
                    'table' => $dTable,
                    'column' => $colName,
                    'sql' => $suggestedSql
                ];
            }
        }
    }

    // Verificar o que sobra no Live (Apenas avisos para o dev: "Você criou e precisa dumpar ou salvar script")
    foreach ($liveSchema as $lTable => $lCols) {
        if (!isset($dumpSchema[$lTable])) {
            // Apenas ignore tabelas que talvez não devam estar lá, exceto se elas tiverem o prefixo
            if (strpos($lTable, '#__') === 0) {
                $diff['extra_tables'][] = [
                    'table' => $lTable,
                    'drop_sql' => "DROP TABLE `{$lTable}`;"
                ];
            }
            continue;
        }

        foreach ($lCols as $colName => $colType) {
            if (!isset($dumpSchema[$lTable][$colName])) {
                $diff['extra_columns'][] = [
                    'table' => $lTable,
                    'column' => $colName,
                    'drop_sql' => "ALTER TABLE `{$lTable}` DROP COLUMN `{$colName}`;"
                ];
            }
        }
    }

    // Status macro: se tem missing, é ERROR. Se tem extras, é WARNING. Se vazio, é OK.
    $status = 'ok';
    if (count($diff['missing_tables']) > 0 || count($diff['missing_columns']) > 0) {
        $status = 'error';
    } elseif (count($diff['extra_tables']) > 0 || count($diff['extra_columns']) > 0) {
        $status = 'warning';
    }

    echo json_encode([
        'success' => true,
        'dump_file' => $dumpFileName,
        'status' => $status,
        'diff' => $diff
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
