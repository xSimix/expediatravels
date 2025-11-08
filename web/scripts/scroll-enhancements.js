(function () {
    const onReady = (callback) => {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback, { once: true });
        } else {
            callback();
        }
    };

    onReady(() => {
        const scrollButton = document.querySelector('[data-scroll-top]');
        if (!scrollButton) {
            return;
        }

        const root = document.documentElement;
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        const toggleVisibility = () => {
            const scrollableHeight = root.scrollHeight - window.innerHeight;

            if (scrollableHeight <= 0) {
                scrollButton.classList.remove('is-visible');
                scrollButton.setAttribute('hidden', 'hidden');
                return;
            }

            const threshold = scrollableHeight / 2;
            const currentScroll = window.scrollY || root.scrollTop;

            if (currentScroll >= threshold) {
                scrollButton.classList.add('is-visible');
                scrollButton.removeAttribute('hidden');
            } else {
                scrollButton.classList.remove('is-visible');
                scrollButton.setAttribute('hidden', 'hidden');
            }
        };

        toggleVisibility();
        document.addEventListener('scroll', toggleVisibility, { passive: true });
        window.addEventListener('resize', toggleVisibility);

        scrollButton.addEventListener('click', () => {
            const behavior = prefersReducedMotion ? 'auto' : 'smooth';
            root.scrollTo({ top: 0, behavior });
        });
    });
})();
