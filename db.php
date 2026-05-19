<?php
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    // Fallback if vendor is missing - this prevents 500 error if someone forgot composer install
    // but features requiring vendor (like dotenv) won't work unless environment variables are set via hosting panel
}

if (class_exists('Dotenv\Dotenv') && file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

function get_env_var($key, $default = null) {
    if (isset($_ENV[$key])) return $_ENV[$key];
    if (isset($_SERVER[$key])) return $_SERVER[$key];
    $val = getenv($key);
    return ($val !== false) ? $val : $default;
}

function get_db_connection() {
    $type = get_env_var('DB_TYPE', 'mysql');
    $host = get_env_var('DB_HOST', 'localhost');
    $port = get_env_var('DB_PORT', ($type === 'mysql' ? '3306' : '5432'));
    $db   = get_env_var('DB_NAME', 'chroma_db');
    $user = get_env_var('DB_USER', 'root');
    $pass = get_env_var('DB_PASS', '');
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
         error_log("Database Connection Error: " . $e->getMessage());
         throw new \PDOException("Connection failed: " . $e->getMessage(), (int)$e->getCode());
    }
}
