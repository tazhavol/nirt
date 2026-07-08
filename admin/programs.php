<?php
// admin/programs.php
require_once __DIR__ . '/../config/auth.php';
requireLogin();
require_once __DIR__ . '/../config/db.php';

$pdo = getDB();
$msg = ''; $msgType = '';

/* ── DELETE ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_method'] ?? '') === 'DELETE') {
  $id = (int)($_POST['id'] ?? 0);
  $pdo->prepare("DELETE FROM programs WHERE id=?")->execute([$id]);
  header('Location: programs.php?deleted=1');
  exit;
}

/* ── INSERT / UPDATE ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_method'] ?? '') !== 'DELETE') {
  $id          = (int)($_POST['id']          ?? 0);
  $title       = trim($_POST['title']        ?? '');
  $image       = trim($_POST['image']        ?? '');
  $description = trim($_POST['description']  ?? '');
  $sort_order  = (int)($_POST['sort_order']  ?? 0);

  if (!$title) {
    $msg = 'عنوان برنامه الزامی است.'; $msgType = 'error';
  } else {
    if ($id) {
      $pdo->prepare(
        "UPDATE programs SET title=?,image=?,description=?,sort_order=? WHERE id=?"
      )->execute([$title, $image, $description, $sort_order, $id]);
      $msg = 'برنامه با موفقیت ویرایش شد.';
    } else {
      $pdo->prepare(
        "INSERT INTO programs (title,image,description,sort_order) VALUES (?,?,?,?)"
      )->execute([$title, $image, $description, $sort_order]);
      $msg = 'برنامه جدید با موفقیت اضافه شد.';
    }
    $msgType = 'success';
  }
}

/* ── READ ── */
$programs = $pdo->query(
  "SELECT * FROM programs ORDER BY sort_order ASC, id ASC"
)->fetchAll();

// اگر آیتمی برای ویرایش انتخاب شده
$editItem = null;
if (isset($_GET['edit'])) {
  $editItem = $pdo->prepare("SELECT * FROM programs WHERE id=?");
  $editItem->execute([(int)$_GET['edit']]);
  $editItem = $editItem->fetch();
}

