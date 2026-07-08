<?php
require_once __DIR__ . '/../config/auth.php';
requireLogin();
require_once __DIR__ . '/../config/db.php';

$pdo = getDB();
$msg = ''; $msgType = '';

/* ── DELETE REQUEST ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_method'] ?? '') === 'DELETE') {$pdo->prepare("DELETE FROM messages WHERE id=? AND form_type='ads'")->execute([(int)$_POST['id']]);
  header('Location: ads.php?deleted=1');
  exit;
}

/* ── SAVE SETTINGS ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_method'] ?? '') !== 'DELETE') {
  $title       = trim($_POST['title']       ?? '');
  $description = trim($_POST['description'] ?? '');

  if (!$title) {
    $msg = 'عنوان صفحه الزامی است.'; $msgType = 'error';
  } else {
    $exists = $pdo->query("SELECT COUNT(*) FROM ads_settings")->fetchColumn();
    if ($exists) {
      $pdo->prepare("UPDATE ads_settings SET title=?, description=?")->execute([$title, $description]);
    } else {
      $pdo->prepare("INSERT INTO ads_settings (title, description) VALUES (?,?)")->execute([$title, $description]);
    }
    $msg = 'تنظیمات با موفقیت ذخیره شد.'; $msgType = 'success';
  }
}

/* ── READ ── */
$settings = $pdo->query("SELECT title, description FROM ads_settings LIMIT 1")->fetch();
$requests = $pdo->query("SELECT * FROM messages WHERE form_type='ads'")->fetchAll();

$activePage = 'ads';
$pageTitle  = 'مدیریت تبلیغات';
$pageIcon   = '📢';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= $pageTitle ?> - NIRT</title>
  <link rel="stylesheet" href="assets/admin.css">
</head>
<body>
<div class="admin-wrapper">

  <?php include 'includes/sidebar.php'; ?>
  <?php include 'includes/header.php'; ?>

  <main class="admin-main">

    <div class="page-header">
      <div class="breadcrumb">
        <a href="dashboard.php">داشبورد</a>
        <span>›</span><span>تبلیغات</span>
      </div>
      <h1>📢 مدیریت تبلیغات</h1>
      <p>تنظیمات صفحه همکاری تبلیغاتی و مشاهده درخواست‌های ثبت‌شده</p>
    </div>

    <?php if ($msg): ?>
      <div class="alert alert-<?= $msgType === 'success' ? 'success' : 'error' ?>">
        <?= $msgType === 'success' ? '✅' : '❌' ?> <?= htmlspecialchars($msg) ?>
      </div>
    <?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?>
      <div class="alert alert-success">✅ درخواست حذف شد.</div>
    <?php endif; ?>

    <div style="display:grid; grid-template-columns:1fr 360px; gap:1.5rem; align-items:start;">

      <!-- لیست درخواست‌ها -->
      <div class="glass-card">
        <div class="glass-card-header">
          <h2><span class="card-icon">📋</span> درخواست‌های تبلیغاتی</h2>
          <span class="badge badge-new"><?= count($requests) ?> درخواست</span>
        </div>

        <?php if (empty($requests)): ?>
          <div class="empty-state">
            <div class="empty-icon">📢</div>
            <h3>هنوز درخواستی ثبت نشده</h3>
            <p>درخواست‌های کاربران از طریق فرم همکاری تبلیغاتی اینجا نمایش داده می‌شود</p>
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>شرکت</th>
                  <th>مسئول</th>
                  <th>تلفن</th>
                  <th>ایمیل</th>
                  <th>نوع تبلیغ</th>
                  <th>پیام</th>
                  <th>عملیات</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($requests as $r): ?>
                  <tr>
                    <td style="color:var(--text-primary); font-weight:500;"><?= htmlspecialchars($r['company_name']) ?></td>
                    <td><?= htmlspecialchars($r['name']) ?></td>
                    <td><?= htmlspecialchars($r['phone']) ?></td>
                    <td><?= htmlspecialchars($r['email']) ?></td>
                    <td>
                      <?php
                        $types = ['tv' => '📺 تلویزیون', 'radio' => '📻 رادیو', 'both' => '📺📻 هر دو'];
                        echo $types[$r['subject']] ?? htmlspecialchars($r['subject']);
                      ?>
                    </td>
                    <td style="max-width:200px;">
                      <span style="display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;">
                        <?= htmlspecialchars($r['message'] ?? '') ?>
                      </span>
                    </td><td>
                      <form method="POST" style="display:inline;"
                            onsubmit="return confirm('حذف درخواست «<?= addslashes(htmlspecialchars($r['company_name'])) ?>»؟')">
                        <input type="hidden" name="_method" value="DELETE">
                        <input type="hidden" name="id" value="<?= $r['id'] ?>">
                        <button type="submit" class="btn btn-danger btn-sm btn-icon" title="حذف">🗑️</button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

      <!-- فرم تنظیمات -->
      <div class="glass-card" style="position:sticky; top: calc(var(--header-h) + 1.5rem);">
        <div class="glass-card-header">
          <h2><span class="card-icon">⚙️</span> تنظیمات صفحه</h2>
        </div>

<form method="POST">
  <div class="form-grid cols-1" style="gap:1rem;">

    <!-- عنوان صفحه -->
    <div class="form-group floating">
      <input type="text" name="title" class="form-control"
             placeholder=" "
             required
             value="<?= htmlspecialchars($settings['title'] ?? 'همکاری تبلیغاتی') ?>">
      <label>عنوان صفحه <span class="required">*</span></label>
    </div>

    <!-- توضیحات -->
    <div class="form-group floating">
      <textarea name="description" class="form-control" rows="6"
                placeholder=" "><?= htmlspecialchars($settings['description'] ?? '') ?></textarea>
      <label>توضیحات</label>
    </div>

    <button type="submit" class="btn btn-primary">💾 ذخیره تنظیمات</button>

  </div>
</form>

      </div>

    </div><!-- /grid -->

  </main>

  <?php include 'includes/footer.php'; ?>

</div>
</body>
</html>
