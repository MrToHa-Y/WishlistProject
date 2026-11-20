<?php

    declare(strict_types=1);

    require_once __DIR__ . '/../../config/constants.php';
    require_once __DIR__ . '/../../core/session.php';
    require_once __DIR__ . '/../../core/functions.php';
    require_once __DIR__ . '/../../core/auth.php';
    require_once __DIR__ . '/../../core/csrf.php';

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST'){
        safe_redirect(DEFAULT_REDIRECT_HOME);
    }

    csrf_validate_request_or_fail(DEFAULT_REDIRECT_HOME, 'Ошибка безопасности при выходе из аккаунта. Обновите страницу и попробуйте снова.');

    logout_user();
    set_flash_message('success', 'Вы вышли из аккаунта.');
    safe_redirect(DEFAULT_REDIRECT_HOME);
?>