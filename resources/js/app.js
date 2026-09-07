import './bootstrap';

const setAuthBookMode = (book, mode) => {
    const register = mode === 'register';
    book.classList.toggle('auth-book--register', register);
    book.classList.toggle('auth-book--login', !register);
    const intro = book.querySelector('[data-auth-intro]');
    if (intro) intro.innerHTML = register
        ? '<span class="auth-book-kicker">Không gian học tập của bạn</span><h1>Bắt đầu một hành trình mới.</h1><p>Tạo tài khoản để lưu khóa học, theo dõi tiến độ và học tập theo cách của riêng bạn.</p>'
        : '<span class="auth-book-kicker">Không gian học tập của bạn</span><h1>Chào mừng bạn trở lại.</h1><p>Tiếp tục những bài học còn dang dở và tiến gần hơn đến mục tiêu của bạn.</p>';
};

document.addEventListener('click', (event) => {
    const passwordToggle = event.target.closest('[data-password-toggle]');
    if (passwordToggle) {
        const input = document.getElementById(passwordToggle.dataset.passwordToggle);
        if (!input) return;
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        passwordToggle.setAttribute('aria-pressed', String(show));
        passwordToggle.setAttribute('aria-label', show ? 'Ẩn mật khẩu' : 'Hiện mật khẩu');
        passwordToggle.classList.toggle('is-visible', show);
        input.focus();
        return;
    }

    const googleLink = event.target.closest('[data-google-auth]');
    const currentBook = document.querySelector('[data-auth-book]');
    if (googleLink && currentBook?.classList.contains('auth-book--register')) {
        const terms = currentBook.querySelector('.auth-book-register-page [data-register-terms]') || currentBook.querySelector('.auth-panel--register [data-register-terms]');
        if (!terms?.checked) {
            event.preventDefault();
            terms?.focus();
            terms?.setCustomValidity('Vui lòng đồng ý với điều khoản trước khi đăng ký bằng Google.');
            terms?.reportValidity();
            terms?.addEventListener('change', () => terms.setCustomValidity(''), { once: true });
            return;
        }
        googleLink.href = `${googleLink.href}?accept_terms=1`;
    }

    const link = event.target.closest('[data-book-switch]');
    const book = document.querySelector('[data-auth-book]');
    if (!link || !book) return;
    event.preventDefault();
    const mode = link.dataset.bookSwitch;
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches || window.innerWidth <= 760;
    if (!reduceMotion) book.classList.add('is-turning', `is-turning-to-${mode}`);
    setAuthBookMode(book, mode);
    history.pushState({authMode: mode}, '', link.href);
    window.setTimeout(() => book.querySelector(mode === 'register' ? '#register-name' : '#login-email')?.focus(), reduceMotion ? 0 : 900);
    window.setTimeout(() => book.classList.remove('is-turning', 'is-turning-to-login', 'is-turning-to-register'), reduceMotion ? 0 : 920);
});

window.addEventListener('popstate', () => {
    const book = document.querySelector('[data-auth-book]');
    if (book) setAuthBookMode(book, location.pathname.endsWith('/register') ? 'register' : 'login');
});

document.addEventListener('submit', (event) => {
    const form = event.target.closest('[data-auth-form]');
    if (!form || !form.checkValidity()) return;
    const button = form.querySelector('[data-auth-submit]');
    if (!button || button.disabled) return;
    button.disabled = true;
    button.classList.add('is-loading');
    const label = button.querySelector('.auth-submit-label');
    if (label) label.textContent = button.dataset.loadingText || 'Đang xử lý…';
});

const updateCapsLockWarning = (event) => {
    const input = event.target.closest('[data-password-input]');
    if (!input) return;
    const warning = input.closest('.auth-field')?.querySelector('[data-caps-warning]');
    if (warning) warning.hidden = !event.getModifierState?.('CapsLock');
};

document.addEventListener('keydown', updateCapsLockWarning);
document.addEventListener('keyup', updateCapsLockWarning);
document.addEventListener('focusout', (event) => {
    if (!event.target.matches('[data-password-input]')) return;
    const warning = event.target.closest('.auth-field')?.querySelector('[data-caps-warning]');
    if (warning) warning.hidden = true;
});

const updatePasswordStrength = (input) => {
    if (input.id !== 'register-password') return;
    const strength = input.closest('form')?.querySelector('[data-password-strength]');
    if (!strength) return;

    const value = input.value;
    let score = 0;
    if (value.length >= 8) score++;
    if (value.length >= 12) score++;
    if (/[a-z]/.test(value) && /[A-Z]/.test(value)) score++;
    if (/\d/.test(value)) score++;
    if (/[^A-Za-z0-9]/.test(value)) score++;

    const level = value.length === 0 ? ['empty', 'Độ mạnh mật khẩu']
        : score <= 1 ? ['weak', 'Yếu']
            : score <= 3 ? ['medium', 'Trung bình'] : ['strong', 'Mạnh'];
    strength.dataset.level = level[0];
    strength.querySelector('[data-password-strength-label]').textContent = level[1];
};

document.addEventListener('input', (event) => {
    const input = event.target.closest('[data-password-input]');
    if (input) updatePasswordStrength(input);
});

document.addEventListener('invalid', (event) => {
    const field = event.target.closest('.auth-field');
    if (!field) return;
    field.classList.remove('auth-field--shake');
    requestAnimationFrame(() => field.classList.add('auth-field--shake'));
    window.setTimeout(() => field.classList.remove('auth-field--shake'), 360);
}, true);

document.querySelectorAll('#register-password').forEach(updatePasswordStrength);

const dismissAuthToast = (toast) => {
    if (!toast || toast.classList.contains('is-leaving')) return;
    toast.classList.add('is-leaving');
    window.setTimeout(() => toast.remove(), 240);
};

document.addEventListener('click', (event) => {
    const close = event.target.closest('[data-auth-toast-close]');
    if (close) dismissAuthToast(close.closest('[data-auth-toast]'));
});

document.querySelectorAll('[data-auth-toast]').forEach((toast) => {
    window.setTimeout(() => dismissAuthToast(toast), 5500);
});
