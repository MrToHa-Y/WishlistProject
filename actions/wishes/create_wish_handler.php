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
    
    $user = auth_user();
    $userId = (int) ($user['id'] ?? 0);
    
    $title = trim((string) ($_POST['title'] ?? ''));
    $description = trim((string) ($_POST['description'] ?? ''));
    $productLink = trim((string) ($_POST['product_link'] ?? ''));
    $priceRaw = trim((string) ($_POST['price'] ?? ''));

    $priceNormalized = str_replace([' ', ','], ['', '.'], $priceRaw);
    $priceValue = is_numeric($priceNormalized) ? (float) $priceNormalized : null;

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
    
    $setOldWishInput = static function () use ($title, $description, $productLink, $priceRaw): void{
        set_old_input([
            'wish_title' => $title,
            'wish_description' => $description,
            'wish_product_link' => $productLink,
            'wish_price' => $priceRaw]);
    };
    
    $errors = [];
    
    if ($title === ''){
        $errors['title'] = 'Укажите название желания.';
    }elseif ((function_exists('mb_strlen') ? mb_strlen($title) : strlen($title)) > 255){
        $errors['title'] = 'Название не должно превышать 255 символов.';
    }
    
    if (!$isValidProductLink($productLink)){
        $errors['product_link'] = 'Укажите корректную ссылку. Допустимо: https://site.com/product или /pages/error.php';
    }
    
    if ($priceRaw === ''){
        $errors['price'] = 'Укажите цену.';
    }elseif ($priceValue === null || $priceValue < 0){
        $errors['price'] = 'Цена должна быть числом больше или равна 0.';
    }
    
    $uploadedImage = $_FILES['image'] ?? null;
    if (!is_array($uploadedImage) || !isset($uploadedImage['error'])) {
        $errors['image'] = 'Добавьте изображение желания.';
    }else{
        $uploadError = (int) $uploadedImage['error'];
    
        if ($uploadError === UPLOAD_ERR_NO_FILE){
            $errors['image'] = 'Изображение не выбрано.';
        }elseif ($uploadError !== UPLOAD_ERR_OK){
            $uploadErrorMap = [
                UPLOAD_ERR_INI_SIZE => 'Файл больше лимита upload_max_filesize в PHP.',
                UPLOAD_ERR_FORM_SIZE => 'Файл больше лимита, указанного в форме.',
                UPLOAD_ERR_PARTIAL => 'Файл загружен частично. Попробуйте еще раз.',
                UPLOAD_ERR_NO_TMP_DIR => 'На сервере отсутствует временная папка для загрузки.',
                UPLOAD_ERR_CANT_WRITE => 'Сервер не может записать файл на диск.',
                UPLOAD_ERR_EXTENSION => 'Загрузка файла остановлена расширением PHP.',
            ];
            $errors['image'] = $uploadErrorMap[$uploadError] ?? ('Ошибка загрузки файла. Код: ' . $uploadError);
        }else{
            $fileSize = (int) ($uploadedImage['size'] ?? 0);
            if ($fileSize <= 0){
                $errors['image'] = 'Файл изображения пустой.';
            }elseif ($fileSize > WISH_IMAGE_MAX_SIZE_BYTES){
                $errors['image'] = 'Размер изображения превышает 8MB.';
            }else{
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
    
                if ($mimeType === '' || !array_key_exists($mimeType, WISH_ALLOWED_MIME_TYPES)){
                    $errors['image'] = 'Разрешены только JPG, PNG и WEBP.';
                }
            }
        }
    }
    
    if ($errors !== []){
        set_validation_errors($errors);
        $setOldWishInput();
        safe_redirect(ROUTE_PROFILE);
    }
    
    $savedRelativePath = '';
    $savedAbsolutePath = '';
    
    try{
        if (!is_dir(WISH_UPLOADS_DIR)){
            if (!mkdir(WISH_UPLOADS_DIR, 0775, true) && !is_dir(WISH_UPLOADS_DIR)){
                throw new RuntimeException('Не удалось создать папку uploads/wishes для загрузки изображений.');
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
            throw new RuntimeException('Неверный формат изображения после проверки на сервере.');
        }
    
        $fileName = 'wish_' . $userId . '_' . bin2hex(random_bytes(12)) . '.' . $extension;
        $savedRelativePath = rtrim(WISH_UPLOADS_WEB_PATH, '/') . '/' . $fileName;
        $savedAbsolutePath = rtrim(WISH_UPLOADS_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileName;
    
        if (!move_uploaded_file($tmpPath, $savedAbsolutePath)){
            throw new RuntimeException('Сервер не смог сохранить файл изображения.');
        }
    
        $pdo = db();
        $insertStatement = $pdo -> prepare(
            'INSERT INTO wishes (user_id, title, description, image, product_link, price, is_active)
             VALUES (:user_id, :title, :description, :image, :product_link, :price, :is_active)');
        $insertStatement -> execute([
            'user_id' => $userId,
            'title' => $title,
            'description' => $description !== '' ? $description : null,
            'image' => $savedRelativePath,
            'product_link' => $productLink !== '' ? $productLink : null,
            'price' => $priceValue ?? 0,
            'is_active' => 1]);
    
        clear_old_input();
        set_validation_errors([]);
        set_flash_message('success', 'Желание успешно создано.');
        safe_redirect(ROUTE_PROFILE);
    }catch (Throwable $exception){
        if ($savedAbsolutePath !== '' && is_file($savedAbsolutePath)){
            @unlink($savedAbsolutePath);
        }
    
        $reason = 'Неизвестная ошибка на сервере.';
        if ($exception instanceof PDOException){
            $dbMessage = (string) $exception -> getMessage();
            if (stripos($dbMessage, 'Base table or view not found') !== false || stripos($dbMessage, 'wishes') !== false){
                $reason = 'В базе данных отсутствует таблица wishes. Примените обновленный database/schema.sql.';
            }elseif (stripos($dbMessage, 'Unknown column') !== false){
                $reason = 'Структура таблицы wishes устарела. Обновите схему базы данных.';
            }else{
                $reason = 'Ошибка базы данных при сохранении желания.';
            }
        }elseif ($exception instanceof RuntimeException){
            $reason = $exception -> getMessage();
        }
    
        set_validation_errors(['wish' => $reason]);
        $setOldWishInput();
        safe_redirect(ROUTE_PROFILE);
    }
?>