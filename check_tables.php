<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!file_exists('configuration.php')) {
    die("❌ configuration.php not found.\n");
}

require_once 'configuration.php';
$config = new JConfig();

// Try connecting using the config host
$host = $config->host;
$user = $config->user;
$pass = $config->password;
$db   = $config->db;

echo "Attempting connection to $host...\n";
$mysqli = @new mysqli($host, $user, $pass, $db);

// If connection failed and host was 'mariadb', try localhost/127.0.0.1 as fallback for CLI
if ($mysqli->connect_error) {
    echo "Connection to $host failed: " . $mysqli->connect_error . "\n";
    if ($host === 'mariadb') {
        echo "Attempting fallback to 127.0.0.1...\n";
        $mysqli = @new mysqli('127.0.0.1', $user, $pass, $db);
    }
}

if ($mysqli->connect_error) {
    die("❌ Connection failed: " . $mysqli->connect_error . "\n");
}

echo "✅ Connected to database '$db'.\n";

$prefix = $config->dbprefix;
$tables_to_check = ['splms_forum_questions', 'splms_forum_answers'];

$all_good = true;

foreach ($tables_to_check as $table) {
    $full_table_name = $prefix . $table;
    $result = $mysqli->query("SHOW TABLES LIKE '$full_table_name'");
    
    if ($result && $result->num_rows > 0) {
        echo "✅ Table found: $full_table_name\n";
    } else {
        echo "❌ Table NOT found: $full_table_name\n";
        $all_good = false;
    }
}

if ($all_good) {
    echo "\n🎉 SUCCESS: All required tables exist.\n";
} else {
    echo "\n⚠️ WARNING: Some tables are missing. You may need to click 'Fix' in Joomla Admin.\n";
}
