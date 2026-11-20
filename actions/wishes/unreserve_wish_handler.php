<?php

    declare(strict_types=1);

    require_once __DIR__ . '/../../config/constants.php';
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../core/session.php';
    require_once __DIR__ . '/../../core/functions.php';
    require_once __DIR__ . '/../../core/auth.php';
    require_once __DIR__ . '/../../core/csrf.php';

    if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'POST'){
        safe_redirect(ROUTE_RESERVATIONS);
    }

    $redirectTo = normalize_redirect_path((string) ($_POST['redirect_to'] ?? ''));
    if ($redirectTo === ''){
        $redirectTo = ROUTE_RESERVATIONS;
    }

    require_auth(make_route_with_redirect(ROUTE_LOGIN, $redirectTo));
    csrf_validate_request_or_fail($redirectTo, 'Ошибка безопасности при отмене бронирования. Обновите страницу и попробуйте снова.');

    $wishId = filter_var($_POST['wish_id'] ?? null, FILTER_VALIDATE_INT);
    if (!is_int($wishId) || $wishId <= 0){
        set_flash_message('error', 'Не удалось отменить бронирование: неверный идентификатор желания.');
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

        $cleanupExpiredStatement = $pdo -> prepare(
            'DELETE FROM wish_reservations
             WHERE wish_id = :wish_id
               AND created_at < (NOW() - INTERVAL 90 DAY)'
        );
        $cleanupExpiredStatement -> execute(['wish_id' => $wishId]);

        $reservationStatement = $pdo -> prepare(
            'SELECT id, reserved_by_user_id
             FROM wish_reservations
             WHERE wish_id = :wish_id
             LIMIT 1
             FOR UPDATE'
        );
        $reservationStatement -> execute(['wish_id' => $wishId]);
        $reservation = $reservationStatement -> fetch();

        if (!is_array($reservation)){
            throw new RuntimeException('Бронирование для этого желания не найдено.');
        }

        $reservedByUserId = (int) ($reservation['reserved_by_user_id'] ?? 0);
        if ($reservedByUserId !== $userId){
            throw new RuntimeException('Нельзя отменить чужое бронирование.');
        }

        $deleteStatement = $pdo -> prepare(
            'DELETE FROM wish_reservations
             WHERE id = :id
               AND reserved_by_user_id = :reserved_by_user_id'
        );
        $deleteStatement -> execute([
            'id' => (int) $reservation['id'],
            'reserved_by_user_id' => $userId,
        ]);

        $pdo -> commit();
        set_flash_message('success', 'Бронирование успешно отменено.');
        safe_redirect($redirectTo);
    }catch (Throwable $exception){
        if ($pdo instanceof PDO && $pdo -> inTransaction()){
            $pdo -> rollBack();
        }

        $message = trim((string) $exception -> getMessage());
        if ($message === ''){
            $message = 'Не удалось отменить бронирование.';
        }

        set_flash_message('error', $message);
        safe_redirect($redirectTo);
    }
?>