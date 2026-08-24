<?php
declare(strict_types=1);

$db_host = 'sql101.infinityfree.com';
$db_name = 'if0_42698950_forum';
$db_user = 'if0_42698950';
$db_pass = 'plokas333';

try {
    $pdo = new PDO(
        "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4",
        $db_user,
        $db_pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok'=>false,'error'=>'Не удалось подключиться к MySQL. Проверь config.php.'], JSON_UNESCAPED_UNICODE);
    exit;
}
