<?php
// api/social.php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$pdo = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $rows = $pdo->query(
        "SELECT platform, url, icon FROM social_links WHERE is_active=1 ORDER BY sort_order ASC, id ASC"
    )->fetchAll();
    echo json_encode(['success'=>true,'data'=>$rows]);
    exit;
}

http_response_code(405);
echo json_encode(['success'=>false,'message'=>'Method not allowed']);
