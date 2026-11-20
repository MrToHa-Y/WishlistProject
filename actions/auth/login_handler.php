<?php

    declare(strict_types=1);

    require_once __DIR__ . '/../../config/constants.php';
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../core/session.php';
    require_once __DIR__ . '/../../core/auth.php';
    require_once __DIR__ . '/../../core/functions.php';
    require_once __DIR__ . '/../../core/csrf.php';

    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST'){
        set_flash_message('warning', 'Неверный метод запроса.');
        safe_redirect(ROUTE_LOGIN);
    }

    $loginRedirectForCsrf = make_route_with_redirect(
        ROUTE_LOGIN,
        normalize_redirect_path((string) ($_POST['redirect_to'] ?? $_GET['redirect'] ?? ''))
    );
    csrf_validate_request_or_fail($loginRedirectForCsrf, 'Ошибка безопасности формы входа. Обновите страницу и попробуйте снова.');

    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    $redirectTo = normalize_redirect_path((string) ($_POST['redirect_to'] ?? $_GET['redirect'] ?? ''));

    if ($redirectTo === ''){
        $redirectTo = normalize_redirect_path((string) session_get(AUTH_REDIRECT_KEY, ''));
    }

    if ($redirectTo === ''){
        $redirectTo = DEFAULT_REDIRECT_HOME;
    }

    remember_auth_redirect($redirectTo);
    $loginRedirect = make_route_with_redirect(ROUTE_LOGIN, $redirectTo);

    $errors = [];

    if (is_blank($email)){
        $errors['email'] = 'Введите email.';
    }elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $errors['email'] = 'Введите корректный email.';
    }

    if (is_blank($password)){
        $errors['password'] = 'Введите пароль.';
    }

    if ($errors !== []){
        set_flash_message('error', 'Не удалось выполнить вход. Проверьте введенные данные.');
        set_validation_errors($errors);
        set_old_input([
            'email' => $email,
            'redirect_to' => $redirectTo,
        ]);
        safe_redirect($loginRedirect);
    }

    try{
        $pdo = db();
    }catch (Throwable $exception){
        set_flash_message('error', 'Не удалось выполнить вход: ошибка подключения к базе данных.');
        set_validation_errors(['login' => 'Ошибка подключения к базе данных.']);
        set_old_input([
            'email' => $email,
            'redirect_to' => $redirectTo,
        ]);
        safe_redirect($loginRedirect);
    }

    $statement = $pdo -> prepare(
        'SELECT id, first_name, last_name, username, email, password_hash
         FROM users
         WHERE email = :email
         LIMIT 1'
    );
    $statement -> execute(['email' => $email]);
    $user = $statement -> fetch();

    $loginError = 'Неверный email или пароль.';

    if (!$user){
        set_flash_message('error', 'Неверный email или пароль.');
        set_validation_errors(['login' => $loginError]);
        set_old_input([
            'email' => $email,
            'redirect_to' => $redirectTo,
        ]);
        safe_redirect($loginRedirect);
    }

    if (!password_verify($password, (string) $user['password_hash'])){
        set_flash_message('error', 'Неверный email или пароль.');
        set_validation_errors(['login' => $loginError]);
        set_old_input([
            'email' => $email,
            'redirect_to' => $redirectTo,
        ]);
        safe_redirect($loginRedirect);
    }

    login_user($user);
    session_set('user_id', (int) $user['id']);
    session_set('username', (string) ($user['username'] ?? ''));

    clear_old_input();
    set_validation_errors([]);
    set_flash_message('success', 'Вход выполнен успешно.');
    safe_redirect(consume_auth_redirect($redirectTo));
?>