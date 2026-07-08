<?php
// admin/contact.php
require_once __DIR__ . '/../config/auth.php';
requireLogin();
require_once __DIR__ . '/../config/db.php';

$pdo = getDB();
$msg = ''; $msgType = '';
$tab = $_GET['tab'] ?? 'info';

/* ── POST: آپدیت اطلاعات تماس ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tab === 'info') {
  $phone   = trim($_POST['phone']   ?? '');
  $email   = trim($_POST['email']   ?? '');
  $address = trim($_POST['address'] ?? '');

  $info      = $pdo->query("SELECT * FROM contact_info LIMIT 1")->fetch();
  $map_image = $info['map_image'] ?? null;

  // حذف تصویر فعلی
  if (!empty($_POST['remove_map_image']) && $map_image) {
    @unlink('../uploads/' . $map_image);
    $map_image = null;
  }

  // آپلود تصویر جدید
  if (!empty($_FILES['map_image']['name'])) {
    $ext      = pathinfo($_FILES['map_image']['name'], PATHINFO_EXTENSION);
    $filename = 'map_' . time() . '.' . $ext;
    if (move_uploaded_file($_FILES['map_image']['tmp_name'], '../uploads/' . $filename)) {
      $map_image = $filename;
    }
  }

  if (!$phone && !$email) {
    $msg = 'حداقل شماره تلفن یا ایمیل را وارد کنید.'; $msgType = 'error';
  } else {
    $exists = $pdo->query("SELECT COUNT(*) FROM contact_info")->fetchColumn();
    if ($exists) {
      $pdo->prepare(
        "UPDATE contact_info SET phone=?, email=?, address=?, map_image=?"
      )->execute([$phone, $email, $address, $map_image]);
    } else {
      $pdo->prepare(
        "INSERT INTO contact_info (phone, email, address, map_image) VALUES (?,?,?,?)"
      )->execute([$phone, $email, $address, $map_image]);
    }
    $msg = 'اطلاعات تماس با موفقیت ذخیره شد.'; $msgType = 'success';
  }
}

/* ── DELETE پیام ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_method'] ?? '') === 'DELETE') {
  $pdo->prepare("DELETE FROM messages WHERE id=? AND form_type='contact'")
      ->execute([(int)$_POST['id']]);
  header('Location: contact.php?tab=messages&deleted=1'); exit;
}

/* ── READ ── */
$info = $pdo->query("SELECT * FROM contact_info LIMIT 1")->fetch();

$perPage   = 10;
$page      = max(1, (int)($_GET['p'] ?? 1));
$offset    = ($page - 1) * $perPage;

$totalMsgs = $pdo->query(
  "SELECT COUNT(*) FROM messages WHERE form_type = 'contact'"
)->fetchColumn();

$messages = $pdo->query(
  "SELECT * FROM messages WHERE form_type = 'contact'
   ORDER BY id DESC LIMIT $perPage OFFSET $offset"
)->fetchAll(PDO::FETCH_ASSOC);

