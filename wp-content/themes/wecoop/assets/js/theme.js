document.addEventListener('DOMContentLoaded', function () {
    var toggle = document.querySelector('.ws-menu-toggle');
    var nav = document.querySelector('.ws-header .ws-links');

    if (toggle && nav) {
        toggle.addEventListener('click', function () {
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
});
