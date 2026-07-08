<?php
// admin/includes/header.php
$pageTitle = $pageTitle ?? 'پنل مدیریت';
$pageIcon  = $pageIcon  ?? '⚙️';
?>
<header class="admin-header">

  <div class="flex gap-1" style="align-items:center;">
    <button class="sidebar-toggle" onclick="toggleSidebar()" aria-label="منو">☰</button>
    <div class="header-title">
      <div class="page-icon"><?= $pageIcon ?></div>
      <span><?= htmlspecialchars($pageTitle) ?></span>
    </div>
  </div>

  <div class="header-actions">
    <a href="../index.html" target="_blank" class="btn btn-outline btn-sm">
      🌐 مشاهده سایت
    </a>
    <div class="header-user">
      <div class="user-avatar">👤</div>
      <span><?= htmlspecialchars($_SESSION['admin_username'] ?? 'ادمین') ?></span>
    </div>
  </div>

</header>
