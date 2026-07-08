<?php
// admin/includes/sidebar.php
// متغیر $activePage باید قبل از include تعریف شده باشد
$activePage = $activePage ?? '';

$menuItems = [
  ['icon'=>'📊','label'=>'داشبورد',      'href'=>'dashboard.php', 'key'=>'dashboard'],
  ['icon'=>'📺','label'=>'پخش زنده',    'href'=>'player.php',    'key'=>'player'],
  ['icon'=>'🎬','label'=>'برنامه‌ها',   'href'=>'programs.php',  'key'=>'programs'],
  ['icon'=>'🔗','label'=>'شبکه‌های اجتماعی','href'=>'social.php','key'=>'social'],
  ['icon'=>'📬','label'=>'اطلاعات تماس','href'=>'contact.php',  'key'=>'contact'],
  ['icon'=>'📣','label'=>'تبلیغات',     'href'=>'ads.php',       'key'=>'ads'],
];
?>
<aside class="sidebar" id="sidebar">

  <div class="sidebar-logo">
    <div class="logo-icon">📡</div>
    <div class="logo-text">
      <span>NIRT</span>
      <span>پنل مدیریت</span>
    </div>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-section-title">منو اصلی</div>
    <?php foreach ($menuItems as $item): ?>
      <div class="nav-item">
        <a href="<?= $item['href'] ?>"
           class="nav-link <?= $activePage === $item['key'] ? 'active' : '' ?>">
          <span class="nav-icon"><?= $item['icon'] ?></span>
          <span><?= $item['label'] ?></span>
        </a>
      </div>
    <?php endforeach; ?>
  </nav>

  <div class="sidebar-footer">
    <a href="logout.php">
      <span>🚪</span>
      <span>خروج از حساب</span>
    </a>
  </div>

</aside>

<div class="sidebar-overlay d-none" id="sidebarOverlay" onclick="closeSidebar()"></div>
