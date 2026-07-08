<?php
require_once 'config/auth.php';
require_once 'config/db.php';
requireLogin();

// حذف
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
  $pdo->prepare("DELETE FROM messages WHERE id=?")->execute([$_GET['delete']]);
  header('Location: messages.php?deleted=1'); exit;
}
// علامت‌گذاری خوانده شده
if (isset($_GET['read']) && is_numeric($_GET['read'])) {
  $pdo->prepare("UPDATE messages SET is_read=1 WHERE id=?")->execute([$_GET['read']]);
}
// علامت‌گذاری همه
if (isset($_GET['mark_all'])) {
  $pdo->exec("UPDATE messages SET is_read=1");
  header('Location: messages.php?success=1'); exit;
}

// فیلتر
$type   = $_GET['type']   ?? 'all';
$status = $_GET['status'] ?? 'all';
$search = trim($_GET['q'] ?? '');

$where  = [];
$params = [];
if ($type !== 'all') { $where[] = 'form_type=?'; $params[] = $type; }
if ($status === 'unread') { $where[] = 'is_read=0'; }
if ($status === 'read')   { $where[] = 'is_read=1'; }
if ($search !== '') {
  $where[]  = '(name LIKE ? OR email LIKE ? OR message LIKE ? OR company_name LIKE ?)';
  $params   = array_merge($params, ["%$search%","%$search%","%$search%","%$search%"]);
}
$sql  = "SELECT * FROM messages";
if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
$sql .= " ORDER BY created_at DESC";
$messages = $pdo->prepare($sql);
$messages->execute($params);
$messages = $messages->fetchAll();

// پیام انتخاب‌شده برای نمایش
$selected = null;
if (isset($_GET['view']) && is_numeric($_GET['view'])) {
  $stmt = $pdo->prepare("SELECT * FROM messages WHERE id=?");
  $stmt->execute([$_GET['view']]);
  $selected = $stmt->fetch();
  if ($selected && !$selected['is_read']) {
    $pdo->prepare("UPDATE messages SET is_read=1 WHERE id=?")->execute([$selected['id']]);
    $selected['is_read'] = 1;
  }
}

$pageTitle  = 'پیام‌ها';
$breadcrumb = [['label'=>'پیام‌ها']];
include 'includes/header.php';
?>

<!-- Page Header -->
<div class="page-header">
  <div class="page-title-group">
    <h1 class="page-title">پیام‌ها</h1>
    <p class="page-subtitle">پیام‌های دریافتی از فرم‌های تماس و آگهی</p>
  </div>
  <div class="page-actions">
    <a href="?mark_all=1" class="btn btn-secondary"
       onclick="return confirm('همه پیام‌ها به عنوان خوانده شده علامت‌گذاری شوند؟')">
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <polyline points="20 6 9 17 4 12"/>
      </svg>
      خواندن همه
    </a>
  </div>
</div>

<!-- Toolbar -->
<div class="table-toolbar mb-16">
  <form method="GET" style="display:flex;gap:8px;flex:1;flex-wrap:wrap">
    <input type="hidden" name="type"   value="<?= htmlspecialchars($type) ?>">
    <input type="hidden" name="status" value="<?= htmlspecialchars($status) ?>">
    <div class="search-box" style="max-width:280px">
      <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
      </svg>
      <input type="text" name="q" class="search-input"
             placeholder="جستجو در پیام‌ها..." value="<?= htmlspecialchars($search) ?>">
    </div>
    <button type="submit" class="btn btn-secondary btn-sm">جستجو</button>
    <?php if ($search||$type!=='all'||$status!=='all'): ?>
      <a href="messages.php" class="btn btn-ghost btn-sm">پاک کردن فیلتر</a>
    <?php endif; ?>
  </form>
</div>

<div style="display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap">
  <!-- نوع -->
  <div class="filter-tabs">
    <?php foreach (['all'=>'همه','contact'=>'تماس','ads'=>'آگهی'] as $v=>$l): ?>
      <a href="?type=<?=$v?>&status=<?=$status?>&q=<?=urlencode($search)?>"
         class="filter-tab <?= $type===$v?'active':'' ?>"><?=$l?></a>
    <?php endforeach; ?>
  </div>
  <!-- وضعیت -->
  <div class="filter-tabs">
    <?php foreach (['all'=>'همه','unread'=>'خوانده‌نشده','read'=>'خوانده‌شده'] as $v=>$l): ?>
      <a href="?type=<?=$type?>&status=<?=$v?>&q=<?=urlencode($search)?>"
         class="filter-tab <?= $status===$v?'active':'' ?>"><?=$l?></a>
    <?php endforeach; ?>
  </div>
</div>

