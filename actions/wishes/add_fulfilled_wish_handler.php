<?php

    declare(strict_types=1);

    require_once __DIR__ . '/../../config/constants.php';
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../core/session.php';
    require_once __DIR__ . '/../../core/functions.php';
    require_once __DIR__ . '/../../core/auth.php';

    if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'POST'){
        safe_redirect(ROUTE_PROFILE);
    }

    $redirectTo = normalize_redirect_path((string) ($_POST['redirect_to'] ?? ''));
    if ($redirectTo === ''){
        $redirectTo = ROUTE_PROFILE;
    }

    require_auth(make_route_with_redirect(ROUTE_LOGIN, $redirectTo));

    $wishId = filter_var($_POST['wish_id'] ?? null, FILTER_VALIDATE_INT);
    if (!is_int($wishId) || $wishId <= 0){
        set_flash_message('error', 'Не удалось отметить желание как исполненное: неверный идентификатор.');
        safe_redirect($redirectTo);
    }

    $user = auth_user();
    $userId = (int) ($user['id'] ?? 0);
    if ($userId <= 0){
        set_flash_message('error', 'Не удалось определить пользователя.');
        safe_redirect($redirectTo);
    }

    $pdo = null;

    try{
        $pdo = db();
        $pdo -> beginTransaction();

        $wishStatement = $pdo -> prepare(
            'SELECT id, user_id, title, description, image, product_link, price, is_active
             FROM wishes
             WHERE id = :id
               AND user_id = :user_id
               AND is_active = 1
             LIMIT 1
             FOR UPDATE'
        );
        $wishStatement -> execute([
            'id' => $wishId,
            'user_id' => $userId,
        ]);
        $wish = $wishStatement -> fetch();

        if (!is_array($wish)){
            throw new RuntimeException('Желание не найдено или уже было перенесено.');
        }

        $insertStatement = $pdo -> prepare(
            'INSERT INTO fulfilled_wishes (
                 user_id,
                 source_wish_id,
                 title,
                 description,
                 image,
                 product_link,
                 price,
                 promo_text,
                 is_active
             ) VALUES (
                 :user_id,
                 :source_wish_id,
                 :title,
                 :description,
                 :image,
                 :product_link,
                 :price,
                 :promo_text,
                 :is_active
             )'
        );
        $insertStatement -> execute([
            'user_id' => (int) ($wish['user_id'] ?? $userId),
            'source_wish_id' => (int) ($wish['id'] ?? $wishId),
            'title' => trim((string) ($wish['title'] ?? '')),
            'description' => ($wish['description'] ?? null) !== null ? trim((string) $wish['description']) : null,
            'image' => trim((string) ($wish['image'] ?? '')),
            'product_link' => ($wish['product_link'] ?? null) !== null ? trim((string) $wish['product_link']) : null,
            'price' => (float) ($wish['price'] ?? 0),
            'promo_text' => null,
            'is_active' => 1,
        ]);

        $deleteStatement = $pdo -> prepare(
            'DELETE FROM wishes
             WHERE id = :id
               AND user_id = :user_id'
        );
        $deleteStatement -> execute([
            'id' => $wishId,
            'user_id' => $userId,
        ]);

        $pdo -> commit();
        set_flash_message('success', 'Желание добавлено в исполненные.');
        safe_redirect($redirectTo);
    }catch (Throwable $exception){
        if ($pdo instanceof PDO && $pdo->inTransaction()){
            $pdo -> rollBack();
        }

        set_flash_message('error', 'Не удалось перенести желание в исполненные.');
        safe_redirect($redirectTo);
    }
?>