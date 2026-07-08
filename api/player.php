<?php
// api/player.php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $row = $pdo->query("SELECT title, video, thumbnail FROM player_settings LIMIT 1")->fetch();
    echo json_encode([
        'success' => true,
        'data'    => $row ?: ['title'=>'پخش زنده','video'=>'','thumbnail'=>'']
    ]);
    exit;
}

http_response_code(405);
echo json_encode(['success'=>false,'message'=>'Method not allowed']);