$activePage = 'contact';
$pageTitle  = 'اطلاعات تماس';
$pageIcon   = '📬';
?>
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= $pageTitle ?> - NIRT</title>
  <link rel="stylesheet" href="assets/admin.css">
  <style>
    .tab-bar { display:flex; gap:.5rem; margin-bottom:1.5rem; }
    .tab-btn {
      padding:.55rem 1.25rem; border-radius:var(--radius-md); font-family:inherit;
      font-size:.88rem; cursor:pointer; border:1px solid var(--glass-border);
      background:var(--glass-bg); color:var(--text-secondary); transition:all .2s;
      text-decoration:none; display:inline-flex; align-items:center; gap:.4rem;
    }
    .tab-btn.active {
      background: linear-gradient(135deg,rgba(0,243,255,.15),rgba(188,19,254,.15));
      border-color:rgba(0,243,255,.35); color:var(--neon-blue);
    }
    .msg-card {
      background:var(--bg-tertiary); border:1px solid var(--glass-border);
      border-radius:var(--radius-lg); padding:1.2rem; margin-bottom:1rem;
      transition: border-color .2s;
    }
    .msg-card:hover { border-color:rgba(0,243,255,.2); }
    .msg-meta { display:flex; gap:1rem; flex-wrap:wrap; margin-bottom:.75rem; }
    .msg-meta span { font-size:.78rem; color:var(--text-muted);
                     display:flex; align-items:center; gap:.25rem; }
    .msg-subject { font-size:.95rem; font-weight:600; color:var(--text-primary); margin-bottom:.5rem; }
    .msg-body { font-size:.85rem; color:var(--text-secondary); line-height:1.7; }
    .pagination { display:flex; gap:.5rem; justify-content:center; margin-top:1.5rem; }
    .pagination a, .pagination span {
      padding:.4rem .8rem; border-radius:var(--radius-sm);
      border:1px solid var(--glass-border); color:var(--text-muted);
      text-decoration:none; font-size:.82rem;
    }
    .pagination a:hover { border-color:var(--neon-blue); color:var(--neon-blue); }
    .pagination .cur { border-color:var(--neon-blue); color:var(--neon-blue);
                       background:rgba(0,243,255,.1); }
    .map-preview img {
      width:100%; border-radius:8px; max-height:220px;
      object-fit:cover; margin-top:8px;
    }
    .map-preview .no-map {
      padding:2rem; text-align:center; color:var(--text-muted);
      border:1px dashed var(--glass-border); border-radius:8px; margin-top:8px;
    }
  </style>
