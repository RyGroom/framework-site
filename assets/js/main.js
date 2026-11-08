// Framework theme — small progressive enhancements only.

// The sticky header gets its divider once the page has scrolled.
(() => {
  const header = document.querySelector('[data-header]');
  if (!header) {
    return;
  }
  const update = () => header.classList.toggle('is-scrolled', window.scrollY > 8);
  update();
  window.addEventListener('scroll', update, { passive: true });
})();
