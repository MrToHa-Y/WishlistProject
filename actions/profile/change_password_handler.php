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

    $errorRedirect = ROUTE_SETTINGS . '?modal=change-password';
    csrf_validate_request_or_fail($errorRedirect, 'Ошибка безопасности формы смены пароля. Обновите страницу и попробуйте снова.');

    $currentPassword = (string) ($_POST['current_password'] ?? '');
    $newPassword = (string) ($_POST['new_password'] ?? '');
    $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

    $errors = [];

    if (trim($currentPassword) === ''){
        $errors['current_password'] = 'Укажите текущий пароль.';
    }

    if (trim($newPassword) === ''){
        $errors['new_password'] = 'Укажите новый пароль.';
    }else{
        $newPasswordLength = function_exists('mb_strlen') ? mb_strlen($newPassword) : strlen($newPassword);
        if ($newPasswordLength < PASSWORD_MIN_LENGTH){
            $errors['new_password'] = 'Новый пароль должен содержать минимум ' . PASSWORD_MIN_LENGTH . ' символов.';
        }elseif ($newPasswordLength > PASSWORD_MAX_LENGTH){
            $errors['new_password'] = 'Новый пароль не должен превышать ' . PASSWORD_MAX_LENGTH . ' символов.';
        }
    }

    if (trim($confirmPassword) === '') {
        $errors['confirm_password'] = 'Подтвердите новый пароль.';
    }elseif ($newPassword !== '' && $newPassword !== $confirmPassword){
        $errors['confirm_password'] = 'Пароли не совпадают.';
    }

    if ($errors !== []){
        set_flash_message('error', 'Не удалось изменить пароль. Проверьте заполнение формы.');
        set_validation_errors($errors);
        safe_redirect($errorRedirect);
    }

    try{
        $pdo = db();
    }catch (Throwable $exception){
        set_flash_message('error', 'Не удалось изменить пароль: ошибка подключения к базе данных.');
        set_validation_errors(['change_password' => 'Ошибка подключения к базе данных.']);
        safe_redirect($errorRedirect);
    }

    try{
        $statement = $pdo -> prepare(
            'SELECT password_hash
             FROM users
             WHERE id = :id
             LIMIT 1'
        );
        $statement -> execute(['id' => $userId]);
        $row = $statement -> fetch();

        if (!is_array($row)){
            set_flash_message('error', 'Не удалось изменить пароль: пользователь не найден.');
            set_validation_errors(['change_password' => 'Пользователь не найден.']);
            safe_redirect($errorRedirect);
        }

        $passwordHash = (string) ($row['password_hash'] ?? '');
        if ($passwordHash === '' || !password_verify($currentPassword, $passwordHash)){
            set_flash_message('error', 'Не удалось изменить пароль: текущий пароль введен неверно.');
            set_validation_errors(['current_password' => 'Текущий пароль указан неверно.']);
            safe_redirect($errorRedirect);
        }

        if ($currentPassword === $newPassword){
            set_flash_message('error', 'Не удалось изменить пароль: новый пароль совпадает с текущим.');
            set_validation_errors(['new_password' => 'Новый пароль должен отличаться от текущего.']);
            safe_redirect($errorRedirect);
        }

        $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        if (!is_string($newPasswordHash) || $newPasswordHash === ''){
            set_flash_message('error', 'Не удалось изменить пароль: ошибка подготовки нового пароля.');
            set_validation_errors(['change_password' => 'Не удалось подготовить новый пароль.']);
            safe_redirect($errorRedirect);
        }

        $updateStatement = $pdo -> prepare(
            'UPDATE users
             SET password_hash = :password_hash
             WHERE id = :id
             LIMIT 1'
        );
        $updateStatement -> execute([
            'password_hash' => $newPasswordHash,
            'id' => $userId,
        ]);

        set_validation_errors([]);
        set_flash_message('success', 'Пароль успешно изменен.');
        safe_redirect(ROUTE_SETTINGS);
    }catch (Throwable $exception){
        set_flash_message('error', 'Не удалось изменить пароль.');
        set_validation_errors(['change_password' => 'Не удалось обновить пароль. Попробуйте еще раз.']);
        safe_redirect($errorRedirect);
    }
?>    