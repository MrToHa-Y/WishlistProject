<?php

    declare(strict_types=1);

    require_once __DIR__ . '/../../config/constants.php';
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../core/session.php';
    require_once __DIR__ . '/../../core/functions.php';
    require_once __DIR__ . '/../../core/auth.php';
    require_once __DIR__ . '/../../core/csrf.php';

    if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'POST'){
        set_flash_message('warning', 'Неверный метод запроса.');
        safe_redirect(ROUTE_SETTINGS);
    }

    require_auth(make_route_with_redirect(ROUTE_LOGIN, ROUTE_SETTINGS));

    $user = auth_user();
    $userId = (int) ($user['id'] ?? 0);
    if ($userId <= 0){
        set_flash_message('error', 'Не удалось определить пользователя.');
        safe_redirect(ROUTE_SETTINGS);
    }

    $errorRedirect = ROUTE_SETTINGS . '?modal=change-email';
    csrf_validate_request_or_fail($errorRedirect, 'Ошибка безопасности формы смены почты. Обновите страницу и попробуйте снова.');

    $newEmail = trim((string) ($_POST['email'] ?? ''));
    $errors = [];

    if ($newEmail === ''){
        $errors['change_email_email'] = 'Укажите новую почту.';
    }elseif (strlen($newEmail) > EMAIL_MAX_LENGTH){
        $errors['change_email_email'] = 'Слишком длинный email.';
    }elseif (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)){
        $errors['change_email_email'] = 'Введите корректный email.';
    }

    if ($errors !== []){
        set_flash_message('error', 'Не удалось изменить почту. Проверьте заполнение формы.');
        set_validation_errors($errors);
        set_old_input(['settings_new_email' => $newEmail]);
        safe_redirect($errorRedirect);
    }

    try{
        $pdo = db();
    }catch (Throwable $exception){
        set_flash_message('error', 'Не удалось изменить почту: ошибка подключения к базе данных.');
        set_validation_errors(['change_email' => 'Не удалось подключиться к базе данных.']);
        set_old_input(['settings_new_email' => $newEmail]);
        safe_redirect($errorRedirect);
    }

    try{
        $emailStatement = $pdo -> prepare(
            'SELECT id
             FROM users
             WHERE email = :email
               AND id <> :id
             LIMIT 1'
        );
        $emailStatement -> execute([
            'email' => $newEmail,
            'id' => $userId,
        ]);

        if ($emailStatement -> fetch()){
            set_flash_message('error', 'Не удалось изменить почту: этот адрес уже используется.');
            set_validation_errors(['change_email_email' => 'Эта почта уже используется.']);
            set_old_input(['settings_new_email' => $newEmail]);
            safe_redirect($errorRedirect);
        }

        $updateStatement = $pdo -> prepare(
            'UPDATE users
             SET email = :email
             WHERE id = :id
             LIMIT 1'
        );
        $updateStatement -> execute([
            'email' => $newEmail,
            'id' => $userId,
        ]);

        $authPayload = auth_user() ?? [];
        $authPayload['email'] = $newEmail;
        session_set(AUTH_SESSION_KEY, $authPayload);

        clear_old_input();
        set_validation_errors([]);
        set_flash_message('success', 'Почта успешно изменена.');
        safe_redirect(ROUTE_SETTINGS);
    }catch (Throwable $exception){
        set_flash_message('error', 'Не удалось изменить почту.');
        set_validation_errors(['change_email' => 'Не удалось обновить почту. Попробуйте еще раз.']);
        set_old_input(['settings_new_email' => $newEmail]);
        safe_redirect($errorRedirect);
    }
?>    