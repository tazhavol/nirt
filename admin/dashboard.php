<?php
// admin/dashboard.php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/auth.php';
requireLogin();

$pdo   = getDB();
$admin = currentAdmin();

// آمار کلی - اصلاح شده برای خواندن از جدول messages
$stats = [
    'programs'  => $pdo->query("SELECT COUNT(*) FROM programs")->fetchColumn(),
    'social'    => $pdo->query("SELECT COUNT(*) FROM social_links WHERE is_active=1")->fetchColumn(),
    'messages'  => $pdo->query("SELECT COUNT(*) FROM messages WHERE form_type='contact'")->fetchColumn(),
    'unread'    => $pdo->query("SELECT COUNT(*) FROM messages WHERE form_type='contact' AND is_read=0")->fetchColumn(),
    'ads_total' => $pdo->query("SELECT COUNT(*) FROM messages WHERE form_type='ads'")->fetchColumn(),
    'ads_new'   => $pdo->query("SELECT COUNT(*) FROM messages WHERE form_type='ads' AND is_read=0")->fetchColumn(),
];

// آخرین پیام‌های تماس - اصلاح شده
$lastMessages = $pdo->query(
    "SELECT * FROM messages WHERE form_type='contact' ORDER BY created_at DESC LIMIT 5"
)->fetchAll();

// آخرین درخواست‌های تبلیغاتی - اصلاح شده
$lastAds = $pdo->query(
    "SELECT * FROM messages WHERE form_type='ads' ORDER BY created_at DESC LIMIT 5"
)->fetchAll();
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>داشبورد - پنل مدیریت NIRT</title>
<link rel="stylesheet" href="assets/admin.css">
</head>
<body>

<?php include __DIR__ . '/includes/sidebar.php'; ?>

<div class="admin-main">
  <?php include __DIR__ . '/includes/header.php'; ?>

  <div class="admin-content">
    <div class="page-title">
      <h1>داشبورد</h1>
      <p>خوش آمدید، <?= htmlspecialchars($admin['name']) ?></p>
    </div>

    <!-- کارت‌های آمار -->
    <div class="stats-grid">
      <div class="stat-card">
        <div class="stat-icon" style="--c:#00d4ff">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/>
          </svg>
        </div>
        <div class="stat-info">
          <span class="stat-num"><?= $stats['programs'] ?></span>
          <span class="stat-label">برنامه‌ها</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon" style="--c:#8b00ff">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/>
          </svg>
        </div>
        <div class="stat-info">
          <span class="stat-num"><?= $stats['social'] ?></span>
          <span class="stat-label">شبکه‌های اجتماعی</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon" style="--c:#00ff88">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
          </svg>
        </div>
<div class="stat-info">
  <span class="stat-num"><?= $stats['unread'] ?></span>
  <span class="stat-label">پیام‌های تماس</span>
</div>

      </div>

      <div class="stat-card">
        <div class="stat-icon" style="--c:#ff6b00">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
          </svg>
        </div>
<div class="stat-info">
  <span class="stat-num"><?= $stats['ads_new'] ?></span>
  <span class="stat-label">درخواست‌های تبلیغاتی</span>
</div>

      </div>
    </div>

    <!-- دو ستون: پیام‌ها + تبلیغات -->
    <div class="two-col">

      <!-- آخرین پیام‌ها - اصلاح شده برای خواندن از messages -->
      <div class="card">
        <div class="card-head">
          <h2>آخرین پیام‌های تماس</h2>
          <a href="contact.php" class="link-more">مشاهده همه</a>
        </div>
        <div class="table-wrap">
          <table class="admin-table">
            <thead>
              <tr><th>نام</th><th>موضوع</th><th>تاریخ</th><th>وضعیت</th></tr>
            </thead>
            <tbody>
              <?php foreach ($lastMessages as $msg): ?>
              <tr>
                <td><?= htmlspecialchars($msg['name']) ?></td>
                <td><?= htmlspecialchars($msg['subject']) ?></td>
                <td><?= substr($msg['created_at'], 0, 10) ?></td>
                <td>
                  <?php if (!$msg['is_read']): ?>
                    <span class="badge badge-new">جدید</span>
                  <?php else: ?>
                    <span class="badge badge-read">خوانده شده</span>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($lastMessages)): ?>
              <tr><td colspan="4" class="empty-row">پیامی وجود ندارد</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <!-- آخرین درخواست‌های تبلیغاتی - اصلاح شده -->
      <div class="card">
        <div class="card-head">
          <h2>آخرین درخواست‌های تبلیغاتی</h2>
          <a href="ads.php" class="link-more">مشاهده همه</a>
        </div>
        <div class="table-wrap">
          <table class="admin-table">
            <thead>
              <tr><th>شرکت</th><th>نوع</th><th>تاریخ</th><th>وضعیت</th></tr>
            </thead>
            <tbody>
              <?php
              $adTypeLabel = ['tv'=>'تلویزیونی','radio'=>'رادیویی','both'=>'ترکیبی'];
              ?>
              <?php foreach ($lastAds as $ad): ?>
              <tr>
                <td><?= htmlspecialchars($ad['company_name']) ?></td>
                <td><?= $adTypeLabel[$ad['subject']] ?? $ad['subject'] ?></td>
                <td><?= substr($ad['created_at'], 0, 10) ?></td>
                <td>
                  <span class="badge badge-pending">در انتظار</span>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($lastAds)): ?>
              <tr><td colspan="4" class="empty-row">درخواستی وجود ندارد</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div> <!-- /.two-col -->

  </div> <!-- /.admin-content -->
</div> <!-- /.admin-main -->

</body>
</html>
