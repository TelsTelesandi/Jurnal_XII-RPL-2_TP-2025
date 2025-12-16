// Global JS for Purchase Order System
document.addEventListener('DOMContentLoaded', () => {
  // Simple helper: auto-dismiss alerts after a few seconds
  const alerts = document.querySelectorAll('.alert.auto-dismiss');
  alerts.forEach((el) => {
    setTimeout(() => {
      el.classList.add('fade');
      el.addEventListener('transitionend', () => el.remove());
    }, 4000);
  });

  // Enable Bootstrap tooltips if present
  if (window.bootstrap && typeof bootstrap.Tooltip === 'function') {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => {
      new bootstrap.Tooltip(el);
    });
  }
});