</head>
<body>
<div class="admin-wrapper">

  <?php include 'includes/sidebar.php'; ?>
  <?php include 'includes/header.php'; ?>

  <main class="admin-main">

    <div class="page-header">
      <div class="breadcrumb">
        <a href="dashboard.php">داشبورد</a>
        <span>›</span><span>تماس</span>
      </div>
      <h1>📬 اطلاعات تماس و پیام‌ها</h1>
      <p>ویرایش اطلاعات تماس و مشاهده پیام‌های دریافتی</p>
    </div>

    <!-- تب‌ها -->
    <div class="tab-bar">
      <a href="contact.php?tab=info"
         class="tab-btn <?= $tab === 'info' ? 'active' : '' ?>">📋 اطلاعات تماس</a>
      <a href="contact.php?tab=messages"
         class="tab-btn <?= $tab === 'messages' ? 'active' : '' ?>">
        📨 پیام‌های دریافتی
        <?php if ($totalMsgs): ?>
          <span class="badge badge-new" style="font-size:.68rem; padding:.1rem .5rem;">
            <?= $totalMsgs ?>
          </span>
        <?php endif; ?>
      </a>
    </div>

    <?php if ($msg): ?>
      <div class="alert alert-<?= $msgType === 'success' ? 'success' : 'error' ?>">
        <?= $msgType === 'success' ? '✅' : '❌' ?> <?= htmlspecialchars($msg) ?>
      </div>
    <?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?>
      <div class="alert alert-success">✅ پیام حذف شد.</div>
    <?php endif; ?>

    <!-- ── تب: اطلاعات تماس ── -->
    <?php if ($tab === 'info'): ?>
      <div class="glass-card" style="max-width:620px;">
        <div class="glass-card-header">
          <h2><span class="card-icon">📋</span> اطلاعات تماس سایت</h2>
        </div>

        <form method="POST" action="contact.php?tab=info" enctype="multipart/form-data">
          <div class="form-grid cols-1" style="gap:1.25rem;">

            <div class="form-group floating">
              <input type="tel" name="phone" class="form-control"
                     placeholder=" "
                     value="<?= htmlspecialchars($info['phone'] ?? '') ?>"
                     style="direction:ltr; text-align:right;">
              <label>شماره تلفن</label>
              <span class="input-hint">شماره‌ای که در بخش تماس سایت نمایش می‌یابد</span>
            </div>

            <div class="form-group floating">
              <input type="email" name="email" class="form-control"
                     placeholder=" "
                     value="<?= htmlspecialchars($info['email'] ?? '') ?>"
                     style="direction:ltr; text-align:right;">
              <label>آدرس ایمیل</label>
            </div>

            <div class="form-group floating">
              <textarea name="address" class="form-control" rows="3"
                        placeholder=" "><?= htmlspecialchars($info['address'] ?? '') ?></textarea>
              <label>آدرس پستی</label>
            </div>

            <!-- تصویر نقشه -->
            <div class="form-group">
              <label>تصویر نقشه</label>
              <div class="map-preview">
                <?php if (!empty($info['map_image'])): ?>
                  <img src="../uploads/<?= htmlspecialchars($info['map_image']) ?>"
                       alt="تصویر نقشه فعلی">
                  <div style="margin-top:.5rem; display:flex; align-items:center; gap:.5rem;">
                    <input type="checkbox" name="remove_map_image" id="removeMap" value="1">
                    <label for="removeMap" style="font-size:.82rem; color:var(--text-muted); cursor:pointer;">
                      حذف تصویر فعلی
                    </label>
                  </div>
                <?php else: ?>
                  <div class="no-map">🗺️ هنوز تصویری آپلود نشده</div>
                <?php endif; ?>
              </div>
              <input type="file" name="map_image" accept="image/*"
                     class="form-control" style="margin-top:.75rem;">
              <small style="color:var(--text-muted);">
                تصویر جدید جایگزین تصویر فعلی می‌شود
              </small>
            </div>

            <div>
              <button type="submit" class="btn btn-primary btn-lg">
                💾 ذخیره اطلاعات تماس
              </button>
            </div>

          </div>
        </form>
      </div>

    <!-- ── تب: پیام‌ها ── -->
    <?php else: ?>

      <div class="glass-card">
        <div class="glass-card-header">
          <h2><span class="card-icon">📨</span> پیام‌های دریافتی</h2>
          <span class="badge badge-new"><?= $totalMsgs ?> پیام</span>
        </div>

        <?php if (empty($messages)): ?>
          <div class="empty-state">
            <div class="empty-icon">📭</div>
            <h3>هیچ پیامی دریافت نشده</h3>
            <p>پیام‌های ارسال‌شده از فرم تماس اینجا نمایش داده می‌شوند</p>
          </div>
        <?php else: ?>
          <?php foreach ($messages as $m): ?>
            <div class="msg-card">
              <div class="msg-meta">
                <strong>👤 <?= htmlspecialchars($m['name'] ?? 'ناشناس') ?></strong><br>
                <strong>📧 <?= htmlspecialchars($m['email'] ?? 'بدون ایمیل') ?></strong><br>
                <strong>📞 <?= htmlspecialchars($m['phone'] ?? 'بدون تلفن') ?></strong>
              </div>
              <div class="meta-info">
                <small>🕐 <?= htmlspecialchars($m['created_at'] ?? '') ?></small>
              </div>
              <div class="content">
                <p><strong>📌 <?= htmlspecialchars($m['subject'] ?? 'بدون موضوع') ?></strong></p>
                <p><?= nl2br(htmlspecialchars($m['message'] ?? '')) ?></p>
              </div>
              <div class="actions">
                <a href="mailto:<?= htmlspecialchars($m['email'] ?? '') ?>"
                   class="btn btn-outline btn-sm">📧 پاسخ</a>
                <form method="POST" style="display:inline;">
                  <input type="hidden" name="_method" value="DELETE">
                  <input type="hidden" name="id" value="<?= $m['id'] ?>">
                  <button type="submit" class="btn btn-outline btn-sm">🗑️ حذف</button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>

          <?php
          $totalPages = ceil($totalMsgs / $perPage);
          if ($totalPages > 1):
          ?>
            <div class="pagination">
              <?php if ($page > 1): ?>
                <a href="?tab=messages&p=<?= $page-1 ?>">‹ قبلی</a>
              <?php endif; ?>
              <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <?php if ($i === $page): ?>
                  <span class="cur"><?= $i ?></span>
                <?php else: ?>
                  <a href="?tab=messages&p=<?= $i ?>"><?= $i ?></a>
                <?php endif; ?>
              <?php endfor; ?>
              <?php if ($page < $totalPages): ?>
                <a href="?tab=messages&p=<?= $page+1 ?>">بعدی ›</a>
              <?php endif; ?>
            </div>
          <?php endif; ?>

        <?php endif; ?>
      </div>
    <?php endif; ?>

  </main>

  <?php include 'includes/footer.php'; ?>
</body>
</html>
