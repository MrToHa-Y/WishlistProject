<?php

    declare(strict_types=1);

    require_once __DIR__ . '/../../config/constants.php';
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../core/session.php';
    require_once __DIR__ . '/../../core/functions.php';
    require_once __DIR__ . '/../../core/auth.php';

    require_auth(make_route_with_redirect(ROUTE_LOGIN, ROUTE_PROFILE));

    if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'POST'){
        safe_redirect(ROUTE_PROFILE);
    }

    $redirectTo = normalize_redirect_path((string) ($_POST['redirect_to'] ?? ROUTE_PROFILE));
    if ($redirectTo === ''){
        $redirectTo = ROUTE_PROFILE;
    }

    $user = auth_user();
    $userId = (int) ($user['id'] ?? 0);
    $wishId = filter_var($_POST['wish_id'] ?? null, FILTER_VALIDATE_INT);

    if ($userId <= 0 || !is_int($wishId) || $wishId <= 0){
        set_flash_message('error', 'Не удалось обновить желание: неверный идентификатор.');
        safe_redirect($redirectTo);
    }

    $title = trim((string) ($_POST['title'] ?? ''));
    $description = trim((string) ($_POST['description'] ?? ''));
    $productLink = trim((string) ($_POST['product_link'] ?? ''));
    $priceRaw = trim((string) ($_POST['price'] ?? ''));

    $isValidProductLink = static function (string $value): bool{
        $value = trim($value);
        if ($value === ''){
            return true;
        }

        if (str_starts_with($value, '/')){
            return true;
        }

        if (filter_var($value, FILTER_VALIDATE_URL) === false){
            return false;
        }

        $scheme = strtolower((string) parse_url($value, PHP_URL_SCHEME));
        return $scheme === 'http' || $scheme === 'https';
    };

    $errors = [];

    if ($title === ''){
        $errors[] = 'Укажите название желания.';
    }elseif ((function_exists('mb_strlen') ? mb_strlen($title) : strlen($title)) > 255){
        $errors[] = 'Название не должно превышать 255 символов.';
    }

    if (!$isValidProductLink($productLink)){
        $errors[] = 'Укажите корректную ссылку на товар.';
    }

    $normalizedPrice = str_replace([' ', ','], ['', '.'], $priceRaw);
    $priceValue = is_numeric($normalizedPrice) ? (float) $normalizedPrice : null;

    if ($priceRaw === '' || $priceValue === null || $priceValue < 0){
        $errors[] = 'Укажите корректную цену.';
    }

    $uploadedImage = $_FILES['image'] ?? null;
    $hasNewImage = is_array($uploadedImage) && isset($uploadedImage['error']) && (int) $uploadedImage['error'] !== UPLOAD_ERR_NO_FILE;
    $newImagePath = '';
    $newImageAbsolutePath = '';

    try{
        $pdo = db();

        $wishStatement = $pdo -> prepare(
            'SELECT id, image
             FROM wishes
             WHERE id = :id
               AND user_id = :user_id
             LIMIT 1'
        );
        $wishStatement -> execute([
            'id' => $wishId,
            'user_id' => $userId,
        ]);
        $wish = $wishStatement -> fetch();

        if (!is_array($wish)){
            set_flash_message('error', 'Желание не найдено.');
            safe_redirect($redirectTo);
        }

        $currentImage = (string) ($wish['image'] ?? '');

        if ($hasNewImage){
            $uploadError = (int) $uploadedImage['error'];
            if ($uploadError !== UPLOAD_ERR_OK){
                $errors[] = 'Не удалось загрузить новое изображение.';
            }else{
                $fileSize = (int) ($uploadedImage['size'] ?? 0);
                if ($fileSize <= 0 || $fileSize > WISH_IMAGE_MAX_SIZE_BYTES) {
                    $errors[] = 'Размер изображения должен быть от 1 байта до 8MB.';
                }

                $tmpPath = (string) ($uploadedImage['tmp_name'] ?? '');
                $mimeType = '';

                if ($tmpPath !== '' && is_file($tmpPath)){
                    if (function_exists('finfo_open')){
                        $finfo = finfo_open(FILEINFO_MIME_TYPE);
                        if ($finfo !== false){
                            $detected = finfo_file($finfo, $tmpPath);
                            finfo_close($finfo);
                            if (is_string($detected)){
                                $mimeType = $detected;
                            }
                        }
                    }

                    if ($mimeType === '' && function_exists('mime_content_type')){
                        $detected = mime_content_type($tmpPath);
                        if (is_string($detected)){
                            $mimeType = $detected;
                        }
                    }
                }

                $extension = $mimeType !== '' ? (WISH_ALLOWED_MIME_TYPES[$mimeType] ?? null) : null;
                if ($extension === null){
                    $errors[] = 'Разрешены только JPG, PNG и WEBP.';
                }
            }
        }

        if ($errors !== []){
            set_flash_message('error', 'Не удалось обновить желание. Проверьте корректность заполнения полей.');
            safe_redirect($redirectTo);
        }

        if ($hasNewImage){
            if (!is_dir(WISH_UPLOADS_DIR)){
                if (!mkdir(WISH_UPLOADS_DIR, 0775, true) && !is_dir(WISH_UPLOADS_DIR)){
                    set_flash_message('error', 'Не удалось обновить желание: ошибка при подготовке папки для изображения.');
                    safe_redirect($redirectTo);
                }
            }

            $tmpPath = (string) $uploadedImage['tmp_name'];
            $mimeType = '';

            if (function_exists('finfo_open')){
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                if ($finfo !== false){
                    $detected = finfo_file($finfo, $tmpPath);
                    finfo_close($finfo);
                    if (is_string($detected)){
                        $mimeType = $detected;
                    }
                }
            }

            if ($mimeType === '' && function_exists('mime_content_type')){
                $detected = mime_content_type($tmpPath);
                if (is_string($detected)){
                    $mimeType = $detected;
                }
            }

            $extension = WISH_ALLOWED_MIME_TYPES[$mimeType] ?? null;
            if ($extension === null){
                set_flash_message('error', 'Не удалось обновить желание: формат изображения не поддерживается.');
                safe_redirect($redirectTo);
            }

            $fileName = 'wish_' . $userId . '_' . bin2hex(random_bytes(12)) . '.' . $extension;
            $newImagePath = rtrim(WISH_UPLOADS_WEB_PATH, '/') . '/' . $fileName;
            $newImageAbsolutePath = rtrim(WISH_UPLOADS_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileName;

            if (!move_uploaded_file($tmpPath, $newImageAbsolutePath)){
                set_flash_message('error', 'Не удалось обновить желание: не удалось загрузить изображение.');
                safe_redirect($redirectTo);
            }
        }

        $finalImagePath = $newImagePath !== '' ? $newImagePath : $currentImage;

        $updateStatement = $pdo -> prepare(
            'UPDATE wishes
             SET title = :title,
                 description = :description,
                 image = :image,
                 product_link = :product_link,
                 price = :price
             WHERE id = :id
               AND user_id = :user_id'
        );

        $updateStatement -> execute([
            'title' => $title,
            'description' => $description !== '' ? $description : null,
            'image' => $finalImagePath,
            'product_link' => $productLink !== '' ? $productLink : null,
            'price' => $priceValue ?? 0,
            'id' => $wishId,
            'user_id' => $userId,
        ]);

        if ($newImagePath !== '' && $currentImage !== ''){
            $webPrefix = rtrim(WISH_UPLOADS_WEB_PATH, '/') . '/';
            if (str_starts_with($currentImage, $webPrefix)){
                $oldFileName = basename($currentImage);
                if ($oldFileName !== '') {
                    $oldAbsolutePath = rtrim(WISH_UPLOADS_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $oldFileName;
                    if (is_file($oldAbsolutePath)) {
                        @unlink($oldAbsolutePath);
                    }
                }
            }
        }

        set_flash_message('success', 'Желание успешно обновлено.');
        safe_redirect($redirectTo);
    }catch (Throwable $exception){
        if ($newImageAbsolutePath !== '' && is_file($newImageAbsolutePath)){
            @unlink($newImageAbsolutePath);
        }

        set_flash_message('error', 'Не удалось обновить желание.');
        safe_redirect($redirectTo);
    }
?>