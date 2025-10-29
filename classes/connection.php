<?php
$db_host = getenv('DB_HOST') ?: 'localhost';
$db_user = getenv('DB_USER') ?: 'root';
$db_pass = getenv('DB_PASS') ?: '';
$db_name = getenv('DB_NAME') ?: 'admin_dashboard_db';

$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);

if ($mysqli->connect_errno) {
    error_log("DB Connection failed: " . $mysqli->connect_error);
    die("Database connection issue.");
}

$mysqli->set_charset("utf8mb4");
?>
