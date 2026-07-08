function setupExportDropdown() {
  const exportBtn = document.getElementById('exportBtn');
  const exportMenu = document.getElementById('exportMenu');
  if (!exportBtn || !exportMenu) return;

  exportBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    const open = exportMenu.classList.toggle('show');
    exportBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
  });

  document.addEventListener('click', () => {
    exportMenu.classList.remove('show');
    exportBtn.setAttribute('aria-expanded', 'false');
  });
}

function setupFilterEnterSubmit() {
  const filterForm = document.getElementById('filterForm');
  filterForm?.querySelectorAll('input,select').forEach(el => {
    el.addEventListener('keydown', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        filterForm.submit();
      }
    });
  });
}

document.addEventListener('DOMContentLoaded', function () {

  const overlay      = document.getElementById('promoOverlay');
  const modalAdd      = document.getElementById('modalAdd');
  const modalEdit      = document.getElementById('modalEdit');
  const modalView      = document.getElementById('modalView');
  const modalDelete     = document.getElementById('modalDelete');

  const toast      = document.getElementById('toast');
  const toastText     = document.getElementById('toastText');

  const formEdit      = document.getElementById('formEdit');
  const formDelete     = document.getElementById('formDelete');

  const serverToast = document.getElementById('serverToast');
  if (serverToast) {
    window.setTimeout(() => serverToast.classList.remove('show'), 2200);
  }

  // GUARD: kalau markup modal (#promoOverlay dkk) belum ter-render —
  // biasanya karena @stack('modals') belum ada di layouts/admin.blade.php —
  // hentikan HANYA bagian modal di sini, tapi JANGAN biarkan seluruh script
  // ini crash sehingga tombol lain (Export dropdown, dll) tetap berfungsi.
  if (!overlay || !modalAdd || !modalEdit || !modalView || !modalDelete) {
    console.error(
      "[promotion.js] Elemen modal (#promoOverlay/#modalAdd/#modalEdit/#modalView/#modalDelete) tidak ditemukan di DOM. " +
      "Pastikan @stack('modals') sudah ditambahkan tepat sebelum </body> di layouts/admin.blade.php."
    );
    setupExportDropdown();
    setupFilterEnterSubmit();
    return;
  }

  function showToast(msg) {
    toastText.textContent = msg;
    toast.classList.add('show');
    window.clearTimeout(showToast._t);
    showToast._t = window.setTimeout(() => toast.classList.remove('show'), 1800);
  }

  function openOverlay(modal) {
    overlay.classList.add('show');
    overlay.setAttribute('aria-hidden', 'false');
    [modalAdd, modalEdit, modalView, modalDelete].forEach(m => m.classList.remove('active'));
    modal.classList.add('active');
    const focusable = modal.querySelector('input,select,textarea,button');
    setTimeout(() => focusable?.focus?.(), 30);
  }

  function closeOverlay() {
    overlay.classList.remove('show');
    overlay.setAttribute('aria-hidden', 'true');
  }

  overlay.addEventListener('click', (e) => {
    if (e.target === overlay) closeOverlay();
    if (e.target.closest('[data-close="true"]')) closeOverlay();
  });

  window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && overlay.classList.contains('show')) closeOverlay();
  });

  function addRoomTag(container, hiddenContainer, id, label, fieldName) {
    if (!id) return;
    const existing = Array.from(container.querySelectorAll('.tag')).some(t => t.dataset.value === String(id));
    if (existing) return;

    const el = document.createElement('span');
    el.className = 'tag';
    el.dataset.value = id;
    el.innerHTML = `<span>${label}</span><button type="button" aria-label="Remove ${label}"><i class="bi bi-x"></i></button>`;

    const hidden = document.createElement('input');
    hidden.type = 'hidden';
    hidden.name = fieldName;
    hidden.value = id;

    el.querySelector('button').addEventListener('click', () => {
      el.remove();
      hidden.remove();
    });

    container.appendChild(el);
    hiddenContainer.appendChild(hidden);
  }

  function setRoomTags(container, hiddenContainer, ids, names, fieldName) {
    container.innerHTML = '';
    hiddenContainer.innerHTML = '';
    ids.forEach((id, i) => id && addRoomTag(container, hiddenContainer, id, names[i] || id, fieldName));
  }

  document.getElementById('roomTypeAdd').addEventListener('change', (e) => {
    const opt = e.target.selectedOptions[0];
    if (!opt || !opt.value) return;
    addRoomTag(
      document.getElementById('roomTagsAdd'),
      document.getElementById('roomHiddenAdd'),
      opt.value,
      opt.text,
      'rooms[]'
    );
    e.target.value = '';
  });

  document.getElementById('roomTypeEdit').addEventListener('change', (e) => {
    const opt = e.target.selectedOptions[0];
    if (!opt || !opt.value) return;
    addRoomTag(
      document.getElementById('roomTagsEdit'),
      document.getElementById('roomHiddenEdit'),
      opt.value,
      opt.text,
      'rooms[]'
    );
    e.target.value = '';
  });

  const dropzoneAdd = document.getElementById('dropzoneAdd');
  const bannerAdd  = document.getElementById('bannerAdd');

  // ============================================================
  // FIX BANNER TIDAK TERSIMPAN:
  // Sebelumnya previewBanner() untuk dropzone melakukan
  // `dropzoneAdd.innerHTML = '<img ...>'`. Karena <input
  // id="bannerAdd" name="banner"> adalah ANAK dari #dropzoneAdd,
  // baris innerHTML itu ikut MENGHAPUS elemen <input> tsb dari DOM.
  // Akibatnya saat form Add di-submit, field "banner" tidak pernah
  // terkirim ke server -> kolom banner selalu NULL di database,
  // meskipun controller (store/update) sudah benar menangani
  // $request->hasFile('banner').
  //
  // Fix: JANGAN overwrite innerHTML dropzone. Preview cukup pakai
  // CSS background-image di elemen dropzone itu sendiri, dan
  // sembunyikan teks placeholder lewat class "has-preview"
  // (tambahkan rule CSS-nya di promotion.css, lihat catatan di
  // bawah). Dengan begitu <input name="banner"> tetap utuh di DOM.
  // ============================================================
  function previewBanner(file, imgTargetOrDropzone, isDropzone) {
    if (file.size > 2 * 1024 * 1024) {
      showToast('Image too large (max 2MB)');
      return;
    }
    const reader = new FileReader();
    reader.onload = () => {
      if (isDropzone) {
        imgTargetOrDropzone.style.backgroundImage = `url(${reader.result})`;
        imgTargetOrDropzone.style.backgroundSize = 'cover';
        imgTargetOrDropzone.style.backgroundPosition = 'center';
        imgTargetOrDropzone.classList.add('has-preview');
        // TIDAK ADA innerHTML = ... di sini lagi — input file tetap aman.
      } else {
        imgTargetOrDropzone.src = reader.result;
      }
    };
    reader.readAsDataURL(file);
  }

  dropzoneAdd.addEventListener('click', () => bannerAdd.click());
  dropzoneAdd.addEventListener('dragover', (e) => { e.preventDefault(); dropzoneAdd.style.borderColor = '#A9C1FF'; });
  dropzoneAdd.addEventListener('dragleave', () => { dropzoneAdd.style.borderColor = ''; });
  dropzoneAdd.addEventListener('drop', (e) => {
    e.preventDefault();
    dropzoneAdd.style.borderColor = '';
    const file = e.dataTransfer.files?.[0];
    if (file) {
      bannerAdd.files = e.dataTransfer.files;
      previewBanner(file, dropzoneAdd, true);
    }
  });
  bannerAdd.addEventListener('change', () => {
    const file = bannerAdd.files?.[0];
    if (file) previewBanner(file, dropzoneAdd, true);
  });

  document.getElementById('changeImgBtn').addEventListener('click', () => {
    document.getElementById('bannerEdit').click();
  });
  document.getElementById('bannerEdit').addEventListener('change', (e) => {
    const file = e.target.files?.[0];
    if (file) previewBanner(file, document.getElementById('editThumb'), false);
  });

  document.getElementById('addBtn').addEventListener('click', () => openOverlay(modalAdd));
  document.getElementById('createFromEmpty')?.addEventListener('click', () => openOverlay(modalAdd));

  document.getElementById('tbody').addEventListener('click', (e) => {
    const btn = e.target.closest('[data-action]');
    if (!btn) return;
    const action = btn.dataset.action;

    if (action === 'view') openView(btn);
    if (action === 'edit') openEdit(btn);
    if (action === 'delete') openDelete(btn);
  });

  function chipHtml(status) {
    const cls = status === 'Active' ? 'success'
      : status === 'Upcoming' ? 'warn'
      : status === 'Expired' ? 'danger' : 'inactive';
    return `<span class="chip ${cls}">${status}</span>`;
  }

  function openView(btn) {
    const d = btn.dataset;
    document.getElementById('viewThumb').src = d.banner || '';
    document.getElementById('vName').textContent = d.name || '—';
    document.getElementById('vCode').textContent = d.code || '—';
    document.getElementById('vType').textContent = d.type || '—';
    document.getElementById('vDiscount').textContent = d.discount || '—';
    document.getElementById('vMin').textContent = d.min || '—';
    document.getElementById('vMax').textContent = d.max || '—';
    document.getElementById('vRooms').textContent = d.rooms ? d.rooms.split(',').join(', ') : '—';
    document.getElementById('vLimit').textContent = (d.limit === '0' || !d.limit ? 'Unlimited' : d.limit);
    document.getElementById('vPeriod').textContent = d.period || '—';
    document.getElementById('vStatus').innerHTML = chipHtml(d.status || 'Inactive');
    document.getElementById('vDesc').textContent = d.desc || '—';
    openOverlay(modalView);
  }

  function openEdit(btn) {
    const d = btn.dataset;

    formEdit.action = d.actionUrl || formEdit.action;

    document.getElementById('editThumb').src = d.banner || '';
    document.getElementById('nameEdit').value = d.name || '';
    document.getElementById('codeEdit').value = d.code || '';
    document.getElementById('typeEdit').value = d.type || '';
    document.getElementById('discountEdit').value = d.discount || '';
    document.getElementById('minEdit').value = d.min || '';
    document.getElementById('maxEdit').value = d.max || '';
    document.getElementById('limitEdit').value = d.limit || 0;
    document.getElementById('startEdit').value = d.start || '';
    document.getElementById('endEdit').value = d.end || '';
    document.getElementById('descEdit').value = d.desc || '';

    const roomIds = d.roomIds ? d.roomIds.split(',') : [];
    const roomNames = d.rooms ? d.rooms.split(',') : [];
    setRoomTags(
      document.getElementById('roomTagsEdit'),
      document.getElementById('roomHiddenEdit'),
      roomIds,
      roomNames,
      'rooms[]'
    );

    if (d.status === 'Inactive') {
      document.getElementById('statusEditInactive').checked = true;
    } else {
      document.getElementById('statusEditActive').checked = true;
    }

    openOverlay(modalEdit);
  }

  function openDelete(btn) {
    const d = btn.dataset;
    formDelete.action = d.actionUrl || formDelete.action;
    document.getElementById('delName').textContent = d.name || '—';
    openOverlay(modalDelete);
  }

  setupExportDropdown();
  setupFilterEnterSubmit();
  if (window.reopenAddModalOnLoad) {
    openOverlay(modalAdd);
    showToast('Gagal menyimpan, cek kembali field yang ditandai merah.');
  }

});
