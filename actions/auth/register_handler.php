<?php

    declare(strict_types=1);

    require_once __DIR__ . '/../../config/constants.php';
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../core/session.php';
    require_once __DIR__ . '/../../core/functions.php';
    require_once __DIR__ . '/../../core/auth.php';
    require_once __DIR__ . '/../../core/csrf.php';

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST'){
        set_flash_message('warning', 'Неверный метод запроса.');
        safe_redirect(ROUTE_REGISTER);
    }

    $length = static function (string $value): int{
        return function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);
    };

    $registerRedirectForCsrf = make_route_with_redirect(
        ROUTE_REGISTER,
        normalize_redirect_path((string) ($_POST['redirect_to'] ?? $_GET['redirect'] ?? ''))
    );
    csrf_validate_request_or_fail($registerRedirectForCsrf, 'Ошибка безопасности формы регистрации. Обновите страницу и попробуйте снова.');

    $redirectTo = normalize_redirect_path((string) ($_POST['redirect_to'] ?? $_GET['redirect'] ?? ''));
    if ($redirectTo === ''){
        $redirectTo = normalize_redirect_path((string) session_get(AUTH_REDIRECT_KEY, ''));
    }
    if ($redirectTo === ''){
        $redirectTo = DEFAULT_REDIRECT_HOME;
    }

    remember_auth_redirect($redirectTo);
    $registerRedirect = make_route_with_redirect(ROUTE_REGISTER, $redirectTo);

    $input = [
        'first_name' => trim((string) ($_POST['first_name'] ?? '')),
        'last_name' => trim((string) ($_POST['last_name'] ?? '')),
        'username' => trim((string) ($_POST['username'] ?? '')),
        'email' => trim((string) ($_POST['email'] ?? '')),
        'password' => (string) ($_POST['password'] ?? ''),
        'confirm_password' => (string) ($_POST['confirm_password'] ?? ''),
        'redirect_to' => $redirectTo];

    $errors = [];

    $requiredFields = [
        'first_name' => 'Введите имя.',
        'last_name' => 'Введите фамилию.',
        'username' => 'Введите username.',
        'email' => 'Введите email.',
        'password' => 'Введите пароль.',
        'confirm_password' => 'Подтвердите пароль.'];

    foreach ($requiredFields as $field => $message){
        if (is_blank($input[$field])){
            $errors[$field] = $message;
        }
    }

    if (!isset($errors['first_name'])){
        $firstNameLength = $length($input['first_name']);
        if ($firstNameLength < NAME_MIN_LENGTH || $firstNameLength > NAME_MAX_LENGTH){
            $errors['first_name'] = 'Имя должно содержать от ' . NAME_MIN_LENGTH . ' до ' . NAME_MAX_LENGTH . ' символов.';
        }
    }

    if (!isset($errors['last_name'])){
        $lastNameLength = $length($input['last_name']);
        if ($lastNameLength < NAME_MIN_LENGTH || $lastNameLength > NAME_MAX_LENGTH){
            $errors['last_name'] = 'Фамилия должна содержать от ' . NAME_MIN_LENGTH . ' до ' . NAME_MAX_LENGTH . ' символов.';
        }
    }

    if (!isset($errors['username'])){
        $usernameLength = $length($input['username']);

        if ($usernameLength < USERNAME_MIN_LENGTH || $usernameLength > USERNAME_MAX_LENGTH){
            $errors['username'] = 'Username должен содержать от ' . USERNAME_MIN_LENGTH . ' до ' . USERNAME_MAX_LENGTH . ' символов.';
        }elseif (!preg_match('/^[A-Za-z0-9_]+$/', $input['username'])){
            $errors['username'] = 'Username может содержать только латинские буквы, цифры и _.';
        }
    }

    if (!isset($errors['email'])) {
        if ($length($input['email']) > EMAIL_MAX_LENGTH){
            $errors['email'] = 'Слишком длинный email.';
        }elseif (!filter_var($input['email'], FILTER_VALIDATE_EMAIL)){
            $errors['email'] = 'Некорректный формат email.';
        }
    }

    if (!isset($errors['password'])) {
        $passwordLength = $length($input['password']);
        if ($passwordLength < PASSWORD_MIN_LENGTH || $passwordLength > PASSWORD_MAX_LENGTH){
            $errors['password'] = 'Пароль должен содержать от ' . PASSWORD_MIN_LENGTH . ' до ' . PASSWORD_MAX_LENGTH . ' символов.';
        }
    }

    if (!isset($errors['confirm_password']) && $input['password'] !== $input['confirm_password']){
        $errors['confirm_password'] = 'Пароли не совпадают.';
    }

    try{
        $pdo = db();
    }catch (Throwable $exception){
        set_old_input([
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'],
            'username' => $input['username'],
            'email' => $input['email'],
            'redirect_to' => $redirectTo]);
        set_flash_message('error', 'Ошибка подключения к базе данных. Проверьте, что MySQL запущен в OSPanel (127.0.1.30:3306).');
        safe_redirect($registerRedirect);
    }

    if (!isset($errors['email']) && $input['email'] !== ''){
        $emailStatement = $pdo -> prepare('SELECT id FROM users WHERE email = :email LIMIT 1');
        $emailStatement -> execute(['email' => $input['email']]);

        if ($emailStatement -> fetch()){
            $errors['email'] = 'Этот email уже используется.';
        }
    }

    if (!isset($errors['username']) && $input['username'] !== ''){
        $usernameStatement = $pdo -> prepare('SELECT id FROM users WHERE username = :username LIMIT 1');
        $usernameStatement -> execute(['username' => $input['username']]);

        if ($usernameStatement -> fetch()){
            $errors['username'] = 'Этот username уже занят.';
        }
    }

    if ($errors !== []){
        set_validation_errors($errors);
        set_old_input([
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'],
            'username' => $input['username'],
            'email' => $input['email'],
            'redirect_to' => $redirectTo]);
        set_flash_message('error', 'Исправьте ошибки в форме и попробуйте снова.');
        safe_redirect($registerRedirect);
    }

    try{
        $passwordHash = password_hash($input['password'], PASSWORD_DEFAULT);

        $insertStatement = $pdo -> prepare(
            'INSERT INTO users (first_name, last_name, username, email, password_hash)
             VALUES (:first_name, :last_name, :username, :email, :password_hash)'
        );

        $insertStatement -> execute([
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'],
            'username' => $input['username'],
            'email' => $input['email'],
            'password_hash' => $passwordHash,
        ]);

        $userId = (int) $pdo->lastInsertId();

        $userStatement = $pdo -> prepare(
            'SELECT id, first_name, last_name, username, email
             FROM users
             WHERE id = :id
             LIMIT 1'
        );
        $userStatement -> execute(['id' => $userId]);
        $user = $userStatement -> fetch();

        if (!$user){
            throw new RuntimeException('Не удалось загрузить данные созданного пользователя.');
        }

        login_user($user);
        clear_old_input();
        set_validation_errors([]);
        set_flash_message('success', 'Регистрация прошла успешно.');
        safe_redirect(consume_auth_redirect($redirectTo));
    }catch (Throwable $exception){
        set_old_input([
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'],
            'username' => $input['username'],
            'email' => $input['email'],
            'redirect_to' => $redirectTo]);
        set_flash_message('error', 'Не удалось создать аккаунт. Попробуйте еще раз.');
        safe_redirect($registerRedirect);
    }
?>