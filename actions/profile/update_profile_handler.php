<?php

    declare(strict_types=1);

    require_once __DIR__ . '/../../config/constants.php';
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../core/session.php';
    require_once __DIR__ . '/../../core/functions.php';
    require_once __DIR__ . '/../../core/auth.php';

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

    $errorRedirect = ROUTE_SETTINGS . '?modal=update-profile';
    $length = static fn (string $value): int => function_exists('mb_strlen') ? mb_strlen($value) : strlen($value);

    $input = [
        'username' => trim((string) ($_POST['username'] ?? '')),
        'first_name' => trim((string) ($_POST['first_name'] ?? '')),
        'last_name' => trim((string) ($_POST['last_name'] ?? '')),
        'description' => trim((string) ($_POST['description'] ?? '')),
        'birth_date' => trim((string) ($_POST['birth_date'] ?? '')),
        'gender' => trim((string) ($_POST['gender'] ?? ''))];

    $persistOldInput = static function () use ($input): void{
        set_old_input([
            'settings_username' => $input['username'],
            'settings_first_name' => $input['first_name'],
            'settings_last_name' => $input['last_name'],
            'settings_description' => $input['description'],
            'settings_birth_date' => $input['birth_date'],
            'settings_gender' => $input['gender']]);
    };

    $errors = [];

    if ($input['username'] === ''){
        $errors['username'] = 'Укажите username.';
    }else{
        $usernameLength = $length($input['username']);
        if ($usernameLength < USERNAME_MIN_LENGTH || $usernameLength > USERNAME_MAX_LENGTH){
            $errors['username'] = 'Username должен содержать от ' . USERNAME_MIN_LENGTH . ' до ' . USERNAME_MAX_LENGTH . ' символов.';
        }elseif (!preg_match('/^[A-Za-z0-9_]+$/', $input['username'])){
            $errors['username'] = 'Username может содержать только латиницу, цифры и _.';
        }
    }

    if ($input['first_name'] === ''){
        $errors['first_name'] = 'Укажите имя.';
    }else{
        $firstNameLength = $length($input['first_name']);
        if ($firstNameLength < NAME_MIN_LENGTH || $firstNameLength > NAME_MAX_LENGTH){
            $errors['first_name'] = 'Имя должно содержать от ' . NAME_MIN_LENGTH . ' до ' . NAME_MAX_LENGTH . ' символов.';
        }
    }

    if ($input['last_name'] === ''){
        $errors['last_name'] = 'Укажите фамилию.';
    }else{
        $lastNameLength = $length($input['last_name']);
        if ($lastNameLength < NAME_MIN_LENGTH || $lastNameLength > NAME_MAX_LENGTH){
            $errors['last_name'] = 'Фамилия должна содержать от ' . NAME_MIN_LENGTH . ' до ' . NAME_MAX_LENGTH . ' символов.';
        }
    }

    if ($input['description'] !== '' && $length($input['description']) > 1000){
        $errors['description'] = 'Описание профиля не должно превышать 1000 символов.';
    }

    $gender = null;
    if ($input['gender'] !== ''){
        if (in_array($input['gender'], ['male', 'female'], true)){
            $gender = $input['gender'];
        }else{
            $errors['gender'] = 'Укажите корректный пол.';
        }
    }

    $birthDate = null;
    if ($input['birth_date'] !== ''){
        $parsedBirthDate = DateTime::createFromFormat('Y-m-d', $input['birth_date']);
        $isValidBirthDate = $parsedBirthDate instanceof DateTime
            && $parsedBirthDate -> format('Y-m-d') === $input['birth_date'];

        if (!$isValidBirthDate){
            $errors['birth_date'] = 'Укажите корректную дату рождения.';
        }else{
            $today = new DateTime('today');
            if ($parsedBirthDate > $today){
                $errors['birth_date'] = 'Дата рождения не может быть в будущем.';
            }else{
                $birthDate = $parsedBirthDate -> format('Y-m-d');
            }
        }
    }

    try{
        $pdo = db();
    }catch (Throwable $exception){
        set_flash_message('error', 'Не удалось обновить данные профиля: ошибка подключения к базе данных.');
        $errors['profile'] = 'Ошибка подключения к базе данных.';
        set_validation_errors($errors);
        $persistOldInput();
        safe_redirect($errorRedirect);
    }

    try{
        $userStatement = $pdo -> prepare(
            'SELECT id, avatar
             FROM users
             WHERE id = :id
             LIMIT 1'
        );
        $userStatement -> execute(['id' => $userId]);
        $userData = $userStatement -> fetch();

        if (!is_array($userData)){
            $errors['profile'] = 'Пользователь не найден.';
        }else{
            $currentAvatar = trim((string) ($userData['avatar'] ?? ''));
            if ($currentAvatar === ''){
                $errors['avatar'] = 'Добавьте аватарку профиля.';
            }
        }

        if (!isset($errors['username'])){
            $usernameStatement = $pdo -> prepare(
                'SELECT id
                 FROM users
                 WHERE username = :username
                   AND id <> :id
                 LIMIT 1'
            );
            $usernameStatement -> execute([
                'username' => $input['username'],
                'id' => $userId,
            ]);

            if ($usernameStatement -> fetch()){
                $errors['username'] = 'Этот username уже занят.';
            }
        }
    }catch (Throwable $exception){
        $errors['profile'] = 'Не удалось проверить данные профиля.';
    }

    if ($errors !== []){
        set_flash_message('error', 'Не удалось обновить данные пользователя. Проверьте заполнение формы.');
        set_validation_errors($errors);
        $persistOldInput();
        safe_redirect($errorRedirect);
    }

    try{
        $updateStatement = $pdo->prepare(
            'UPDATE users
             SET username = :username,
                 first_name = :first_name,
                 last_name = :last_name,
                 description = :description,
                 gender = :gender,
                 birth_date = :birth_date
             WHERE id = :id
             LIMIT 1'
        );

        $updateStatement -> execute([
            'username' => $input['username'],
            'first_name' => $input['first_name'],
            'last_name' => $input['last_name'],
            'description' => $input['description'] !== '' ? $input['description'] : null,
            'gender' => $gender,
            'birth_date' => $birthDate,
            'id' => $userId,
        ]);

        $authPayload = auth_user() ?? [];
        $authPayload['id'] = $userId;
        $authPayload['username'] = $input['username'];
        $authPayload['first_name'] = $input['first_name'];
        $authPayload['last_name'] = $input['last_name'];
        session_set(AUTH_SESSION_KEY, $authPayload);
        session_set('username', $input['username']);

        clear_old_input();
        set_validation_errors([]);
        set_flash_message('success', 'Данные профиля успешно обновлены.');
        safe_redirect(ROUTE_SETTINGS);
    }catch (Throwable $exception){
        set_flash_message('error', 'Не удалось обновить данные профиля.');
        set_validation_errors(['profile' => 'Не удалось обновить данные профиля. Попробуйте еще раз.']);
        $persistOldInput();
        safe_redirect($errorRedirect);
    }
?>   