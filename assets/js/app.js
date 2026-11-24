document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('[data-confirm]').forEach((item) => {
    item.addEventListener('click', (event) => {
      if (!window.confirm(item.dataset.confirm || 'Continue?')) event.preventDefault();
    });
  });
});
