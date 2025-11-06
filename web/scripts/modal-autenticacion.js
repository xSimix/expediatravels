document.addEventListener('DOMContentLoaded', () => {
    const modal = document.querySelector('[data-auth-modal]');
    if (!modal) {
        return;
    }

    const views = new Map();
    modal.querySelectorAll('[data-auth-view]').forEach((view) => {
        const key = view.dataset.authView;
        if (key) {
            views.set(key, view);
        }
    });

    const messageEl = modal.querySelector('[data-auth-message]');
    const loginForm = modal.querySelector('#auth-login-form');
    const registerForm = modal.querySelector('#auth-register-form');
    const verifyForm = modal.querySelector('#auth-verify-form');
    const forgotForm = modal.querySelector('#auth-forgot-form');
    const resendButton = modal.querySelector('[data-auth-resend]');
    const googleButton = modal.querySelector('[data-auth-google]');
    const logoutButtons = document.querySelectorAll('[data-auth-logout]');
    let currentView = 'login';

    const setMessage = (text = '', type = null) => {
        if (!messageEl) {
            return;
        }

        messageEl.textContent = text;
        messageEl.classList.remove('auth-modal__message--error', 'auth-modal__message--success');

        if (type === 'error') {
            messageEl.classList.add('auth-modal__message--error');
        } else if (type === 'success') {
            messageEl.classList.add('auth-modal__message--success');
        }
    };

    const showView = (view, options = {}) => {
        if (!views.has(view)) {
            return;
        }

        views.forEach((element, key) => {
            if (key === view) {
                element.removeAttribute('hidden');
            } else {
                element.setAttribute('hidden', '');
            }
        });

        currentView = view;
        setMessage('');

        if (options.email) {
            const emailInput = views.get(view)?.querySelector('input[name="correo"]');
            if (emailInput) {
                emailInput.value = options.email;
            }
        }

        const firstField = views.get(view)?.querySelector('input, button, select, textarea');
        if (firstField) {
            window.setTimeout(() => {
                firstField.focus();
            }, 150);
        }
    };

    const openModal = (view = 'login', options = {}) => {
        showView(view, options);
        modal.removeAttribute('hidden');
        window.requestAnimationFrame(() => {
            modal.classList.add('auth-modal--visible');
        });
        document.body.style.overflow = 'hidden';
    };

    const closeModal = () => {
        modal.classList.remove('auth-modal--visible');
        window.setTimeout(() => {
            modal.setAttribute('hidden', '');
            document.body.style.overflow = '';
            setMessage('');
        }, 220);
    };

    document.querySelectorAll('[data-auth-open]').forEach((button) => {
        button.addEventListener('click', () => {
            const view = button.dataset.authView || 'login';
            openModal(view);
        });
    });

    modal.querySelectorAll('[data-auth-close]').forEach((button) => {
        button.addEventListener('click', () => {
            closeModal();
        });
    });

    modal.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeModal();
        }
    });

    modal.querySelectorAll('[data-auth-switch]').forEach((switcher) => {
        switcher.addEventListener('click', () => {
            const targetView = switcher.dataset.authSwitch;
            if (targetView) {
                showView(targetView);
            }
        });
    });

    const toObject = (form) => Object.fromEntries(new FormData(form).entries());

    const request = async (action, payload) => {
        const response = await fetch(`autenticacion.php?action=${encodeURIComponent(action)}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify(payload),
        });

        let data;
        try {
            data = await response.json();
        } catch (error) {
            throw new Error('No pudimos procesar la respuesta del servidor.');
        }

        if (!response.ok || !data?.success) {
            const message = data?.message || 'Ocurrió un error inesperado.';
            const error = new Error(message);
            error.payload = data;
            throw error;
        }

        return data;
    };

    const handleFormSubmission = (form, action, onSuccess) => {
        if (!form) {
            return;
        }

        form.addEventListener('submit', async (event) => {
            event.preventDefault();
            setMessage('Procesando solicitud...');

            try {
                const payload = toObject(form);
                const result = await request(action, payload);
                if (typeof onSuccess === 'function') {
                    onSuccess(result, payload);
                }
                if (result?.message) {
                    setMessage(result.message, 'success');
                } else {
                    setMessage('Operación completada.', 'success');
                }
            } catch (error) {
                if (error?.payload?.data?.needsVerification) {
                    const correo = error.payload.data.correo || toObject(form).correo || '';
                    showView('verify', { email: correo });
                }
                setMessage(error.message, 'error');
            }
        });
    };

    handleFormSubmission(loginForm, 'login', (result) => {
        window.setTimeout(() => {
            closeModal();
            window.location.reload();
        }, 600);
    });

    handleFormSubmission(registerForm, 'register', (result, payload) => {
        const email = result?.data?.correo || payload?.correo;
        showView('verify', { email });
        setMessage(result.message || 'Cuenta creada. Verifica tu correo.', 'success');
    });

    handleFormSubmission(verifyForm, 'verify', () => {
        window.setTimeout(() => {
            closeModal();
            window.location.reload();
        }, 600);
    });

    handleFormSubmission(forgotForm, 'forgot-password', () => {
        showView('login');
    });

    if (resendButton) {
        resendButton.addEventListener('click', async () => {
            const emailInput = verifyForm?.querySelector('input[name="correo"]');
            const correo = emailInput?.value?.trim();

            if (!correo) {
                setMessage('Ingresa el correo electrónico para reenviar el PIN.', 'error');
                if (emailInput) {
                    emailInput.focus();
                }
                return;
            }

            setMessage('Enviando un nuevo PIN...');

            try {
                const result = await request('resend-pin', { correo });
                setMessage(result.message, 'success');
            } catch (error) {
                setMessage(error.message, 'error');
            }
        });
    }

    if (googleButton) {
        googleButton.addEventListener('click', () => {
            setMessage('La autenticación con Google estará disponible muy pronto.', 'success');
        });
    }

    logoutButtons.forEach((button) => {
        button.addEventListener('click', async () => {
            try {
                await request('logout', {});
                window.location.reload();
            } catch (error) {
                openModal('login');
                setMessage(error.message, 'error');
            }
        });
    });
});
