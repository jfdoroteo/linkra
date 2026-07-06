// ── THEME ────────────────────────────────────────────────────
(function(){
  const t = localStorage.getItem('linkra_theme') || 'light';
  document.documentElement.setAttribute('data-theme', t);
})();

function toggleTheme() {
  const cur  = document.documentElement.getAttribute('data-theme');
  const next = cur === 'dark' ? 'light' : 'dark';
  document.documentElement.setAttribute('data-theme', next);
  localStorage.setItem('linkra_theme', next);
  _updateThemeBtn();
}

function _updateThemeBtn() {
  const cur = document.documentElement.getAttribute('data-theme');
  document.querySelectorAll('.theme-toggle').forEach(btn => {
    btn.innerHTML = cur === 'dark'
      ? '<i class="ti ti-sun"></i><span>Light</span>'
      : '<i class="ti ti-moon"></i><span>Dark</span>';
  });
}

// ── SIDEBAR ──────────────────────────────────────────────────
function toggleSidebar() {
  document.querySelector('.sidebar')?.classList.toggle('open');
}

// ── MODALS ───────────────────────────────────────────────────
function openModal(id)  { document.getElementById(id)?.classList.add('open'); }
function closeModal(id) { document.getElementById(id)?.classList.remove('open'); }

// ── COMMENTS TOGGLE ──────────────────────────────────────────
function toggleComments(id) {
  const el = document.getElementById('cmts-' + id);
  if (el) el.style.display = el.style.display === 'none' ? 'block' : 'none';
}

// ── 10-MIN COUNTDOWN ─────────────────────────────────────────
function startCountdown(submittedAt) {
  const wrap  = document.getElementById('countdown-wrap');
  const timer = document.getElementById('timer');
  if (!wrap || !timer) return;
  const deadline = submittedAt * 1000 + 10 * 60 * 1000;
  const tick = () => {
    const left = Math.max(0, Math.floor((deadline - Date.now()) / 1000));
    timer.textContent = String(Math.floor(left/60)).padStart(2,'0') + ':' + String(left%60).padStart(2,'0');
    if (left === 0) {
      wrap.classList.add('expired');
      timer.textContent = 'Expired';
      const btn = document.getElementById('replace-btn');
      if (btn) btn.disabled = true;
      clearInterval(iv);
    }
  };
  tick();
  const iv = setInterval(tick, 1000);
}

// ── IMAGE PREVIEW ─────────────────────────────────────────────
function previewImage(input, previewId) {
  const file = input.files[0];
  if (!file) return;
  const preview = document.getElementById(previewId);
  if (!preview) return;
  const reader = new FileReader();
  reader.onload = e => {
    preview.src = e.target.result;
    preview.style.display = 'block';
  };
  reader.readAsDataURL(file);
}

// ── DOM READY ─────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  _updateThemeBtn();

  // Escape closes modals
  document.addEventListener('keydown', e => {
    if (e.key === 'Escape')
      document.querySelectorAll('.modal-overlay.open').forEach(m => m.classList.remove('open'));
  });

  // Close sidebar on outside click (mobile)
  document.addEventListener('click', e => {
    const sidebar = document.querySelector('.sidebar');
    const ham     = document.querySelector('.hamburger');
    if (sidebar && sidebar.classList.contains('open')
        && !sidebar.contains(e.target)
        && e.target !== ham && !ham?.contains(e.target)) {
      sidebar.classList.remove('open');
    }
  });

  // Char counters
  document.querySelectorAll('textarea[maxlength], input[maxlength]').forEach(el => {
    const max = parseInt(el.getAttribute('maxlength'));
    const counter = el.closest('.field-wrap')?.querySelector('.char-counter')
                 || el.parentElement?.querySelector('.char-counter');
    if (!counter) return;
    const update = () => {
      const len = el.value.length;
      counter.textContent = len + '/' + max;
      counter.className = 'char-counter' + (len >= max ? ' over' : len > max * .9 ? ' warn' : '');
    };
    el.addEventListener('input', update);
    update();
  });

  // Filter chips
  document.querySelectorAll('.chip[data-filter]').forEach(chip => {
    chip.addEventListener('click', () => {
      const group  = chip.closest('.chips');
      const bar    = chip.closest('.filter-bar');
      group?.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
      chip.classList.add('active');
      const filter = chip.dataset.filter;
      const tClass = bar?.dataset.target;
      if (!tClass) return;
      document.querySelectorAll('.' + tClass).forEach(card => {
        const match = filter === 'all'
          || card.dataset.status   === filter
          || card.dataset.platform === filter
          || card.dataset.type     === filter
          || card.dataset.cat      === filter;
        card.style.display = match ? '' : 'none';
      });
    });
  });

  // Role selector
  document.querySelectorAll('.role-opt').forEach(opt => {
    opt.addEventListener('click', () => {
      document.querySelectorAll('.role-opt').forEach(o => o.classList.remove('selected'));
      opt.classList.add('selected');
      opt.querySelector('input[type=radio]').checked = true;
      document.querySelectorAll('.role-fields').forEach(f => f.style.display = 'none');
      document.getElementById('fields-' + opt.dataset.role)?.style.setProperty('display','block');
    });
  });

  // Auto-dismiss alerts
  document.querySelectorAll('.alert[data-auto-dismiss]').forEach(a => {
    setTimeout(() => { a.style.transition='opacity .4s'; a.style.opacity='0'; setTimeout(()=>a.remove(),400); }, 4000);
  });

  // File upload preview via data-preview attribute
  document.querySelectorAll('input[type=file][data-preview]').forEach(input => {
    input.addEventListener('change', () => previewImage(input, input.dataset.preview));
  });
});
