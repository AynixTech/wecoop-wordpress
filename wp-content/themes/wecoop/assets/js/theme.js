document.addEventListener('DOMContentLoaded', function () {
    var toggle = document.querySelector('.wecoop-menu-toggle');
    var nav = document.querySelector('.wecoop-nav');

    if (!toggle || !nav) {
        return;
    }

    toggle.addEventListener('click', function () {
        var isOpen = nav.classList.toggle('is-open');
        toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
    });
});
