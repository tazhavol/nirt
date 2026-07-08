<?php
// config/db.php

define('DB_HOST', 'localhost');     // معمولاً همینه، اگه هاست اشتراکیه شاید فرق داشته باشه
define('DB_NAME', 'nirt');  // از cpanel بگیر
define('DB_USER', 'root'); // از cpanel بگیر
define('DB_PASS', '');// از cpanel بگیر
define('DB_CHARSET', 'utf8mb4');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST
             . ";dbname=" . DB_NAME
             . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => 'خطا در اتصال به دیتابیس'
            ]);
            exit;
        }
    }
    return $pdo;
}
