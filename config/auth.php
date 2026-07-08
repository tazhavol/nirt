<?php
// config/auth.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * بررسی لاگین بودن ادمین
 * اگر لاگین نباشد → ریدایرکت به صفحه لاگین
 */
function requireLogin(): void {
    if (empty($_SESSION['admin_id'])) {
        $base = getAdminBase();
        header("Location: {$base}index.php");
        exit;
    }
}

/**
 * مسیر پایه پوشه admin را برمی‌گرداند
 */
function getAdminBase(): string {
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME']);
    $admin  = str_replace('\\', '/', realpath(__DIR__ . '/../admin'));
    if (strpos($script, $admin) === 0) {
        return '';          // از داخل admin/ صدا زده شده
    }
    return 'admin/';
}

/**
 * لاگین کردن ادمین
 */
function loginAdmin(int $id, string $username, string $fullName): void {
    session_regenerate_id(true);
    $_SESSION['admin_id']       = $id;
    $_SESSION['admin_username'] = $username;
    $_SESSION['admin_name']     = $fullName;
}

/**
 * خروج از پنل
 */
function logoutAdmin(): void {
    $_SESSION = [];
    session_destroy();
}

/**
 * اطلاعات ادمین جاری
 */
function currentAdmin(): array {
    return [
        'id'       => $_SESSION['admin_id']       ?? 0,
        'username' => $_SESSION['admin_username']  ?? '',
        'name'     => $_SESSION['admin_name']      ?? 'مدیر',
    ];
}
