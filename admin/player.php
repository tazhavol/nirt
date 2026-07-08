<?php
// admin/player.php
require_once __DIR__ . '/../config/auth.php';
requireLogin();
require_once __DIR__ . '/../config/db.php';

$pdo = getDB();
$msg = '';
$msgType = '';

/* ── POST: ذخیره تنظیمات ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title     = trim($_POST['title']     ?? '');
  $video     = trim($_POST['video']     ?? '');
  $thumbnail = trim($_POST['thumbnail'] ?? '');

  if (!$title || !$video) {
    $msg = 'عنوان و آدرس ویدیو الزامی است.';
    $msgType = 'error';
  } else {
    $exists = $pdo->query("SELECT COUNT(*) FROM player_settings")->fetchColumn();
    if ($exists) {
      $stmt = $pdo->prepare(
        "UPDATE player_settings SET title=?, video=?, thumbnail=? WHERE id=(SELECT MIN(id) FROM (SELECT id FROM player_settings) t)"
      );
    } else {
      $stmt = $pdo->prepare(
        "INSERT INTO player_settings (title, video, thumbnail) VALUES (?,?,?)"
      );
    }
    $stmt->execute([$title, $video, $thumbnail]);
    $msg = 'تنظیمات پخش زنده با موفقیت ذخیره شد.';
    $msgType = 'success';
  }
}

/* ── GET: خواندن داده‌های فعلی ── */
$row = $pdo->query("SELECT * FROM player_settings LIMIT 1")->fetch();

$activePage = 'player';
$pageTitle  = 'مدیریت پخش زنده';
$pageIcon   = '📺';
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

    <!-- Page Header -->
    <div class="page-header">
      <div class="breadcrumb">
        <a href="dashboard.php">داشبورد</a>
        <span>›</span>
        <span>پخش زنده</span>
      </div>
      <h1>📺 مدیریت پخش زنده</h1>
      <p>تنظیمات عنوان، آدرس ویدیو و تصویر پیش‌نمایش پخش زنده</p>
    </div>

    <?php if ($msg): ?>
      <div class="alert alert-<?= $msgType === 'success' ? 'success' : 'error' ?>">
        <?= $msgType === 'success' ? '✅' : '❌' ?> <?= htmlspecialchars($msg) ?>
      </div>
    <?php endif; ?>

    <div style="display:grid; grid-template-columns:1fr 380px; gap:1.5rem; align-items:start;">

      <!-- فرم تنظیمات -->
      <div class="glass-card">
        <div class="glass-card-header">
          <h2><span class="card-icon">⚙️</span> تنظیمات پخش زنده</h2>
        </div>

<form method="POST" id="playerForm">
  <div class="form-grid cols-1" style="gap:1.25rem;">

    <!-- عنوان پخش زنده -->
    <div class="form-group floating">
      <input type="text" name="title" class="form-control"
             placeholder=" "
             value="<?= htmlspecialchars($row['title'] ?? 'پخش زنده NIRT') ?>"
             required>
      <label>عنوان پخش زنده <span class="required">*</span></label>
      <span class="input-hint">این عنوان در بخش پخش زنده سایت نمایش داده می‌شود</span>
    </div>

    <!-- آدرس ویدیو -->
    <div class="form-group floating">
      <input type="url" name="video" class="form-control"
             placeholder=" "
             value="<?= htmlspecialchars($row['video'] ?? '') ?>"
             required>
      <label>آدرس ویدیو (URL) <span class="required">*</span></label>
      <span class="input-hint">آدرس استریم زنده (HLS, RTMP یا iframe)</span>
    </div>

    <!-- تصویر پیش‌نمایش -->
    <div class="form-group floating">
      <input type="url" name="thumbnail" class="form-control"
             placeholder=" "
             value="<?= htmlspecialchars($row['thumbnail'] ?? '') ?>"
             id="thumbnailInput"
             oninput="livePreviewThumb(this.value)">
      <label>تصویر پیش‌نمایش (Thumbnail)</label>
      <span class="input-hint">آدرس تصویری که پیش از شروع پخش نشان داده می‌شود</span>
    </div>

</form>
            <div class="flex" style="gap:.75rem; padding-top:.5rem;">
              <button type="submit" class="btn btn-primary btn-lg" id="saveBtn">
                💾 ذخیره تنظیمات
              </button>
              <button type="reset" class="btn btn-outline">↩️ بازنشانی</button>
            </div>
          </div>
        </form>
      </div>

      <!-- پیش‌نمایش -->
      <div class="glass-card" style="position:sticky; top: calc(var(--header-h) + 1.5rem);">
        <div class="glass-card-header">
          <h2><span class="card-icon">👁️</span> پیش‌نمایش</h2>
        </div>

        <div style="border-radius:var(--radius-md); overflow:hidden; background:#000;
                    aspect-ratio:16/9; position:relative; border:1px solid var(--glass-border);">
          <img id="thumbPreview"
               src="<?= htmlspecialchars($row['thumbnail'] ?? '') ?>"
               alt="thumbnail"
               style="width:100%; height:100%; object-fit:cover;
                      <?= empty($row['thumbnail']) ? 'display:none;' : '' ?>">
          <div id="thumbPlaceholder"
               style="position:absolute; inset:0; display:flex; flex-direction:column;
                      align-items:center; justify-content:center; gap:.5rem; color:var(--text-muted);
                      <?= !empty($row['thumbnail']) ? 'display:none !important;' : '' ?>">
            <span style="font-size:2.5rem; opacity:.3;">📺</span>
            <span style="font-size:.8rem;">تصویر پیش‌نمایش</span>
          </div>
          <!-- دکمه play -->
          <div style="position:absolute; inset:0; display:flex; align-items:center; justify-content:center;">
            <div style="width:56px; height:56px; border-radius:50%;
                        background:rgba(0,243,255,.25); border:2px solid var(--neon-blue);
                        display:flex; align-items:center; justify-content:center;
                        font-size:1.5rem;">▶️</div>
          </div>
        </div>

        <div style="margin-top:1rem;">
          <p style="font-size:.8rem; color:var(--text-muted); text-align:center;">
            نمای تقریبی در صفحه اصلی سایت
          </p>
        </div>
      </div>

    </div><!-- end grid -->

  </main>

  <?php include 'includes/footer.php'; ?>

<script>
function livePreviewThumb(url) {
  const img = document.getElementById('thumbPreview');
  const ph  = document.getElementById('thumbPlaceholder');
  if (url) {
    img.src = url;
    img.style.display = 'block';
    ph.style.display  = 'none';
  } else {
    img.style.display = 'none';
    ph.style.display  = 'flex';
  }
}

document.getElementById('playerForm').addEventListener('submit', function() {
  const btn = document.getElementById('saveBtn');
  btn.disabled = true;
  btn.textContent = '⏳ در حال ذخیره...';
});
</script>
</body>
</html>
