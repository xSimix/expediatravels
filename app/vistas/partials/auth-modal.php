<div class="auth-modal" data-auth-modal hidden>
    <div class="auth-modal__backdrop" data-auth-close></div>
    <div class="auth-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="auth-modal-title">
        <button class="auth-modal__close" type="button" aria-label="Cerrar ventana" data-auth-close>×</button>
        <div class="auth-modal__logo" aria-hidden="true">🧭</div>
        <div class="auth-modal__message" data-auth-message role="status" aria-live="polite"></div>

        <section class="auth-view auth-view--active" data-auth-view="login">
            <header class="auth-view__header">
                <h2 id="auth-modal-title">Inicia sesión</h2>
                <p>Accede a tu cuenta para completar tus reservas y seguir tus itinerarios.</p>
            </header>
            <form class="auth-form" id="auth-login-form" novalidate>
                <label class="auth-field">
                    <span>Correo electrónico</span>
                    <input type="email" name="correo" autocomplete="email" required />
                </label>
                <label class="auth-field">
                    <span>Contraseña</span>
                    <input type="password" name="password" autocomplete="current-password" required />
                </label>
                <label class="auth-checkbox">
                    <input type="checkbox" name="recordar" value="1" />
                    <span>Recordar mi cuenta</span>
                </label>
                <button class="auth-submit" type="submit">Ingresar</button>
                <button class="auth-google" type="button" data-auth-google>
                    <span aria-hidden="true">🔐</span>
                    Continuar con Google
                </button>
                <p class="auth-links">
                    <button class="auth-link" type="button" data-auth-switch="forgot">¿Olvidaste tu contraseña?</button>
                </p>
                <p class="auth-links">
                    ¿No tienes una cuenta?
                    <button class="auth-link" type="button" data-auth-switch="register">Crear una cuenta</button>
                </p>
            </form>
        </section>

        <section class="auth-view" data-auth-view="register" hidden>
            <header class="auth-view__header">
                <h2>Crea tu cuenta</h2>
                <p>Regístrate para recibir beneficios exclusivos y asesoría personalizada.</p>
            </header>
            <form class="auth-form" id="auth-register-form" novalidate>
                <label class="auth-field">
                    <span>Nombre completo</span>
                    <input type="text" name="nombre" autocomplete="name" required />
                </label>
                <label class="auth-field">
                    <span>Correo electrónico</span>
                    <input type="email" name="correo" autocomplete="email" required />
                </label>
                <label class="auth-field">
                    <span>Contraseña</span>
                    <input type="password" name="password" autocomplete="new-password" required />
                </label>
                <label class="auth-checkbox">
                    <input type="checkbox" name="terminos" value="1" required />
                    <span>Acepto los términos y condiciones</span>
                </label>
                <button class="auth-submit" type="submit">Crear cuenta</button>
                <p class="auth-links">
                    ¿Ya tienes una cuenta?
                    <button class="auth-link" type="button" data-auth-switch="login">Inicia sesión</button>
                </p>
            </form>
        </section>

        <section class="auth-view" data-auth-view="forgot" hidden>
            <header class="auth-view__header">
                <h2>Recupera tu acceso</h2>
                <p>Te enviaremos un enlace para restablecer tu contraseña.</p>
            </header>
            <form class="auth-form" id="auth-forgot-form" novalidate>
                <label class="auth-field">
                    <span>Correo electrónico</span>
                    <input type="email" name="correo" autocomplete="email" required />
                </label>
                <button class="auth-submit" type="submit">Enviar enlace</button>
                <p class="auth-links">
                    ¿Recordaste tu contraseña?
                    <button class="auth-link" type="button" data-auth-switch="login">Volver a iniciar sesión</button>
                </p>
            </form>
        </section>
    </div>
</div>
