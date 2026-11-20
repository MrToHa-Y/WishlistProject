<?php

    declare(strict_types=1);

    require_once __DIR__ . '/session.php';

    if (!defined('CSRF_TOKEN_SESSION_KEY')){
        define('CSRF_TOKEN_SESSION_KEY', 'csrf_token');
    }

    if (!defined('CSRF_TOKEN_FIELD_NAME')){
        define('CSRF_TOKEN_FIELD_NAME', 'csrf_token');
    }

    if (!function_exists('csrf_token')){
        function csrf_token(bool $regenerate = false): string
        {
            start_session();

            $token = $_SESSION[CSRF_TOKEN_SESSION_KEY] ?? null;
            if (!$regenerate && is_string($token) && $token !== ''){
                return $token;
            }

            try{
                $token = bin2hex(random_bytes(32));
            }catch (Throwable $exception){
                $token = hash('sha256', uniqid((string) mt_rand(), true));
            }

            $_SESSION[CSRF_TOKEN_SESSION_KEY] = $token;
            return $token;
        }
    }

    if (!function_exists('csrf_field')){
        function csrf_field(): string
        {
            $name = htmlspecialchars(CSRF_TOKEN_FIELD_NAME, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $value = htmlspecialchars(csrf_token(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            return '<input type="hidden" name="' . $name . '" value="' . $value . '">';
        }
    }

    if (!function_exists('csrf_is_valid')){
        function csrf_is_valid(?string $submittedToken): bool
        {
            start_session();

            $sessionToken = $_SESSION[CSRF_TOKEN_SESSION_KEY] ?? '';
            if (!is_string($sessionToken) || $sessionToken === ''){
                return false;
            }

            $submittedToken = is_string($submittedToken) ? trim($submittedToken) : '';
            if ($submittedToken === ''){
                return false;
            }

            return hash_equals($sessionToken, $submittedToken);
        }
    }

    if (!function_exists('csrf_validate_request_or_fail')){
        function csrf_validate_request_or_fail(
            string $redirectPath,
            string $errorMessage = 'Ошибка безопасности формы. Обновите страницу и попробуйте снова.'
        ): void{
            $submittedToken = $_POST[CSRF_TOKEN_FIELD_NAME] ?? '';
            if (csrf_is_valid(is_scalar($submittedToken) ? (string) $submittedToken : '')){
                return;
            }

            if (function_exists('set_flash_message')){
                set_flash_message('error', $errorMessage);
            }

            if (function_exists('safe_redirect')){
                safe_redirect($redirectPath);
            }

            header('Location: ' . $redirectPath, true, 302);
            exit;
        }
    }
