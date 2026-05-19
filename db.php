<?php
require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

function get_db_connection() {
    $type = $_ENV['DB_TYPE'] ?? 'mysql';
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $port = $_ENV['DB_PORT'] ?? ($type === 'mysql' ? '3306' : '5432');
    $db   = $_ENV['DB_NAME'] ?? 'chroma_db';
    $user = $_ENV['DB_USER'] ?? 'root';
    $pass = $_ENV['DB_PASS'] ?? '';
    $charset = 'utf8mb4';

    if ($type === 'mysql') {
        $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
    } else {
        $dsn = "pgsql:host=$host;port=$port;dbname=$db";
    }

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
         return new PDO($dsn, $user, $pass, $options);
    } catch (\PDOException $e) {
         throw new \PDOException($e->getMessage(), (int)$e->getCode());
    }
}
