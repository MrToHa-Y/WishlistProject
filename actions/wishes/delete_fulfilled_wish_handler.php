<?php

    declare(strict_types=1);

    require_once __DIR__ . '/../../config/constants.php';
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../core/session.php';
    require_once __DIR__ . '/../../core/functions.php';
    require_once __DIR__ . '/../../core/auth.php';

    if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'POST'){
        safe_redirect(ROUTE_FULFILLED);
    }

    $redirectTo = normalize_redirect_path((string) ($_POST['redirect_to'] ?? ''));
    if ($redirectTo === ''){
        $redirectTo = ROUTE_FULFILLED;
    }

    require_auth(make_route_with_redirect(ROUTE_LOGIN, $redirectTo));

    $fulfilledId = filter_var($_POST['fulfilled_id'] ?? ($_POST['wish_id'] ?? null), FILTER_VALIDATE_INT);
    if (!is_int($fulfilledId) || $fulfilledId <= 0){
        set_flash_message('error', 'Не удалось удалить желание из исполненных: неверный идентификатор.');
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

        $fulfilledStatement = $pdo -> prepare(
            'SELECT id, user_id, title, description, image, product_link, price, is_active
             FROM fulfilled_wishes
             WHERE id = :id
               AND user_id = :user_id
               AND is_active = 1
             LIMIT 1
             FOR UPDATE'
        );
        $fulfilledStatement -> execute([
            'id' => $fulfilledId,
            'user_id' => $userId,
        ]);
        $fulfilledWish = $fulfilledStatement -> fetch();

        if (!is_array($fulfilledWish)){
            throw new RuntimeException('Исполненное желание не найдено.');
        }

        $insertStatement = $pdo -> prepare(
            'INSERT INTO wishes (
                 user_id,
                 title,
                 description,
                 image,
                 product_link,
                 price,
                 is_active
             ) VALUES (
                 :user_id,
                 :title,
                 :description,
                 :image,
                 :product_link,
                 :price,
                 :is_active
             )'
        );
        $insertStatement -> execute([
            'user_id' => (int) ($fulfilledWish['user_id'] ?? $userId),
            'title' => trim((string) ($fulfilledWish['title'] ?? '')),
            'description' => ($fulfilledWish['description'] ?? null) !== null ? trim((string) $fulfilledWish['description']) : null,
            'image' => trim((string) ($fulfilledWish['image'] ?? '')),
            'product_link' => ($fulfilledWish['product_link'] ?? null) !== null ? trim((string) $fulfilledWish['product_link']) : null,
            'price' => (float) ($fulfilledWish['price'] ?? 0),
            'is_active' => 1,
        ]);

        $deleteStatement = $pdo -> prepare(
            'DELETE FROM fulfilled_wishes
             WHERE id = :id
               AND user_id = :user_id'
        );
        $deleteStatement -> execute([
            'id' => $fulfilledId,
            'user_id' => $userId,
        ]);

        $pdo -> commit();
        set_flash_message('success', 'Желание удалено из исполненных и возвращено в профиль.');
        safe_redirect($redirectTo);
    }catch (Throwable $exception){
        if ($pdo instanceof PDO && $pdo -> inTransaction()){
            $pdo -> rollBack();
        }

        set_flash_message('error', 'Не удалось вернуть желание в список профиля.');
        safe_redirect($redirectTo);
    }
?>