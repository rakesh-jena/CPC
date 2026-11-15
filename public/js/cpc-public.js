document.addEventListener('DOMContentLoaded', function () {
    var toggle = document.querySelector('[data-nav-toggle]');
    var nav = document.querySelector('[data-nav]');
    if (toggle && nav) {
        toggle.addEventListener('click', function () {
            var open = nav.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    }

    // Keep filter controls usable without JavaScript; this only improves keyboard interaction.
    var filterForm = document.getElementById('filterForm');
    if (filterForm) {
        filterForm.querySelectorAll('select').forEach(function (select) {
            select.addEventListener('change', function () {
                // Intentionally do not auto-submit each select: researchers can combine filters first.
            });
        });
    }

    // Smooth-scroll to profile sections and account for the sticky section navigation.
    document.querySelectorAll('.profile-tabs a[href^="#"]').forEach(function (link) {
        link.addEventListener('click', function (event) {
            var target = document.querySelector(link.getAttribute('href'));
            if (!target) return;
            event.preventDefault();
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
            history.replaceState(null, '', link.getAttribute('href'));
        });
    });
});
