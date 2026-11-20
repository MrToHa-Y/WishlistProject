<?php

    declare(strict_types=1);

    if (!function_exists('start_session')){
        function start_session(): void
        {
            if (session_status() === PHP_SESSION_ACTIVE){
                return;
            }

            if (!headers_sent()){
                ini_set('default_charset', 'UTF-8');
                header('Content-Type: text/html; charset=UTF-8');
            }

            $isSecure = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => $isSecure,
                'httponly' => true,
                'samesite' => 'Lax',
                ]);

            session_start();
        }
    }

    if (!function_exists('session_get')){
        function session_get(string $key, $default = null)
        {
            start_session();
            return $_SESSION[$key] ?? $default;
        }
    }

    if (!function_exists('session_set')){
        function session_set(string $key, $value): void
        {
            start_session();
            $_SESSION[$key] = $value;
        }
    }

    if (!function_exists('session_pull')){
        function session_pull(string $key, $default = null)
        {
            start_session();

            if (!array_key_exists($key, $_SESSION)){
                return $default;
            }

            $value = $_SESSION[$key];
            unset($_SESSION[$key]);

            return $value;
        }
    }

    if (!function_exists('session_forget')){
        function session_forget(string $key): void
        {
            start_session();
            unset($_SESSION[$key]);
        }
    }

    start_session();
