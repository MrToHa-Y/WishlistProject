<?php

    declare(strict_types=1);

    require_once __DIR__ . '/../../config/constants.php';
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../core/session.php';
    require_once __DIR__ . '/../../core/functions.php';
    require_once __DIR__ . '/../../core/auth.php';

    if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? '')) !== 'POST'){
        safe_redirect(ROUTE_HOME);
    }

    $redirectTo = normalize_redirect_path((string) ($_POST['redirect_to'] ?? ''));
    if ($redirectTo === ''){
        $redirectTo = ROUTE_HOME;
    }

    if (!is_authenticated()){
        set_flash_message('warning', 'Чтобы сохранить желание, войдите в аккаунт.');
        safe_redirect(make_route_with_redirect(ROUTE_LOGIN, $redirectTo));
    }

    $itemId = filter_var($_POST['item_id'] ?? null, FILTER_VALIDATE_INT);
    if (!is_int($itemId) || $itemId <= 0){
        set_flash_message('error', 'Не удалось сохранить желание: неверный идентификатор карточки.');
        safe_redirect($redirectTo);
    }

    $user = auth_user();
    $userId = (int) (($user['id'] ?? 0));
    if ($userId <= 0){
        set_flash_message('error', 'Не удалось определить пользователя для сохранения желания.');
        safe_redirect($redirectTo);
    }

    try{
        $pdo = db();

        $catalogStatement = $pdo -> prepare(
            'SELECT id, title, description, image, product_link, price
             FROM catalog_items
             WHERE id = :id
               AND is_active = 1
             LIMIT 1'
        );
        $catalogStatement -> execute(['id' => $itemId]);
        $catalogItem = $catalogStatement -> fetch();

        if (!is_array($catalogItem)){
            set_flash_message('error', 'Не удалось сохранить желание: карточка не найдена.');
            safe_redirect($redirectTo);
        }

        $title = trim((string) ($catalogItem['title'] ?? ''));
        $image = trim((string) ($catalogItem['image'] ?? ''));
        $description = trim((string) ($catalogItem['description'] ?? ''));
        $productLink = trim((string) ($catalogItem['product_link'] ?? ''));
        $price = (float) ($catalogItem['price'] ?? 0);

        if ($title === '' || $image === ''){
            set_flash_message('error', 'Не удалось сохранить желание: в карточке не хватает обязательных данных.');
            safe_redirect($redirectTo);
        }

        $fulfilledDuplicateStatement = $pdo -> prepare(
            'SELECT id
             FROM fulfilled_wishes
             WHERE user_id = :user_id
               AND is_active = 1
               AND title = :title
               AND image = :image
               AND COALESCE(product_link, "") = :product_link
             LIMIT 1'
        );
        $fulfilledDuplicateStatement -> execute([
            'user_id' => $userId,
            'title' => $title,
            'image' => $image,
            'product_link' => $productLink,
        ]);

        if ($fulfilledDuplicateStatement -> fetchColumn() !== false){
            set_flash_message('warning', 'Это желание уже добавлено в ваш профиль и находится в исполненных желаниях.');
            safe_redirect($redirectTo);
        }

        $duplicateStatement = $pdo -> prepare(
            'SELECT id
             FROM wishes
             WHERE user_id = :user_id
               AND title = :title
               AND image = :image
               AND COALESCE(product_link, "") = :product_link
             LIMIT 1'
        );
        $duplicateStatement -> execute([
            'user_id' => $userId,
            'title' => $title,
            'image' => $image,
            'product_link' => $productLink,
        ]);

        if ($duplicateStatement -> fetchColumn() !== false){
            set_flash_message('info', 'Это желание уже добавлено в ваш профиль.');
            safe_redirect($redirectTo);
        }

        $insertStatement = $pdo -> prepare(
            'INSERT INTO wishes (user_id, title, description, image, product_link, price, is_active)
             VALUES (:user_id, :title, :description, :image, :product_link, :price, :is_active)'
        );
        $insertStatement -> execute([
            'user_id' => $userId,
            'title' => $title,
            'description' => $description !== '' ? $description : null,
            'image' => $image,
            'product_link' => $productLink !== '' ? $productLink : null,
            'price' => $price,
            'is_active' => 1,
        ]);

        set_flash_message('success', 'Желание добавлено в ваш профиль.');
        safe_redirect($redirectTo);
    }catch (Throwable $exception){
        set_flash_message('error', 'Не удалось сохранить желание: ошибка базы данных.');
        safe_redirect($redirectTo);
    }
?>