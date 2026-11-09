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

// Sections settle into place as they scroll into view (styles in _motion.scss).
// Runs only when the head script switched motion on; anything it can't reach
// stays visible because the hiding rules only apply under html.has-motion.
(() => {
  const root = document.documentElement;
  if (!root.classList.contains('has-motion')) {
    return;
  }
  window.frameworkReveal = true; // tells the head script's fallback we've taken over

  // Keep in step with $reveal-targets in _motion.scss.
  const REVEAL_TARGETS =
    '.c-hero .row > *, .c-section__head, .c-pricing__intro, .c-pricing__note, .c-split .row > *, .c-feature-grid .row > *, .c-feature-list .row > *, .c-pricing .row > *, .c-steps ol > li, .c-faq .col-lg-8 > *, .c-text .c-content__inner, .c-cta .container > *';
  const STEP = 70; // ms between siblings that arrive together
  const MAX_DELAY = 280;

  const targets = document.querySelectorAll(REVEAL_TARGETS);

  const reveal = (el, delay) => {
    el.style.setProperty('--reveal-delay', `${delay}ms`);
    el.classList.add('is-revealed');
    // Clear the delay afterwards so nothing later inherits it.
    setTimeout(() => el.style.removeProperty('--reveal-delay'), delay + 700);
  };

  const observer = new IntersectionObserver(
    (entries) => {
      // Siblings arriving in the same batch ripple in, in page order.
      const counts = new Map();
      entries
        .filter((entry) => entry.isIntersecting)
        .map((entry) => entry.target)
        .sort((a, b) => (a.compareDocumentPosition(b) & Node.DOCUMENT_POSITION_FOLLOWING ? -1 : 1))
        .forEach((el) => {
          const n = counts.get(el.parentNode) || 0;
          counts.set(el.parentNode, n + 1);
          reveal(el, Math.min(n * STEP, MAX_DELAY));
          observer.unobserve(el);
        });
    },
    { rootMargin: '0px 0px -8% 0px' }
  );

  targets.forEach((el) => observer.observe(el));

  // Printing, or jumping around with Find, should never meet hidden content.
  const showAll = () => targets.forEach((el) => el.classList.add('is-revealed'));
  window.addEventListener('beforeprint', showAll);
})();
