document.addEventListener('DOMContentLoaded', () => {
    const menuButton = document.querySelector('[data-menu-toggle]');
    const navigation = document.querySelector('[data-main-menu]');

    menuButton?.addEventListener('click', () => {
        const isOpen = navigation?.classList.toggle('open') ?? false;
        menuButton.setAttribute('aria-expanded', String(isOpen));
    });

    const notificationCenter = document.querySelector('[data-notification-center]');
    const notificationToggle = notificationCenter?.querySelector('[data-notification-toggle]');
    const notificationDropdown = notificationCenter?.querySelector('[data-notification-dropdown]');
    const closeNotifications = () => {
        if (!notificationDropdown || !notificationToggle) return;
        notificationDropdown.hidden = true;
        notificationToggle.setAttribute('aria-expanded', 'false');
    };
    notificationToggle?.addEventListener('click', () => {
        const willOpen = notificationDropdown?.hidden ?? false;
        if (notificationDropdown) notificationDropdown.hidden = !willOpen;
        notificationToggle.setAttribute('aria-expanded', String(willOpen));
    });
    document.addEventListener('click', (event) => {
        if (notificationCenter && !notificationCenter.contains(event.target)) closeNotifications();
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') closeNotifications();
    });

    document.querySelectorAll('[data-course-image]').forEach((image) => {
        const useFallback = () => image.classList.add('is-broken');
        if (image.complete && image.naturalWidth === 0) useFallback();
        image.addEventListener('error', useFallback, { once: true });
    });

    if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches && 'IntersectionObserver' in window) {
        const revealItems = document.querySelectorAll('.hero-copy, .hero-art, .section-heading-row, .course-card, .benefit-grid article, .teacher-art, .teacher-copy');
        revealItems.forEach((item) => item.classList.add('site-reveal'));
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                revealObserver.unobserve(entry.target);
            });
        }, { threshold: .12, rootMargin: '0px 0px -35px' });
        revealItems.forEach((item) => revealObserver.observe(item));
    }

    document.querySelector('[data-catalog-form]')?.addEventListener('submit', () => {
        document.querySelector('[data-catalog-results]')?.classList.add('is-loading');
        document.querySelector('[data-catalog-loading]')?.classList.add('is-visible');
    });

    const certificateLightbox = document.querySelector('[data-certificate-lightbox]');
    const certificatePreview = certificateLightbox?.querySelector('[data-certificate-preview]');
    const closeCertificate = () => {
        if (!certificateLightbox) return;
        certificateLightbox.hidden = true;
        document.body.classList.remove('certificate-open');
        if (certificatePreview) certificatePreview.src = '';
    };
    document.querySelectorAll('[data-certificate-image]').forEach((button) => {
        button.addEventListener('click', () => {
            if (!certificateLightbox || !certificatePreview) return;
            certificatePreview.src = button.dataset.certificateImage;
            certificateLightbox.hidden = false;
            document.body.classList.add('certificate-open');
        });
    });
    certificateLightbox?.addEventListener('click', (event) => {
        if (event.target === certificateLightbox || event.target.closest('[data-certificate-close]')) closeCertificate();
    });

    document.querySelectorAll('[data-certificate-carousel]').forEach((carousel) => {
        const slides = [...carousel.querySelectorAll('[data-certificate-slide]')];
        const dots = [...carousel.querySelectorAll('[data-certificate-dot]')];
        let activeIndex = 0;

        const renderCertificateCarousel = () => {
            slides.forEach((slide, index) => {
                slide.classList.remove('is-active', 'is-prev', 'is-next');
                if (index === activeIndex) slide.classList.add('is-active');
                else if (index === (activeIndex - 1 + slides.length) % slides.length) slide.classList.add('is-prev');
                else if (index === (activeIndex + 1) % slides.length) slide.classList.add('is-next');
                slide.setAttribute('aria-hidden', String(index !== activeIndex));
            });
            dots.forEach((dot, index) => dot.classList.toggle('is-active', index === activeIndex));
            carousel.classList.add('is-ready');
        };
        const moveCertificate = (step) => {
            if (!slides.length) return;
            activeIndex = (activeIndex + step + slides.length) % slides.length;
            renderCertificateCarousel();
        };

        carousel.querySelector('[data-certificate-prev]')?.addEventListener('click', () => moveCertificate(-1));
        carousel.querySelector('[data-certificate-next]')?.addEventListener('click', () => moveCertificate(1));
        dots.forEach((dot, index) => dot.addEventListener('click', () => {
            activeIndex = index;
            renderCertificateCarousel();
        }));
        carousel.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowLeft') moveCertificate(-1);
            if (event.key === 'ArrowRight') moveCertificate(1);
        });
        renderCertificateCarousel();
    });

    document.querySelectorAll('[data-accordion-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const section = button.closest('.public-section');
            const lessons = section?.querySelector('.public-lessons');
            const isOpen = button.getAttribute('aria-expanded') !== 'true';

            button.setAttribute('aria-expanded', String(isOpen));
            if (lessons) lessons.hidden = !isOpen;
        });
    });

    const storageKey = 'khoahoc_cart';
    const drawer = document.querySelector('[data-cart-drawer]');
    const overlay = document.querySelector('[data-cart-backdrop]');
    const cartItems = document.querySelector('[data-cart-items]');
    const cartTotal = document.querySelector('[data-cart-total]');
    const cartEmpty = document.querySelector('[data-cart-empty]');
    const cartSummary = document.querySelector('[data-cart-summary]');
    const cartCheckoutButton = document.querySelector('[data-cart-checkout]');
    const cartFeedback = document.querySelector('[data-cart-feedback]');

    const readCart = () => {
        try {
            const value = JSON.parse(localStorage.getItem(storageKey) || '[]');
            return Array.isArray(value) ? value : [];
        } catch {
            return [];
        }
    };

    const saveCart = (cart) => localStorage.setItem(storageKey, JSON.stringify(cart));
    const money = (value) => new Intl.NumberFormat('vi-VN').format(Number(value) || 0) + 'đ';

    const closeCart = () => {
        document.body.classList.remove('cart-open');
    };

    const openCart = () => {
        document.body.classList.add('cart-open');
    };

    const renderCart = () => {
        const cart = readCart();
        document.querySelectorAll('[data-cart-count]').forEach((element) => {
            element.textContent = String(cart.length);
        });

        if (cartItems) {
            cartItems.innerHTML = cart.length
                ? cart.map((item) => `
                    <article class="cart-item">
                        <div>
                            <span>Khóa học</span>
                            <strong>${escapeHtml(item.name)}</strong>
                            <small>${money(item.price)}</small>
                        </div>
                        <button type="button" data-remove-course="${item.id}" aria-label="Xóa ${escapeHtml(item.name)}">×</button>
                    </article>
                `).join('')
                : '';
        }

        if (cartEmpty) cartEmpty.hidden = cart.length > 0;
        if (cartSummary) cartSummary.hidden = cart.length === 0;

        if (cartTotal) {
            cartTotal.textContent = money(cart.reduce((total, item) => total + Number(item.price || 0), 0));
        }
    };

    function escapeHtml(value) {
        const element = document.createElement('div');
        element.textContent = String(value ?? '');
        return element.innerHTML;
    }

    document.querySelectorAll('[data-cart-open]').forEach((button) => button.addEventListener('click', openCart));
    document.querySelectorAll('[data-cart-close]').forEach((button) => button.addEventListener('click', closeCart));
    overlay?.addEventListener('click', closeCart);

    document.querySelectorAll('[data-add-cart]').forEach((button) => {
        button.addEventListener('click', () => {
            const cart = readCart();
            const course = {
                id: String(button.dataset.courseId),
                name: button.dataset.courseName || 'Khóa học',
                price: Number(button.dataset.coursePrice || 0),
            };

            if (!cart.some((item) => String(item.id) === course.id)) {
                cart.push(course);
                saveCart(cart);
            }

            renderCart();
            openCart();
        });
    });

    cartItems?.addEventListener('click', (event) => {
        const button = event.target.closest('[data-remove-course]');
        if (!button) return;

        const cart = readCart().filter((item) => String(item.id) !== button.dataset.removeCourse);
        saveCart(cart);
        renderCart();
    });

    cartCheckoutButton?.addEventListener('click', () => {
        const cart = readCart();
        if (!cart.length) return;
        const url = new URL(cartCheckoutButton.dataset.checkoutUrl, window.location.origin);
        cart.forEach((item) => url.searchParams.append('course_ids[]', item.id));
        window.location.assign(url.toString());
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeCart();
            closeCertificate();
        }
    });

    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        button.addEventListener('click', () => {
            const input = document.getElementById(button.dataset.passwordToggle);
            if (!input) return;

            const isHidden = input.type === 'password';
            input.type = isHidden ? 'text' : 'password';
            button.textContent = isHidden ? '🙈' : '👁';
            button.setAttribute('aria-label', isHidden ? 'Ẩn mật khẩu' : 'Hiện mật khẩu');
        });
    });

    renderCart();
});
