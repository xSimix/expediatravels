<script>
    document.addEventListener('DOMContentLoaded', () => {
        const header = document.querySelector('[data-site-header]');
        const toggle = document.querySelector('[data-menu-toggle]');
        const nav = document.querySelector('[data-site-nav]');

        if (header) {
            const updateHeaderState = () => {
                if (window.scrollY > 24) {
                    header.classList.add('site-header--scrolled');
                } else {
                    header.classList.remove('site-header--scrolled');
                }
            };

            updateHeaderState();
            window.addEventListener('scroll', updateHeaderState, { passive: true });
        }

        const closeUserMenu = () => {
            const container = document.querySelector('[data-user-menu-container]');
            const menu = document.querySelector('[data-user-menu]');
            const toggleButton = document.querySelector('[data-user-menu-toggle]');

            if (!menu || menu.hidden) {
                return;
            }

            menu.hidden = true;
            toggleButton?.setAttribute('aria-expanded', 'false');
            container?.classList.remove('site-header__user--open');
        };

        if (toggle && nav && header) {
            toggle.addEventListener('click', () => {
                const isOpen = header.classList.toggle('site-header--open');
                toggle.setAttribute('aria-expanded', String(isOpen));
                if (isOpen) {
                    closeUserMenu();
                }
            });

            nav.addEventListener('click', (event) => {
                if (event.target instanceof HTMLElement && event.target.classList.contains('site-header__link')) {
                    header.classList.remove('site-header--open');
                    toggle.setAttribute('aria-expanded', 'false');
                    closeUserMenu();
                }
            });
        }

        const userMenuContainer = document.querySelector('[data-user-menu-container]');
        const userMenuToggle = document.querySelector('[data-user-menu-toggle]');
        const userMenu = document.querySelector('[data-user-menu]');

        if (userMenuContainer && userMenuToggle && userMenu) {
            userMenuToggle.addEventListener('click', (event) => {
                event.stopPropagation();
                if (userMenu.hidden) {
                    userMenu.hidden = false;
                    userMenuToggle.setAttribute('aria-expanded', 'true');
                    userMenuContainer.classList.add('site-header__user--open');
                } else {
                    closeUserMenu();
                }
            });

            userMenu.addEventListener('click', (event) => {
                if (event.target instanceof HTMLElement && event.target.closest('[data-user-menu-close]')) {
                    closeUserMenu();
                }
            });

            document.addEventListener('click', (event) => {
                const target = event.target;
                if (!userMenu.hidden && userMenuContainer && target instanceof Node && !userMenuContainer.contains(target)) {
                    closeUserMenu();
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !userMenu.hidden) {
                    closeUserMenu();
                    userMenuToggle.focus();
                }
            });
        }
    });
</script>