$activePage = 'programs';
$pageTitle  = 'مدیریت برنامه‌ها';
$pageIcon   = '🎬';
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
        <span>›</span><span>برنامه‌ها</span>
      </div>
      <h1>🎬 مدیریت برنامه‌ها</h1>
      <p>افزودن، ویرایش و مرتب‌سازی برنامه‌های تلویزیونی</p>
    </div>

    <?php if ($msg): ?>
      <div class="alert alert-<?= $msgType === 'success' ? 'success' : 'error' ?>">
        <?= $msgType === 'success' ? '✅' : '❌' ?> <?= htmlspecialchars($msg) ?>
      </div>
    <?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?>
      <div class="alert alert-success">✅ برنامه حذف شد.</div>
    <?php endif; ?>

    <div style="display:grid; grid-template-columns:1fr 360px; gap:1.5rem; align-items:start;">

      <!-- لیست برنامه‌ها -->
      <div class="glass-card">
        <div class="glass-card-header">
          <h2><span class="card-icon">📋</span> لیست برنامه‌ها</h2>
          <span class="badge badge-new"><?= count($programs) ?> برنامه</span>
        </div>

        <?php if (empty($programs)): ?>
          <div class="empty-state">
            <div class="empty-icon">🎬</div>
            <h3>هنوز برنامه‌ای ثبت نشده</h3>
            <p>از فرم کناری اولین برنامه را اضافه کنید</p>
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="admin-table">
              <thead>
                <tr>
                  <th>تصویر</th>
                  <th>عنوان برنامه</th>
                  <th>توضیحات</th>
                  <th>ترتیب</th>
                  <th>عملیات</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($programs as $p): ?>
                  <tr>
                    <td>
                      <?php if ($p['image']): ?>
                        <img src="<?= htmlspecialchars($p['image']) ?>"
                             class="table-thumb" alt="">
                      <?php else: ?>
                        <div style="width:48px; height:36px; background:var(--bg-tertiary);
                                    border-radius:var(--radius-sm); display:flex;
                                    align-items:center; justify-content:center;
                                    font-size:1.2rem;">🎬</div>
                      <?php endif; ?>
                    </td>
                    <td style="color:var(--text-primary); font-weight:500;">
                      <?= htmlspecialchars($p['title']) ?>
                    </td>
                    <td style="max-width:220px;">
                      <span style="display:-webkit-box; -webkit-line-clamp:2;
                                   -webkit-box-orient:vertical; overflow:hidden;">
                        <?= htmlspecialchars($p['description'] ?? '') ?>
                      </span>
                    </td>
                    <td>
                      <span class="badge badge-inactive"><?= (int)$p['sort_order'] ?></span>
                    </td>
                    <td>
                      <div class="table-actions">
                        <a href="programs.php?edit=<?= $p['id'] ?>"
                           class="btn btn-outline btn-sm btn-icon" title="ویرایش">✏️</a>
                        <form method="POST" style="display:inline;"
                              onsubmit="return confirm('حذف «<?= addslashes(htmlspecialchars($p['title'])) ?>»؟')">
                          <input type="hidden" name="_method" value="DELETE">
                          <input type="hidden" name="id" value="<?= $p['id'] ?>">
                          <button type="submit" class="btn btn-danger btn-sm btn-icon"
                                  title="حذف">🗑️</button>
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

      <!-- فرم افزودن / ویرایش برنامه (با لیبل شناور) -->
      <div class="glass-card" style="position:sticky; top: calc(var(--header-h) + 1.5rem);">
        <div class="glass-card-header">
          <h2>
            <span class="card-icon"><?= $editItem ? '✏️' : '➕' ?></span>
            <?= $editItem ? 'ویرایش برنامه' : 'افزودن برنامه جدید' ?>
          </h2>
        </div>

        <form method="POST">
          <?php if ($editItem): ?>
            <input type="hidden" name="id" value="<?= $editItem['id'] ?>">
          <?php endif; ?>

          <div class="form-grid cols-1" style="gap:1rem;">

            <!-- عنوان برنامه -->
            <div class="form-group floating">
              <input type="text" name="title" class="form-control" required
                     placeholder=" "
                     value="<?= htmlspecialchars($editItem['title'] ?? '') ?>">
              <label>عنوان برنامه <span class="required">*</span></label>
            </div>

            <!-- آدرس تصویر -->
            <div class="form-group floating">
              <input type="url" name="image" class="form-control"
                     placeholder=" "
                     value="<?= htmlspecialchars($editItem['image'] ?? '') ?>">
              <label>آدرس تصویر (URL)</label>
              <span class="input-hint">تصویر برنامه که در سایت نمایش داده می‌شود</span>
            </div>

            <!-- توضیحات -->
            <div class="form-group floating">
              <textarea name="description" class="form-control" rows="4"
                        placeholder=" "><?= htmlspecialchars($editItem['description'] ?? '') ?></textarea>
              <label>توضیحات کوتاه</label>
            </div>

            <!-- ترتیب -->
            <div class="form-group floating">
              <input type="number" name="sort_order" class="form-control"
                     placeholder=" "
                     value="<?= htmlspecialchars($editItem['sort_order'] ?? (count($programs) + 1)) ?>">
              <label>ترتیب نمایش</label>
              <span class="input-hint">عدد کوچکتر یعنی نمایش بالاتر در لیست</span>
            </div>

            <div>
              <button type="submit" class="btn btn-primary">
                <?= $editItem ? '💾 ذخیره تغییرات' : '➕ افزودن برنامه' ?>
              </button>
              <?php if ($editItem): ?>
                <a href="programs.php" class="btn btn-ghost">انصراف</a>
              <?php endif; ?>
            </div>

          </div>
        </form>
      </div>


    </div><!-- /grid -->

  </main>

  <?php include 'includes/footer.php'; ?>

<script>
function previewProgramImg(url) {
  const preview = document.getElementById('programImgPreview');
  if (url) {
    preview.querySelector('img').src = url;
    preview.classList.add('show');
  } else {
    preview.classList.remove('show');
  }
}
</script>
</body>
</html>
