<?php

    declare(strict_types=1);

    require_once __DIR__ . '/session.php';
    require_once __DIR__ . '/functions.php';
    require_once __DIR__ . '/csrf.php';
    require_once __DIR__ . '/../config/constants.php';

    if (!function_exists('is_authenticated')){
        function is_authenticated(): bool
        {
            $user = session_get(AUTH_SESSION_KEY);
            return is_array($user) && isset($user['id']) && (int) $user['id'] > 0;
        }
    }

    if (!function_exists('auth_user')){
        function auth_user(): ?array
        {
            $user = session_get(AUTH_SESSION_KEY);
            return is_array($user) ? $user : null;
        }
    }

    if (!function_exists('login_user')){
        function login_user(array $user): void
        {
            start_session();
            session_regenerate_id(true);

            session_set(AUTH_SESSION_KEY, [
                'id' => (int) ($user['id'] ?? 0),
                'first_name' => (string) ($user['first_name'] ?? ''),
                'last_name' => (string) ($user['last_name'] ?? ''),
                'username' => (string) ($user['username'] ?? ''),
                'email' => (string) ($user['email'] ?? '')]);
        }
    }

    if (!function_exists('logout_user')){
        function logout_user(): void
        {
            start_session();
            session_forget(AUTH_SESSION_KEY);
            session_regenerate_id(true);
        }
    }

    if (!function_exists('require_auth')){
        function require_auth(string $redirect = DEFAULT_REDIRECT_LOGIN): void
        {
            if (!is_authenticated()){
                $message = 'Сначала выполните вход в аккаунт.';
                $flash = get_flash_messages(false);
                $existingWarnings = $flash['warning'] ?? [];
                $alreadyHasMessage = is_array($existingWarnings) && in_array($message, $existingWarnings, true);

                if (!$alreadyHasMessage){
                    set_flash_message('warning', $message);
                }

                safe_redirect($redirect);
            }
        }
    }

    if (!function_exists('require_guest')){
        function require_guest(string $redirect = DEFAULT_REDIRECT_HOME): void
        {
            if (is_authenticated()){
                safe_redirect($redirect);
            }
        }
    }

    if (!function_exists('render_auth_entry_button')){
        function render_auth_entry_button(): string
        {
            if (!is_authenticated()){
                $registerUrl = e(ROUTE_REGISTER);

                return '<a class="auth-switch__button auth-switch__button--register" href="' . $registerUrl . '">Sign up</a>';
            }

            $user = auth_user();
            if ($user === null){
                return '';
            }

            $fullName = trim($user['first_name'] . ' ' . $user['last_name']);
            if ($fullName === ''){
                $fullName = $user['username'] !== '' ? $user['username'] : 'Profile';
            }

            $safeName = e($fullName);
            $safeUsername = e($user['username']);
            $safeEmail = e($user['email']);
            $logoutUrl = e(ROUTE_LOGOUT_HANDLER);
            $csrfField = csrf_field();

            return <<<HTML
                <div class="auth-switch">
                    <button class="auth-switch__button auth-switch__button--profile" type="button" data-profile-toggle>{$safeName}</button>
                    <div class="auth-switch__modal" data-profile-modal hidden>
                        <button type="button" class="auth-switch__close" data-profile-close aria-label="Close profile">&times;</button>
                        <h3 class="auth-switch__title">Profile</h3>
                        <dl class="auth-switch__details">
                            <div class="auth-switch__row">
                                <dt>Name</dt>
                                <dd>{$safeName}</dd>
                            </div>
                            <div class="auth-switch__row">
                                <dt>Username</dt>
                                <dd>@{$safeUsername}</dd>
                            </div>
                            <div class="auth-switch__row">
                                <dt>Email</dt>
                                <dd>{$safeEmail}</dd>
                            </div>
                        </dl>
                        <form method="post" action="{$logoutUrl}">
                            {$csrfField}
                            <button class="auth-switch__logout" type="submit">Sign out</button>
                        </form>
                    </div>
                </div>
            HTML;
        }
    }