<?php

    declare(strict_types=1);

    require_once __DIR__ . '/../../config/constants.php';
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../core/session.php';
    require_once __DIR__ . '/../../core/functions.php';
    require_once __DIR__ . '/../../core/auth.php';
    require_once __DIR__ . '/../../core/csrf.php';

    if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'POST'){
        safe_redirect(ROUTE_SEARCH);
    }

    $redirectTo = normalize_redirect_path((string) ($_POST['redirect_to'] ?? ''));
    if ($redirectTo === ''){
        $redirectTo = ROUTE_SEARCH;
    }

    require_auth(make_route_with_redirect(ROUTE_LOGIN, $redirectTo));
    csrf_validate_request_or_fail($redirectTo, 'Ошибка безопасности при бронировании. Обновите страницу и попробуйте снова.');

    $wishId = filter_var($_POST['wish_id'] ?? null, FILTER_VALIDATE_INT);
    if (!is_int($wishId) || $wishId <= 0){
        set_flash_message('error', 'Не удалось забронировать желание: неверный идентификатор.');
        safe_redirect($redirectTo);
    }

    $user = auth_user();
    $userId = (int) ($user['id'] ?? 0);
    if ($userId <= 0){
        set_flash_message('error', 'Не удалось определить текущего пользователя.');
        safe_redirect($redirectTo);
    }

    $pdo = null;

    try{
        $pdo = db();
        $pdo -> beginTransaction();

        $wishStatement = $pdo -> prepare(
            'SELECT id, user_id
             FROM wishes
             WHERE id = :id
               AND is_active = 1
             LIMIT 1
             FOR UPDATE'
        );
        $wishStatement -> execute(['id' => $wishId]);
        $wish = $wishStatement -> fetch();

        if (!is_array($wish)){
            throw new RuntimeException('Желание не найдено или уже недоступно для бронирования.');
        }

        $wishOwnerId = (int) ($wish['user_id'] ?? 0);
        if ($wishOwnerId <= 0){
            throw new RuntimeException('Не удалось определить владельца желания.');
        }

        if ($wishOwnerId === $userId){
            throw new RuntimeException('Нельзя забронировать собственное желание.');
        }

        $cleanupExpiredStatement = $pdo -> prepare(
            'DELETE FROM wish_reservations
             WHERE wish_id = :wish_id
               AND created_at < (NOW() - INTERVAL 90 DAY)'
        );
        $cleanupExpiredStatement -> execute(['wish_id' => $wishId]);

        $existingReservationStatement = $pdo -> prepare(
            'SELECT id, reserved_by_user_id
             FROM wish_reservations
             WHERE wish_id = :wish_id
             LIMIT 1
             FOR UPDATE'
        );
        $existingReservationStatement -> execute(['wish_id' => $wishId]);
        $existingReservation = $existingReservationStatement -> fetch();

        if (is_array($existingReservation)){
            $reservedByUserId = (int) ($existingReservation['reserved_by_user_id'] ?? 0);
            if ($reservedByUserId === $userId){
                throw new RuntimeException('Это желание уже забронировано вами.');
            }

            throw new RuntimeException('Это желание уже забронировано другим пользователем.');
        }

        $insertStatement = $pdo -> prepare(
            'INSERT INTO wish_reservations (
                 wish_id,
                 wish_owner_id,
                 reserved_by_user_id
             ) VALUES (
                 :wish_id,
                 :wish_owner_id,
                 :reserved_by_user_id
             )'
        );
        $insertStatement -> execute([
            'wish_id' => $wishId,
            'wish_owner_id' => $wishOwnerId,
            'reserved_by_user_id' => $userId,
        ]);

        $pdo -> commit();
        set_flash_message('success', 'Желание успешно забронировано.');
        safe_redirect($redirectTo);
    }catch (Throwable $exception){
        if ($pdo instanceof PDO && $pdo -> inTransaction()){
            $pdo -> rollBack();
        }

        $message = trim((string) $exception -> getMessage());
        if ($message === ''){
            $message = 'Не удалось забронировать желание. Попробуйте еще раз.';
        }

        set_flash_message('error', $message);
        safe_redirect($redirectTo);
    }
?>