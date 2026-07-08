<?php
// admin/social.php
require_once __DIR__ . '/../config/auth.php';
requireLogin();
require_once __DIR__ . '/../config/db.php';

$pdo = getDB();
$msg = ''; $msgType = '';

/* ── DELETE ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_method'] ?? '') === 'DELETE') {
  $pdo->prepare("DELETE FROM social_links WHERE id=?")->execute([(int)$_POST['id']]);
  header('Location: social.php?deleted=1');
  exit;
}

/* ── TOGGLE is_active ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_method'] ?? '') === 'TOGGLE') {
  $id = (int)$_POST['id'];
  $pdo->prepare(
    "UPDATE social_links SET is_active = NOT is_active WHERE id=?"
  )->execute([$id]);
  header('Location: social.php');
  exit;
}

/* ── INSERT / UPDATE ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !in_array($_POST['_method'] ?? '', ['DELETE','TOGGLE'])) {
  $id         = (int)($_POST['id']         ?? 0);
  $platform   = trim($_POST['platform']   ?? '');
  $url        = trim($_POST['url']        ?? '');
  $icon       = trim($_POST['icon']       ?? '');
  $sort_order = (int)($_POST['sort_order'] ?? 0);
  $is_active  = isset($_POST['is_active']) ? 1 : 0;

  if (!$platform || !$url) {
    $msg = 'نام پلتفرم و آدرس الزامی است.'; $msgType = 'error';
  } else {
    if ($id) {
      $pdo->prepare(
        "UPDATE social_links SET platform=?,url=?,icon=?,sort_order=?,is_active=? WHERE id=?"
      )->execute([$platform,$url,$icon,$sort_order,$is_active,$id]);
      $msg = 'لینک با موفقیت ویرایش شد.';
    } else {
      $pdo->prepare(
        "INSERT INTO social_links (platform,url,icon,sort_order,is_active) VALUES (?,?,?,?,?)"
      )->execute([$platform,$url,$icon,$sort_order,$is_active]);
      $msg = 'لینک جدید اضافه شد.';
    }
    $msgType = 'success';
  }
}

/* ── READ ── */
$links = $pdo->query(
  "SELECT * FROM social_links ORDER BY sort_order ASC, id ASC"
)->fetchAll();

$editItem = null;
if (isset($_GET['edit'])) {
  $s = $pdo->prepare("SELECT * FROM social_links WHERE id=?");
  $s->execute([(int)$_GET['edit']]);
  $editItem = $s->fetch();
}

// پلتفرم‌های پیش‌فرض
$platforms = [
  ['name'=>'Instagram','icon'=>'📸'],
  ['name'=>'Telegram','icon'=>'✈️'],
  ['name'=>'YouTube','icon'=>'▶️'],
  ['name'=>'Twitter','icon'=>'🐦'],
  ['name'=>'WhatsApp','icon'=>'💬'],
  ['name'=>'Facebook','icon'=>'👤'],
  ['name'=>'LinkedIn','icon'=>'💼'],
  ['name'=>'Aparat','icon'=>'🎥'],
];

