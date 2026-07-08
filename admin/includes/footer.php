<?php // admin/includes/footer.php ?>
</div><!-- /.admin-main -->
</div><!-- /.admin-wrapper -->

<!-- Toast Container -->
<div class="toast-container" id="toastContainer"></div>

<script>
/* ── Sidebar Toggle ── */
function toggleSidebar() {
  const sb  = document.getElementById('sidebar');
  const ov  = document.getElementById('sidebarOverlay');
  sb.classList.toggle('open');
  ov.classList.toggle('d-none');
}
function closeSidebar() {
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('sidebarOverlay').classList.add('d-none');
}

/* ── Toast ── */
function showToast(msg, type = 'success') {
  const icons = { success:'✅', error:'❌', info:'ℹ️' };
  const colors = {
    success: 'rgba(0,255,136,.15)',
    error:   'rgba(255,0,110,.15)',
    info:    'rgba(0,243,255,.15)'
  };
  const borders = {
    success: 'rgba(0,255,136,.35)',
    error:   'rgba(255,0,110,.35)',
    info:    'rgba(0,243,255,.35)'
  };
  const t = document.createElement('div');
  t.className = 'toast';
  t.style.cssText = `background:${colors[type]};border:1px solid ${borders[type]};`;
  t.innerHTML = `<span>${icons[type]}</span><span>${msg}</span>`;
  document.getElementById('toastContainer').prepend(t);
  setTimeout(() => t.remove(), 3800);
}

/* ── Modal Helpers ── */
function openModal(id)  {
  const m = document.getElementById(id);
  if (m) m.classList.add('show');
}
function closeModal(id) {
  const m = document.getElementById(id);
  if (m) m.classList.remove('show');
}
// بستن با کلیک بیرون
document.querySelectorAll('.modal-overlay').forEach(overlay => {
  overlay.addEventListener('click', e => {
    if (e.target === overlay) overlay.classList.remove('show');
  });
});

/* ── Image Preview ── */
function previewImage(input, previewId) {
  const preview = document.getElementById(previewId);
  if (!input.files || !input.files[0]) return;
  const reader = new FileReader();
  reader.onload = e => {
    preview.querySelector('img').src = e.target.result;
    preview.classList.add('show');
  };
  reader.readAsDataURL(input.files[0]);
}

/* ── Confirm Delete ── */
function confirmDelete(url, label) {
  if (!confirm(`آیا از حذف "${label}" اطمینان دارید؟`)) return;
  fetch(url, { method: 'DELETE' })
    .then(r => r.json())
    .then(d => {
      showToast(d.message || 'حذف شد', d.success ? 'success' : 'error');
      if (d.success) setTimeout(() => location.reload(), 900);
    })
    .catch(() => showToast('خطا در ارتباط با سرور', 'error'));
}
</script>
