<?php
// api/ads.php
require_once __DIR__ . '/../config/db.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$pdo = getDB();

// ── GET: تنظیمات صفحه تبلیغات ────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $row = $pdo->query("SELECT title, description FROM ads_settings LIMIT 1")->fetch();
    echo json_encode([
        'success' => true,
        'data'    => $row ?: ['title'=>'همکاری تبلیغاتی','description'=>'']
    ]);
    exit;
}

// ── POST: ذخیره درخواست تبلیغاتی ─────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $body = json_decode(file_get_contents('php://input'), true);

    $companyName   = trim($body['companyName']   ?? '');
    $contactPerson = trim($body['contactPerson'] ?? '');
    $phoneNumber   = trim($body['phoneNumber']   ?? '');
    $email         = trim($body['email']         ?? '');
    $adType        = trim($body['adType']        ?? '');
    $message       = trim($body['message']       ?? '');

    if (!$companyName || !$contactPerson || !$phoneNumber || !$email || !$adType) {
        http_response_code(422);
        echo json_encode(['success'=>false,'message'=>'فیلدهای اجباری را پر کنید']);
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(422);
        echo json_encode(['success'=>false,'message'=>'ایمیل معتبر نیست']);
        exit;
    }
    if (!in_array($adType, ['tv','radio','both','digital','sponsor'])) {
        http_response_code(422);
        echo json_encode(['success'=>false,'message'=>'نوع تبلیغ معتبر نیست']);
        exit;
    }

$stmt = $pdo->prepare(
    "INSERT INTO messages (company_name, name, phone, email, subject, message, form_type)
     VALUES (?, ?, ?, ?, ?, ?, 'ads')"
);
$stmt->execute([$companyName, $contactPerson, $phoneNumber, $email, $adType, $message]);


    echo json_encode(['success'=>true,'message'=>'درخواست شما با موفقیت ثبت شد']);
    exit;
}



http_response_code(405);
echo json_encode(['success'=>false,'message'=>'Method not allowed']);
