// Workshop OS - Main Application JS

// Initialize tooltips and popovers
document.addEventListener('DOMContentLoaded', function() {
  // Bootstrap tooltips
  const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
  tooltips.forEach(el => new bootstrap.Tooltip(el));

  // Bootstrap popovers
  const popovers = document.querySelectorAll('[data-bs-toggle="popover"]');
  popovers.forEach(el => new bootstrap.Popover(el));

  // Auto-hide alerts after 5s
  document.querySelectorAll('.alert-auto-dismiss').forEach(alert => {
    setTimeout(() => {
      const bsAlert = new bootstrap.Alert(alert);
      bsAlert.close();
    }, 5000);
  });

  // Sidebar toggle (mobile)
  const sidebarToggle = document.getElementById('sidebarToggle');
  const sidebar = document.getElementById('sidebar');
  if (sidebarToggle && sidebar) {
    sidebarToggle.addEventListener('click', () => sidebar.classList.toggle('show'));
  }

  // Confirm delete dialogs
  document.querySelectorAll('[data-confirm]').forEach(el => {
    el.addEventListener('click', function(e) {
      const msg = this.dataset.confirm || 'Are you sure?';
      if (!confirm(msg)) e.preventDefault();
    });
  });

  // Fix table-responsive clipping dropdown menus by using Popper fixed strategy
  document.querySelectorAll('.table-responsive [data-bs-toggle="dropdown"]').forEach(function(btn) {
    try {
      new bootstrap.Dropdown(btn, {
        popperConfig: function(defaultBsPopperConfig) {
          return Object.assign({}, defaultBsPopperConfig, {
            strategy: 'fixed'
          });
        }
      });
    } catch (err) {}
  });
});

// Toast notifications
function showToast(message, type = 'success') {
  const container = document.getElementById('toastContainer') || createToastContainer();
  const id = 'toast-' + Date.now();
  const icons = { success: 'check-circle-fill', error: 'exclamation-triangle-fill', warning: 'exclamation-circle-fill', info: 'info-circle-fill' };
  const colors = { success: 'text-success', error: 'text-danger', warning: 'text-warning', info: 'text-info' };
  const html = `
    <div id="${id}" class="toast align-items-center border-0" role="alert">
      <div class="d-flex">
        <div class="toast-body d-flex align-items-center gap-2">
          <i class="bi bi-${icons[type] || 'info-circle'} ${colors[type] || ''}"></i>
          ${escapeHtml(message)}
        </div>
        <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
      </div>
    </div>`;
  container.insertAdjacentHTML('beforeend', html);
  const toastEl = document.getElementById(id);
  const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
  toast.show();
  toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
}

function createToastContainer() {
  const c = document.createElement('div');
  c.id = 'toastContainer';
  c.className = 'toast-container position-fixed top-0 end-0 p-3';
  c.style.zIndex = '9999';
  document.body.appendChild(c);
  return c;
}

function escapeHtml(text) {
  const div = document.createElement('div');
  div.textContent = text;
  return div.innerHTML;
}

// CSRF token for AJAX requests
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

// AJAX helper
async function apiRequest(url, method = 'GET', data = null) {
  const opts = {
    method,
    headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-Token': csrfToken }
  };
  if (data) {
    if (data instanceof FormData) {
      opts.body = data;
    } else {
      opts.headers['Content-Type'] = 'application/json';
      opts.body = JSON.stringify(data);
    }
  }
  const resp = await fetch(url, opts);
  return resp.json();
}