<!-- Layout: List + Detail -->
<div style="display:grid;grid-template-columns:1fr <?= $selected?'1.4fr':'' ?>;gap:20px;align-items:start">

  <!-- لیست -->
  <div class="card" style="overflow:hidden">
    <?php if (empty($messages)): ?>
      <div class="empty-state">
        <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
          <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
        </svg>
        <h3>پیامی یافت نشد</h3>
        <p>فیلترها را تغییر دهید یا منتظر پیام جدید باشید.</p>
      </div>
    <?php else: ?>
      <?php foreach ($messages as $msg): ?>
        <?php $isActive = $selected && $selected['id'] == $msg['id']; ?>
        <div style="display:flex;align-items:center;gap:12px;padding:14px 16px;
                    border-bottom:1px solid var(--glass-border);cursor:pointer;
                    transition:background .2s;
                    background:<?= $isActive ? 'var(--glass-highlight)' : 'transparent' ?>"
             onclick="location.href='?view=<?=$msg['id']?>&type=<?=$type?>&status=<?=$status?>&q=<?=urlencode($search)?>'">

          <span class="status-dot <?= $msg['is_read'] ? 'read' : 'unread' ?>"></span>

          <div style="flex:1;min-width:0">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:3px">
              <span style="font-size:.85rem;font-weight:<?= $msg['is_read']?'400':'600' ?>;
                           color:var(--text-primary);white-space:nowrap;overflow:hidden;
                           text-overflow:ellipsis;max-width:150px">
                <?= htmlspecialchars($msg['name'] ?? $msg['company_name'] ?? '—') ?>
              </span>
              <span class="badge <?= $msg['form_type']==='ads'?'badge-purple':'badge-blue' ?>">
                <?= $msg['form_type']==='ads' ? 'آگهی' : 'تماس' ?>
              </span>
            </div>
            <div style="font-size:.75rem;color:var(--text-muted);white-space:nowrap;
                        overflow:hidden;text-overflow:ellipsis">
              <?= htmlspecialchars(mb_substr($msg['message'], 0, 50)) ?>...
            </div>
          </div>

          <div style="font-size:.7rem;color:var(--text-muted);white-space:nowrap;
                      text-align:left;flex-shrink:0">
            <?= date('d/m', strtotime($msg['created_at'])) ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <!-- جزئیات -->
  <?php if ($selected): ?>
  <div class="card">
    <div class="card-header">
      <span class="card-title">جزئیات پیام</span>
      <div style="display:flex;gap:6px">
        <a href="?view=<?=$selected['id']?>&delete=<?=$selected['id']?>"
           class="btn btn-danger btn-sm"
           onclick="return confirm('این پیام حذف شود؟')">
          <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
            <path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/>
          </svg>
          حذف
        </a>
        <a href="messages.php?type=<?=$type?>&status=<?=$status?>" class="btn btn-ghost btn-sm">
          <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
          </svg>
        </a>
      </div>
    </div>
    <div class="card-body">
      <!-- نوع فرم -->
      <div style="margin-bottom:18px">
        <span class="badge <?= $selected['form_type']==='ads'?'badge-purple':'badge-blue' ?>">
          <?= $selected['form_type']==='ads' ? 'فرم آگهی' : 'فرم تماس' ?>
        </span>
        <span class="badge <?= $selected['is_read']?'badge-gray':'badge-pink' ?>" style="margin-right:6px">
          <?= $selected['is_read'] ? 'خوانده شده' : 'جدید' ?>
        </span>
      </div>

      <!-- فیلدهای مشترک -->
      <?php
      $fields = [];
      if ($selected['form_type'] === 'contact') {
        $fields = [
          'نام'       => $selected['name']    ?? '',
          'ایمیل'     => $selected['email']   ?? '',
          'پیام'      => $selected['message'] ?? '',
        ];
      } else {
  $fields = [
  'نام شرکت'         => $selected['company_name'] ?? '',
  'نام مسئول'        => $selected['name']         ?? '',
  'شماره تلفن'       => $selected['phone']        ?? '',
  'ایمیل'            => $selected['email']        ?? '',
  'نوع آگهی'         => $selected['subject']      ?? '',
  'پیام / توضیحات'   => $selected['message']      ?? '',
];
      }
      foreach ($fields as $label => $value): if (!$value) continue; ?>
        <div style="margin-bottom:14px">
          <div class="form-label" style="margin-bottom:5px"><?= $label ?></div>
          <div style="background:var(--bg-tertiary);border:1px solid var(--glass-border);
                      border-radius:var(--radius-sm);padding:10px 13px;font-size:.85rem;
                      color:var(--text-primary);line-height:1.7;word-break:break-word">
            <?= nl2br(htmlspecialchars($value)) ?>
          </div>
        </div>
      <?php endforeach; ?>

      <!-- زمان -->
      <div class="divider"></div>
      <div style="font-size:.75rem;color:var(--text-muted)">
        دریافت در: <?= date('Y/m/d H:i', strtotime($selected['created_at'])) ?>
      </div>

      <!-- Reply -->
      <?php if (!empty($selected['email'])): ?>
        <div class="mt-16">
          <a href="mailto:<?= htmlspecialchars($selected['email']) ?>"
             class="btn btn-primary" style="width:100%;justify-content:center">
            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
              <polyline points="22,6 12,13 2,6"/>
            </svg>
            پاسخ از طریق ایمیل
          </a>
        </div>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>

</div>

<style>
@media(max-width:768px){
  div[style*="grid-template-columns:1fr 1.4fr"] {
    grid-template-columns: 1fr !important;
  }
}
</style>

<?php include 'includes/footer.php'; ?>
