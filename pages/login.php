<?php

    declare(strict_types=1);

    require_once __DIR__ . '/../config/constants.php';
    require_once __DIR__ . '/../core/session.php';
    require_once __DIR__ . '/../core/functions.php';
    require_once __DIR__ . '/../core/auth.php';
    require_once __DIR__ . '/../core/csrf.php';
    require_once __DIR__ . '/../core/seo.php';

    require_guest(DEFAULT_REDIRECT_HOME);

    $errors = get_validation_errors();

    $requestedRedirect = normalize_redirect_path((string) ($_GET['redirect'] ?? old('redirect_to')));
    if ($requestedRedirect === ''){
        $requestedRedirect = normalize_redirect_path((string) session_get(AUTH_REDIRECT_KEY, ''));
    }
    if ($requestedRedirect === ''){
        $requestedRedirect = DEFAULT_REDIRECT_HOME;
    }

    $closeRedirect = $requestedRedirect;
    $closePathOnly = (string) (parse_url($closeRedirect, PHP_URL_PATH) ?? '');
    $closeBlockedPaths = [
        ROUTE_LOGIN,
        ROUTE_REGISTER,
        ROUTE_LOGIN_HANDLER,
        ROUTE_REGISTER_HANDLER,
        ROUTE_PROFILE,
        ROUTE_SETTINGS,
        ROUTE_FULFILLED];
    if ($closeRedirect === '' || in_array($closePathOnly, $closeBlockedPaths, true)){
        $closeRedirect = DEFAULT_REDIRECT_HOME;
    }

    remember_auth_redirect($requestedRedirect);
    $registerWithRedirect = make_route_with_redirect(ROUTE_REGISTER, $requestedRedirect);

    $oldValues = [
        'email' => old('email'),
        'redirect_to' => old('redirect_to', $requestedRedirect)];

    $seoMeta = [
        'title' => 'Вход — Ohmywishes',
        'robots' => 'noindex, nofollow',
        'url' => ROUTE_LOGIN,
        'og_type' => 'website',
        'og_image' => ASSETS_PATH . '/images/icons/icon_logo.png',
        'og_site_name' => 'Ohmywishes'];

    clear_old_input();

    $versionedAsset = static function (string $publicPath): string{
        $normalizedPath = '/' . ltrim($publicPath, '/');
        $absolutePath = __DIR__ . '/..' . $normalizedPath;
        $version = is_file($absolutePath) ? (string) filemtime($absolutePath) : '1';

        return $normalizedPath . '?v=' . $version;
    };
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <?= render_seo_meta($seoMeta) ?>
        <link rel="icon" href="./../assets/images/icons/icon_logo.png">
        <link rel="stylesheet" href="<?= e(versioned_asset('/css/main.css')) ?>">
        <script src="<?= e(versioned_asset(ASSETS_PATH . '/scripts/alerts.js')) ?>" defer></script>
        <script src="<?= e(versioned_asset(ASSETS_PATH . '/scripts/form_validation.js')) ?>" defer></script>
        <script src="<?= e(versioned_asset(ASSETS_PATH . '/scripts/background_photo_animation.js')) ?>" defer></script>
        <script src="<?= e(versioned_asset(ASSETS_PATH . '/scripts/main.js')) ?>" defer></script>
    </head>
    <body class="login-page">
        <div class="login-page__overlay" data-login-overlay data-close-url="<?= e($closeRedirect) ?>">
            <div class="login-page__toast-area">
                <?php require __DIR__ . '/../includes/flash_message.php'; ?>

                <?php if ($errors !== []): ?>
                    <section class="login-form__summary" data-login-form-error-summary role="alert">
                        <p>Исправьте ошибки в форме входа:</p>
                        <ul class="login-form__summary-list">
                            <?php foreach ($errors as $error): ?>
                                <li><?= e((string) $error) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </section>
                <?php else: ?>
                    <section class="login-form__summary login-form__summary--hidden" data-login-form-error-summary hidden></section>
                <?php endif; ?>
            </div>

            <main class="login-page__card auth-card" aria-labelledby="login-title">
                <a class="login-page__close" href="<?= e($closeRedirect) ?>" data-login-close>
                    <svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="18" cy="18" r="18" fill="#313132"/>
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M26.4853 10.9288C26.8758 10.5383 26.8758 9.9051 26.4853 9.51458C26.0948 9.12405 25.4616 9.12405 25.0711 9.51458L17.9999 16.5858L10.9288 9.51469C10.5383 9.12417 9.90513 9.12417 9.5146 9.51469C9.12408 9.90522 9.12408 10.5384 9.5146 10.9289L16.5857 18L9.51472 25.0709C9.12419 25.4614 9.12419 26.0946 9.51472 26.4851C9.90524 26.8757 10.5384 26.8757 10.9289 26.4851L17.9999 19.4142L25.071 26.4853C25.4615 26.8758 26.0946 26.8758 26.4852 26.4853C26.8757 26.0947 26.8757 25.4616 26.4852 25.071L19.4141 18L26.4853 10.9288Z" fill="white"/>
                    </svg>
                </a>

                <h1 class="auth-card__title" id="login-title">Sign in</h1>

                <form class="login-form" method="post" action="<?= e(ROUTE_LOGIN_HANDLER) ?>" novalidate data-login-form>
                    <input type="hidden" name="redirect_to" value="<?= e($oldValues['redirect_to']) ?>">
                    <?= csrf_field() ?>

                    <div class="login-form__field<?= field_error($errors, 'email') ? ' is-invalid' : '' ?>" data-login-field="email">
                        <input class="login-form__input" id="login_email" name="email" type="email" autocomplete="email" placeholder=" " value="<?= e($oldValues['email']) ?>" required>
                        <label class="login-form__label" for="login_email">Email</label>
                        <p class="login-form__field-error" data-login-field-error-for="email">
                            <?= e((string) field_error($errors, 'email')) ?>
                        </p>
                    </div>

                    <div class="login-form__field<?= field_error($errors, 'password') ? ' is-invalid' : '' ?>" data-login-field="password">
                        <input class="login-form__input" id="login_password" name="password" type="password" autocomplete="current-password" placeholder=" " required>
                        <label class="login-form__label" for="login_password">Password</label>
                        <p class="login-form__field-error" data-login-field-error-for="password">
                            <?= e((string) field_error($errors, 'password')) ?>
                        </p>
                    </div>

                    <button class="login-form__submit" type="submit">Войти</button>

                    <div class="login-form__footer">
                        <a class="login-form__action login-form__action--forgot" href="#">Забыли пароль?</a>
                        <a class="login-form__action login-form__action--register" href="<?= e($registerWithRedirect) ?>">Зарегистрироваться</a>
                    </div>
                </form>
            </main>
        </div>
    </body>
</html>
