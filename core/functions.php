<?php

    declare(strict_types=1);

    require_once __DIR__ . '/session.php';
    require_once __DIR__ . '/../config/constants.php';

    if (!function_exists('e')){
        function e(string $value): string
        {
            return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        }
    }

    if (!function_exists('is_blank')){
        function is_blank(?string $value): bool
        {
            return trim((string) $value) === '';
        }
    }

    if (!function_exists('safe_redirect')){
        function safe_redirect(string $path): void
        {
            $path = trim($path);

            if ($path === ''){
                $path = DEFAULT_REDIRECT_HOME;
            }

            $parts = parse_url($path);
            if ($parts !== false && isset($parts['host'])){
                $path = DEFAULT_REDIRECT_HOME;
            }

            if (substr($path, 0, 1) !== '/'){
                $path = '/' . ltrim($path, '/');
            }

            header('Location: ' . $path, true, 302);
            exit;
        }
    }

    if (!function_exists('normalize_redirect_path')){
        function normalize_redirect_path(?string $path): string
        {
            $path = trim((string) $path);
            if ($path === ''){
                return '';
            }

            $parts = parse_url($path);
            if ($parts === false){
                return '';
            }

            if (isset($parts['scheme']) || isset($parts['host'])){
                return '';
            }

            $normalizedPath = (string) ($parts['path'] ?? '');
            if ($normalizedPath === '' || substr($normalizedPath, 0, 1) !== '/' || substr($normalizedPath, 0, 2) === '//') {
                return '';
            }

            if (isset($parts['query']) && $parts['query'] !== ''){
                $normalizedPath .= '?' . $parts['query'];
            }

            return $normalizedPath;
        }
    }

    if (!function_exists('make_route_with_redirect')){
        function make_route_with_redirect(string $route, string $redirectPath): string
        {
            $redirectPath = normalize_redirect_path($redirectPath);
            if ($redirectPath === ''){
                return $route;
            }

            return $route . '?redirect=' . rawurlencode($redirectPath);
        }
    }

    if (!function_exists('remember_auth_redirect')){
        function remember_auth_redirect(string $path): void
        {
            $path = normalize_redirect_path($path);
            if ($path === ''){
                return;
            }

            $pathOnly = (string) (parse_url($path, PHP_URL_PATH) ?? '');

            $blocked = [
                ROUTE_LOGIN,
                ROUTE_REGISTER,
                ROUTE_LOGIN_HANDLER,
                ROUTE_REGISTER_HANDLER,
                ROUTE_LOGOUT_HANDLER,
                ROUTE_SAVE_WISH_HANDLER,
                ROUTE_ADD_FULFILLED_WISH_HANDLER,
                ROUTE_DELETE_FULFILLED_WISH_HANDLER,
                ROUTE_UPDATE_FULFILLED_WISH_HANDLER,
                ROUTE_DELETE_FULFILLED_WISH_PERMANENT_HANDLER,
                ROUTE_UPDATE_PROFILE_HANDLER,
                ROUTE_UPLOAD_AVATAR_HANDLER,
                ROUTE_CHANGE_EMAIL_HANDLER,
                ROUTE_CHANGE_PASSWORD_HANDLER,
                ROUTE_DELETE_PROFILE_HANDLER];

            if (in_array($pathOnly, $blocked, true)){
                return;
            }

            session_set(AUTH_REDIRECT_KEY, $path);
        }
    }

    if (!function_exists('consume_auth_redirect')){
        function consume_auth_redirect(string $fallback = DEFAULT_REDIRECT_HOME): string
        {
            $stored = normalize_redirect_path((string) session_get(AUTH_REDIRECT_KEY, ''));
            session_forget(AUTH_REDIRECT_KEY);

            if ($stored !== ''){
                return $stored;
            }

            $fallback = normalize_redirect_path($fallback);
            return $fallback !== '' ? $fallback : DEFAULT_REDIRECT_HOME;
        }
    }

    if (!function_exists('set_flash_message')){
        function set_flash_message(string $type, string $message): void
        {
            $allowedTypes = ['success', 'error', 'warning', 'info'];
            $type = in_array($type, $allowedTypes, true) ? $type : 'info';

            $flash = session_get(FLASH_MESSAGES_KEY, []);
            $flash[$type][] = $message;

            session_set(FLASH_MESSAGES_KEY, $flash);
        }
    }

    if (!function_exists('get_flash_messages')){
        function get_flash_messages(bool $clear = true): array
        {
            $messages = session_get(FLASH_MESSAGES_KEY, []);

            if ($clear){
                session_forget(FLASH_MESSAGES_KEY);
            }

            return is_array($messages) ? $messages : [];
        }
    }

    if (!function_exists('set_validation_errors')){
        function set_validation_errors(array $errors): void
        {
            session_set(VALIDATION_ERRORS_KEY, $errors);
        }
    }

    if (!function_exists('get_validation_errors')){
        function get_validation_errors(bool $clear = true): array
        {
            $errors = session_get(VALIDATION_ERRORS_KEY, []);

            if ($clear){
                session_forget(VALIDATION_ERRORS_KEY);
            }

            return is_array($errors) ? $errors : [];
        }
    }

    if (!function_exists('set_old_input')){
        function set_old_input(array $input): void
        {
            session_set(OLD_INPUT_KEY, $input);
        }
    }

    if (!function_exists('old')){
        function old(string $key, string $default = ''): string
        {
            $oldInput = session_get(OLD_INPUT_KEY, []);
            if (!is_array($oldInput)){
                return $default;
            }

            $value = $oldInput[$key] ?? $default;
            return is_scalar($value) ? (string) $value : $default;
        }
    }

    if (!function_exists('clear_old_input')){
        function clear_old_input(): void
        {
            session_forget(OLD_INPUT_KEY);
        }
    }

    if (!function_exists('field_error')){
        function field_error(array $errors, string $field): ?string
        {
            if (!isset($errors[$field])){
                return null;
            }

            return is_string($errors[$field]) ? $errors[$field] : null;
        }
    }

    if (!function_exists('versioned_asset')){
        function versioned_asset(string $publicPath): string
        {
            $normalizedPath = '/' . ltrim($publicPath, '/');
            $relativePath = str_replace('/', DIRECTORY_SEPARATOR, ltrim($normalizedPath, '/'));
            $absolutePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . $relativePath;
            $version = is_file($absolutePath) ? (string) filemtime($absolutePath) : '1';

            return $normalizedPath . '?v=' . $version;
        }
    }
