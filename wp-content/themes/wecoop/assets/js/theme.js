document.addEventListener('DOMContentLoaded', function () {
    // ── Mobile menu toggle ────────────────────────────────────────────────
    var toggle = document.querySelector('.ws-menu-toggle');
    if (toggle) {
        toggle.addEventListener('click', function () {
            var nav = document.querySelector('.ws-nav .ws-links') || document.querySelector('.ws-header .ws-links');
            if (!nav) return;
            var isOpen = nav.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    }

    // ── Language modal ────────────────────────────────────────────────────
    var triggers = document.querySelectorAll('#ws-lang-trigger');
    var modal    = document.getElementById('ws-lang-modal');
    var backdrop = document.getElementById('ws-lang-modal-backdrop');
    var closeBtn = document.getElementById('ws-lang-modal-close');

    if (!modal) return;

    function openModal() {
        modal.removeAttribute('hidden');
        document.body.style.overflow = 'hidden';
        triggers.forEach(function (t) { t.classList.add('is-open'); });
        closeBtn.focus();
    }

    function closeModal() {
        modal.setAttribute('hidden', '');
        document.body.style.overflow = '';
        triggers.forEach(function (t) { t.classList.remove('is-open'); });
    }

    triggers.forEach(function (trigger) {
        trigger.addEventListener('click', function () {
            if (modal.hasAttribute('hidden')) {
                openModal();
            } else {
                closeModal();
            }
        });
    });

    backdrop.addEventListener('click', closeModal);
    closeBtn.addEventListener('click', closeModal);

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !modal.hasAttribute('hidden')) {
            closeModal();
        }
    });

    // ── Scroll to Top Button ──────────────────────────────────────────────
    var scrollToTopBtn = document.getElementById('ws-scroll-to-top');
    if (scrollToTopBtn) {
        // Mostra/nascondi il bottone in base allo scroll
        window.addEventListener('scroll', function () {
            if (window.pageYOffset > 300) {
                scrollToTopBtn.classList.add('visible');
            } else {
                scrollToTopBtn.classList.remove('visible');
            }
        });

        // Scroll verso l'alto quando clicchi il bottone
        scrollToTopBtn.addEventListener('click', function () {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
});
