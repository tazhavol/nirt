<?php
// api/contact.php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$pdo = getDB();

// ── GET: اطلاعات تماس ──────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = $pdo->query("SELECT * FROM contact_info ORDER BY id DESC LIMIT 1");
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    echo json_encode([
        'success' => true,
        'data' => $row ?: [
            'phone'     => '',
            'email'     => '',
            'address'   => '',
            'map_image' => null
        ]
    ]);
    exit;
}



// ── POST: ذخیره پیام تماس ─────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
$body = json_decode(file_get_contents('php://input'), true);

$fullName       = trim($body['fullName']       ?? '');
$contactEmail   = trim($body['contactEmail']   ?? '');
$subject        = trim($body['subject']        ?? '');
$contactPhone   = trim($body['contactPhone']   ?? '');
$contactMessage = trim($body['message'] ?? '');


    if (!$fullName || !$contactEmail || !$contactMessage) {

        http_response_code(422);
        echo json_encode(['success'=>false,'message'=>'فیلدهای اجباری را پر کنید']);
        exit;
    }
    if (!filter_var($contactEmail, FILTER_VALIDATE_EMAIL)) {
        http_response_code(422);
        echo json_encode(['success'=>false,'message'=>'ایمیل معتبر نیست']);
        exit;
    }

$stmt = $pdo->prepare(
    "INSERT INTO messages
     (name, email, subject, phone, message, form_type)
     VALUES (?, ?, ?, ?, ?, 'contact')"
);
$stmt->execute([$fullName, $contactEmail, $subject, $contactPhone ?: null, $contactMessage]);


    echo json_encode(['success'=>true,'message'=>'پیام شما با موفقیت ارسال شد']);
    exit;
}



http_response_code(405);
echo json_encode(['success'=>false,'message'=>'Method not allowed']);
