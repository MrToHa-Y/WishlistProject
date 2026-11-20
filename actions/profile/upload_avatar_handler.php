<?php

    declare(strict_types=1);

    require_once __DIR__ . '/../../config/constants.php';
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../core/session.php';
    require_once __DIR__ . '/../../core/functions.php';
    require_once __DIR__ . '/../../core/auth.php';

    if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'POST'){
        safe_redirect(ROUTE_SETTINGS);
    }

    require_auth(make_route_with_redirect(ROUTE_LOGIN, ROUTE_SETTINGS));

    $user = auth_user();
    $userId = (int) ($user['id'] ?? 0);
    if ($userId <= 0){
        safe_redirect(ROUTE_SETTINGS);
    }

    $errorRedirect = ROUTE_SETTINGS . '?modal=update-profile';
    $action = trim((string) ($_POST['avatar_action'] ?? 'upload'));
    if (!in_array($action, ['upload', 'delete'], true)){
        $action = 'upload';
    }

    try{
        $pdo = db();
    }catch (Throwable $exception){
        set_validation_errors(['avatar' => 'Ошибка подключения к базе данных.']);
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
            set_validation_errors(['avatar' => 'Пользователь не найден.']);
            safe_redirect($errorRedirect);
        }

        $currentAvatar = trim((string) ($userData['avatar'] ?? ''));
    }catch (Throwable $exception){
        set_validation_errors(['avatar' => 'Не удалось получить данные профиля.']);
        safe_redirect($errorRedirect);
    }

    $deleteOldAvatar = static function (string $avatarPath): void{
        if ($avatarPath === ''){
            return;
        }

        $webPrefix = rtrim(AVATAR_UPLOADS_WEB_PATH, '/') . '/';
        if (!str_starts_with($avatarPath, $webPrefix)){
            return;
        }

        $fileName = basename($avatarPath);
        if ($fileName === ''){
            return;
        }

        $absolutePath = rtrim(AVATAR_UPLOADS_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileName;
        if (is_file($absolutePath)){
            @unlink($absolutePath);
        }
    };

    if ($action === 'delete'){
        try{
            $deleteStatement = $pdo -> prepare(
                'UPDATE users
                 SET avatar = NULL
                 WHERE id = :id
                 LIMIT 1'
            );
            $deleteStatement -> execute(['id' => $userId]);

            $deleteOldAvatar($currentAvatar);
            set_validation_errors([]);
            safe_redirect($errorRedirect);
        }catch (Throwable $exception){
            set_validation_errors(['avatar' => 'Не удалось удалить аватарку.']);
            safe_redirect($errorRedirect);
        }
    }

    $uploadedAvatar = $_FILES['avatar'] ?? null;
    if (!is_array($uploadedAvatar) || !isset($uploadedAvatar['error'])){
        set_validation_errors(['avatar' => 'Выберите изображение для загрузки.']);
        safe_redirect($errorRedirect);
    }

    $uploadError = (int) $uploadedAvatar['error'];
    if ($uploadError !== UPLOAD_ERR_OK){
        set_validation_errors(['avatar' => 'Ошибка загрузки изображения.']);
        safe_redirect($errorRedirect);
    }

    $fileSize = (int) ($uploadedAvatar['size'] ?? 0);
    if ($fileSize <= 0 || $fileSize > AVATAR_IMAGE_MAX_SIZE_BYTES){
        set_validation_errors(['avatar' => 'Размер изображения должен быть от 1 байта до 4MB.']);
        safe_redirect($errorRedirect);
    }

    $tmpPath = (string) ($uploadedAvatar['tmp_name'] ?? '');
    $mimeType = '';

    if ($tmpPath !== '' && is_file($tmpPath) && function_exists('finfo_open')){
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo !== false) {
            $detected = finfo_file($finfo, $tmpPath);
            finfo_close($finfo);
            if (is_string($detected)) {
                $mimeType = $detected;
            }
        }
    }

    if ($mimeType === '' && $tmpPath !== '' && is_file($tmpPath) && function_exists('mime_content_type')){
        $detected = mime_content_type($tmpPath);
        if (is_string($detected)) {
            $mimeType = $detected;
        }
    }

    $extension = AVATAR_ALLOWED_MIME_TYPES[$mimeType] ?? null;
    if ($extension === null){
        set_validation_errors(['avatar' => 'Разрешены только JPG, PNG и WEBP.']);
        safe_redirect($errorRedirect);
    }

    if (!is_dir(AVATAR_UPLOADS_DIR)){
        if (!mkdir(AVATAR_UPLOADS_DIR, 0775, true) && !is_dir(AVATAR_UPLOADS_DIR)){
            set_validation_errors(['avatar' => 'Не удалось создать папку uploads/avatar.']);
            safe_redirect($errorRedirect);
        }
    }

    try{
        $fileName = 'avatar_' . $userId . '_' . bin2hex(random_bytes(12)) . '.' . $extension;
    }catch (Throwable $exception){
        set_validation_errors(['avatar' => 'Не удалось сгенерировать имя файла.']);
        safe_redirect($errorRedirect);
    }

    $newAvatarWebPath = rtrim(AVATAR_UPLOADS_WEB_PATH, '/') . '/' . $fileName;
    $newAvatarAbsolutePath = rtrim(AVATAR_UPLOADS_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileName;

    if (!move_uploaded_file($tmpPath, $newAvatarAbsolutePath)){
        set_validation_errors(['avatar' => 'Не удалось сохранить аватарку.']);
        safe_redirect($errorRedirect);
    }

    try{
        $updateStatement = $pdo -> prepare(
            'UPDATE users
             SET avatar = :avatar
             WHERE id = :id
             LIMIT 1'
        );
        $updateStatement -> execute([
            'avatar' => $newAvatarWebPath,
            'id' => $userId,
        ]);

        $deleteOldAvatar($currentAvatar);
        set_validation_errors([]);
        safe_redirect($errorRedirect);
    }catch (Throwable $exception){
        if (is_file($newAvatarAbsolutePath)){
            @unlink($newAvatarAbsolutePath);
        }

        set_validation_errors(['avatar' => 'Не удалось обновить аватарку.']);
        safe_redirect($errorRedirect);
    }
?>