<?php
require_once __DIR__ . '/config.php';

session_start();

function requireAuth() {
    if (!isset($_SESSION[ADMIN_SESSION_NAME]) || $_SESSION[ADMIN_SESSION_NAME] !== true) {
        header('Location: login.php');
        exit;
    }
}

function isLoggedIn() {
    return isset($_SESSION[ADMIN_SESSION_NAME]) && $_SESSION[ADMIN_SESSION_NAME] === true;
}

function logout() {
    session_destroy();
    header('Location: login.php');
    exit;
}
?>
