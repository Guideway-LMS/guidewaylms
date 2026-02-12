<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!file_exists('configuration.php')) {
    die("❌ configuration.php not found.\n");
}

require_once 'configuration.php';
$config = new JConfig();

// Connect
$mysqli = @new mysqli($config->host, $config->user, $config->password, $config->db);

// Fallback logic from check_tables.php
if ($mysqli->connect_error && $config->host === 'mariadb') {
    echo "Connection to mariadb failed, trying 127.0.0.1...\n";
    $mysqli = @new mysqli('127.0.0.1', $config->user, $config->password, $config->db);
}

if ($mysqli->connect_error) {
    die("❌ Connection failed: " . $mysqli->connect_error . "\n");
}

echo "✅ Connected to database '{$config->db}'.\n";

$prefix = $config->dbprefix;
$tableName = $prefix . 'splms_lessons';

// Columns to add
// Format: Column Name => Definition
$columns = [
    'is_optional' => "TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0=Mandatory, 1=Optional' AFTER published",
    'quiz_id' => "INT(11) NOT NULL DEFAULT 0 COMMENT 'Linked Quiz ID' AFTER is_optional",
    'passing_score' => "INT(3) NULL DEFAULT NULL COMMENT 'Approving Score (0-100)' AFTER quiz_id",
     'lesson_format' => "VARCHAR(50) NOT NULL DEFAULT 'content' COMMENT 'content, assignment, quiz' AFTER passing_score"
];

foreach ($columns as $colName => $def) {
    // Check if column exists
    $check = $mysqli->query("SHOW COLUMNS FROM `$tableName` LIKE '$colName'");
    
    if ($check && $check->num_rows > 0) {
        echo "ℹ️  Column '$colName' already exists. Skipping.\n";
    } else {
        echo "➕ Adding column '$colName'...\n";
        $sql = "ALTER TABLE `$tableName` ADD COLUMN `$colName` $def";
        if ($mysqli->query($sql)) {
            echo "✅ Success.\n";
        } else {
            echo "❌ Failed: " . $mysqli->error . "\n";
        }
    }
}

echo "\nDatabase update complete.\n";
