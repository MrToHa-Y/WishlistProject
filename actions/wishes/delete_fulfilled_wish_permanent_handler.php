<?php

    declare(strict_types=1);

    require_once __DIR__ . '/../../config/constants.php';
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../core/session.php';
    require_once __DIR__ . '/../../core/functions.php';
    require_once __DIR__ . '/../../core/auth.php';

    require_auth(make_route_with_redirect(ROUTE_LOGIN, ROUTE_FULFILLED));

    if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'POST'){
        safe_redirect(ROUTE_FULFILLED);
    }

    $redirectTo = normalize_redirect_path((string) ($_POST['redirect_to'] ?? ROUTE_FULFILLED));
    if ($redirectTo === ''){
        $redirectTo = ROUTE_FULFILLED;
    }

    $user = auth_user();
    $userId = (int) ($user['id'] ?? 0);
    $fulfilledId = filter_var($_POST['fulfilled_id'] ?? ($_POST['wish_id'] ?? null), FILTER_VALIDATE_INT);

    if ($userId <= 0 || !is_int($fulfilledId) || $fulfilledId <= 0){
        set_flash_message('error', 'Не удалось удалить исполненное желание: неверный идентификатор.');
        safe_redirect($redirectTo);
    }

    try {
        $pdo = db();

        $wishStatement = $pdo -> prepare(
            'SELECT id, image
             FROM fulfilled_wishes
             WHERE id = :id
               AND user_id = :user_id
             LIMIT 1'
        );
        $wishStatement -> execute([
            'id' => $fulfilledId,
            'user_id' => $userId,
        ]);
        $wish = $wishStatement -> fetch();

        if (!is_array($wish)){
            set_flash_message('error', 'Исполненное желание не найдено.');
            safe_redirect($redirectTo);
        }

        $imagePath = trim((string) ($wish['image'] ?? ''));

        $deleteStatement = $pdo -> prepare(
            'DELETE FROM fulfilled_wishes
             WHERE id = :id
               AND user_id = :user_id'
        );
        $deleteStatement -> execute([
            'id' => $fulfilledId,
            'user_id' => $userId,
        ]);

        if ($imagePath !== ''){
            $webPrefix = rtrim(WISH_UPLOADS_WEB_PATH, '/') . '/';
            if (str_starts_with($imagePath, $webPrefix)){
                $fileName = basename($imagePath);
                if ($fileName !== '') {
                    $absolutePath = rtrim(WISH_UPLOADS_DIR, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileName;
                    if (is_file($absolutePath)){
                        @unlink($absolutePath);
                    }
                }
            }
        }

        set_flash_message('success', 'Исполненное желание удалено.');
        safe_redirect($redirectTo);
    }catch (Throwable $exception){
        set_flash_message('error', 'Не удалось удалить исполненное желание.');
        safe_redirect($redirectTo);
    }
?>