<?php
// admin/logout.php
require_once __DIR__ . '/../config/auth.php';
logoutAdmin();
header('Location: index.php');
exit;