$activePage = 'social';
$pageTitle  = 'شبکه‌های اجتماعی';
$pageIcon   = '🔗';
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
        <span>›</span><span>شبکه‌های اجتماعی</span>
      </div>
      <h1>🔗 مدیریت شبکه‌های اجتماعی</h1>
      <p>لینک‌های شبکه‌های اجتماعی NIRT را مدیریت کنید</p>
    </div>

    <?php if ($msg): ?>
      <div class="alert alert-<?= $msgType === 'success' ? 'success' : 'error' ?>">
        <?= $msgType === 'success' ? '✅' : '❌' ?> <?= htmlspecialchars($msg) ?>
      </div>
    <?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?>
      <div class="alert alert-success">✅ لینک حذف شد.</div>
    <?php endif; ?>

    <div style="display:grid; grid-template-columns:1fr 340px; gap:1.5rem; align-items:start;">

      <!-- لیست لینک‌ها -->
      <div class="glass-card">
        <div class="glass-card-header">
          <h2><span class="card-icon">🔗</span> لینک‌های ثبت‌شده</h2>
          <span class="badge badge-new"><?= count($links) ?> لینک</span>
        </div>

        <?php if (empty($links)): ?>
          <div class="empty-state">
            <div class="empty-icon">🔗</div>
            <h3>هنوز لینکی ثبت نشده</h3>
            <p>از فرم کناری اولین شبکه اجتماعی را اضافه کنید</p>
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>آیکون</th>
                  <th>پلتفرم</th>
                  <th>آدرس</th>
                  <th>ترتیب</th>
                  <th>وضعیت</th>
                  <th>عملیات</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($links as $lnk): ?>
                  <tr>
                    <td style="font-size:1.4rem; text-align:center;">
                      <?= htmlspecialchars($lnk['icon'] ?? '🔗') ?>
                    </td>
                    <td style="color:var(--text-primary); font-weight:500;">
                      <?= htmlspecialchars($lnk['platform']) ?>
                    </td>
                    <td>
                      <a href="<?= htmlspecialchars($lnk['url']) ?>" target="_blank"
                         style="color:var(--neon-blue); font-size:.8rem;
                                word-break:break-all;">
                        <?= htmlspecialchars(substr($lnk['url'], 0, 40)) ?>...
                      </a>
                    </td>
                    <td>
                      <span class="badge badge-inactive"><?= (int)$lnk['sort_order'] ?></span>
                    </td>
                    <td>
                      <form method="POST" style="display:inline;">
                        <input type="hidden" name="_method" value="TOGGLE">
                        <input type="hidden" name="id" value="<?= $lnk['id'] ?>">
                        <button type="submit" class="badge <?= $lnk['is_active'] ? 'badge-active' : 'badge-inactive' ?>"
                                style="border:none; cursor:pointer; font-family:inherit;">
                          <?= $lnk['is_active'] ? '✅ فعال' : '⭕ غیرفعال' ?>
                        </button>
                      </form>
                    </td>
                    <td>
                      <div class="table-actions">
                        <a href="social.php?edit=<?= $lnk['id'] ?>"
                           class="btn btn-outline btn-sm btn-icon" title="ویرایش">✏️</a>
                        <form method="POST" style="display:inline;"
                              onsubmit="return confirm('حذف «<?= addslashes($lnk['platform']) ?>»؟')">
                          <input type="hidden" name="_method" value="DELETE">
                          <input type="hidden" name="id" value="<?= $lnk['id'] ?>">
                          <button type="submit" class="btn btn-danger btn-sm btn-icon">🗑️</button>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>

      <!-- فرم -->
      <!-- فرم افزودن / ویرایش لینک (با لیبل شناور) -->
      <div class="glass-card" style="position:sticky; top: calc(var(--header-h) + 1.5rem);">
        <div class="glass-card-header">
          <h2>
            <span class="card-icon"><?= $editItem ? '✏️' : '➕' ?></span>
            <?= $editItem ? 'ویرایش لینک' : 'افزودن لینک جدید' ?>
          </h2>
        </div>

        <form method="POST">
          <?php if ($editItem): ?>
            <input type="hidden" name="id" value="<?= $editItem['id'] ?>">
          <?php endif; ?>

          <div class="form-grid cols-1" style="gap:1rem;">

            <!-- پلتفرم -->
            <div class="form-group floating">
              <select name="platform" class="form-control" required>
                <option value="">انتخاب کنید...</option>
                <?php foreach ($platforms as $p): ?>
                  <option value="<?= $p['name'] ?>"
                    <?= ($editItem['platform'] ?? '') === $p['name'] ? 'selected' : '' ?>>
                    <?= $p['icon'] ?> <?= $p['name'] ?>
                  </option>
                <?php endforeach; ?>
                <?php if ($editItem && !in_array($editItem['platform'], array_column($platforms,'name'))): ?>
                  <option value="<?= htmlspecialchars($editItem['platform']) ?>" selected>
                    <?= htmlspecialchars($editItem['platform']) ?>
                  </option>
                <?php endif; ?>
              </select>
              <label>پلتفرم <span class="required">*</span></label>
              <span class="input-hint">مثلاً Instagram, Telegram و ...</span>
            </div>

            <!-- URL -->
            <div class="form-group floating">
              <input type="url" name="url" class="form-control" required
                     placeholder=" "
                     value="<?= htmlspecialchars($editItem['url'] ?? '') ?>">
              <label>آدرس لینک (URL) <span class="required">*</span></label>
            </div>

            <!-- آیکن -->
            <div class="form-group floating">
              <input type="text" name="icon" class="form-control"
                     placeholder=" "
                     value="<?= htmlspecialchars($editItem['icon'] ?? '') ?>">
              <label>آیکن (اختیاری)</label>
              <span class="input-hint">می‌توانید اموجی یا کلاس آیکن (مانند icon-instagram) وارد کنید</span>
            </div>

            <!-- ترتیب -->
            <div class="form-group floating">
              <input type="number" name="sort_order" class="form-control"
                     placeholder=" "
                     value="<?= htmlspecialchars($editItem['sort_order'] ?? (count($links) + 1)) ?>">
              <label>ترتیب نمایش</label>
              <span class="input-hint">عدد کوچکتر یعنی نمایش بالاتر</span>
            </div>

            <!-- فعال/غیرفعال (اینجا لیبل شناور نمی‌گذاریم) -->
            <div class="form-group" style="display:flex;align-items:center;gap:.5rem;">
              <label style="margin:0;">فعال باشد؟</label>
              <label class="switch">
                <input type="checkbox" name="is_active"
                       <?= ($editItem['is_active'] ?? 1) ? 'checked' : '' ?>>
                <span class="slider"></span>
              </label>
            </div>

            <div>
              <button type="submit" class="btn btn-primary">
                <?= $editItem ? '💾 ذخیره تغییرات' : '➕ افزودن لینک' ?>
              </button>
              <?php if ($editItem): ?>
                <a href="social.php" class="btn btn-ghost">انصراف</a>
              <?php endif; ?>
            </div>

          </div>
        </form>
      </div>


    </div><!-- /grid -->

  </main>

  <?php include 'includes/footer.php'; ?>

<script>
function fillPlatform(name, icon) {
  document.getElementById('platformInput').value = name;
  document.getElementById('iconInput').value     = icon;
}
</script>
</body>
</html>
