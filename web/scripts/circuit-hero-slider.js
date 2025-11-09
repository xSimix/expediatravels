(() => {
    const initialiseSlider = () => {
        const slider = document.querySelector('[data-slider="circuit-hero"]');
        if (!slider) {
            return;
        }

        const track = slider.querySelector('.circuit-hero__track');
        const dotsContainer = slider.querySelector('.circuit-hero__dots');
        if (!track) {
            return;
        }

        const slides = Array.from(track.children);
        if (slides.length === 0) {
            return;
        }

        const dataset = slider.dataset;
        const tabletQuery = window.matchMedia('(max-width: 1024px)');
        const mobileQuery = window.matchMedia('(max-width: 640px)');

        const getVisibleSlides = () => {
            if (mobileQuery.matches) {
                return Math.max(1, parseInt(dataset.visibleMobile || dataset.visibleTablet || dataset.visibleDesktop || '1', 10));
            }
            if (tabletQuery.matches) {
                return Math.max(1, parseInt(dataset.visibleTablet || dataset.visibleDesktop || '2', 10));
            }
            return Math.max(1, parseInt(dataset.visibleDesktop || '3', 10));
        };

        let visibleSlides = Math.min(slides.length, getVisibleSlides());
        let maxIndex = Math.max(0, slides.length - visibleSlides);
        let currentIndex = 0;

        const updateDots = () => {
            if (!dotsContainer) {
                return;
            }
            const dots = dotsContainer.querySelectorAll('[data-slide-to]');
            dots.forEach((dot) => {
                const dotIndex = Number(dot.getAttribute('data-slide-to'));
                dot.classList.toggle('is-active', dotIndex === currentIndex);
                dot.setAttribute('aria-pressed', dotIndex === currentIndex ? 'true' : 'false');
            });
        };

        const goTo = (position) => {
            currentIndex = Math.max(0, Math.min(maxIndex, position));
            const targetSlide = slides[currentIndex];
            if (targetSlide) {
                const offset = targetSlide.offsetLeft;
                track.style.transform = `translate3d(-${offset}px, 0, 0)`;
            }
            updateDots();
        };

        const buildDots = () => {
            if (!dotsContainer) {
                return;
            }
            dotsContainer.innerHTML = '';
            const totalDots = maxIndex + 1;
            for (let index = 0; index < totalDots; index += 1) {
                const button = document.createElement('button');
                button.type = 'button';
                button.className = 'circuit-hero__dot';
                button.setAttribute('data-slide-to', String(index));
                button.setAttribute('role', 'tab');
                button.setAttribute('aria-label', `Ir a la imagen ${index + 1}`);
                button.setAttribute('aria-pressed', 'false');
                button.addEventListener('click', () => {
                    goTo(index);
                });
                dotsContainer.append(button);
            }
        };

        const recalculate = () => {
            visibleSlides = Math.min(slides.length, getVisibleSlides());
            maxIndex = Math.max(0, slides.length - visibleSlides);
            currentIndex = Math.min(currentIndex, maxIndex);
            buildDots();
            goTo(currentIndex);
        };

        recalculate();

        let resizeScheduled = false;
        window.addEventListener('resize', () => {
            if (resizeScheduled) {
                return;
            }
            resizeScheduled = true;
            window.requestAnimationFrame(() => {
                resizeScheduled = false;
                recalculate();
            });
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialiseSlider, { once: true });
    } else {
        initialiseSlider();
    }
})();
