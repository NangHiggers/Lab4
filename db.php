<?php
$host = 'MySQL-5.7';
$user = 'root';
$password = '';
$database = 'ProcIMP';

$connection = new mysqli($host, $user, $password, $database);

if ($connection->connect_error) {
    die("Ошибка подключения: " . $connection->connect_error);
}

$connection->set_charset("utf8mb4");

require_once __DIR__ . '/error_handler.php';

?>