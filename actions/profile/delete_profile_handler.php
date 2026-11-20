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
    $confirmed = (string) ($_POST['confirm_delete'] ?? '') === '1';

    if ($userId <= 0 || !$confirmed){
        safe_redirect(ROUTE_SETTINGS);
    }

    try{
        $pdo = db();
    }catch (Throwable $exception){
        set_flash_message('error', 'Не удалось подключиться к базе данных.');
        safe_redirect(ROUTE_SETTINGS);
    }

    $tableExists = static function (PDO $pdo, string $tableName): bool{
        $statement = $pdo -> prepare(
            'SELECT COUNT(*)
             FROM information_schema.tables
             WHERE table_schema = DATABASE()
               AND table_name = :table_name'
        );
        $statement -> execute(['table_name' => $tableName]);

        return (int) $statement -> fetchColumn() > 0;
    };

    $avatarPath = '';
    $wishImages = [];

    try{
        $pdo -> beginTransaction();

        $userStatement = $pdo -> prepare(
            'SELECT avatar
             FROM users
             WHERE id = :id
             LIMIT 1
             FOR UPDATE'
        );
        $userStatement -> execute(['id' => $userId]);
        $userRow = $userStatement -> fetch();

        if (!is_array($userRow)){
            throw new RuntimeException('Пользователь не найден.');
        }

        $avatarPath = trim((string) ($userRow['avatar'] ?? ''));

        foreach (['wishes', 'fulfilled_wishes'] as $tableName){
            if (!$tableExists($pdo, $tableName)){
                continue;
            }

            $imagesStatement = $pdo -> prepare(
                "SELECT image
                 FROM `{$tableName}`
                 WHERE user_id = :user_id
                   AND image IS NOT NULL
                   AND image <> ''"
            );
            $imagesStatement -> execute(['user_id' => $userId]);

            foreach ($imagesStatement -> fetchAll() as $row){
                if (!is_array($row)){
                    continue;
                }

                $imagePath = trim((string) ($row['image'] ?? ''));
                if ($imagePath !== ''){
                    $wishImages[] = $imagePath;
                }
            }
        }

        $foreignTablesStatement = $pdo -> query(
            'SELECT table_name, column_name
             FROM information_schema.key_column_usage
             WHERE constraint_schema = DATABASE()
               AND referenced_table_name = "users"
               AND referenced_column_name = "id"'
        );

        foreach ($foreignTablesStatement -> fetchAll() as $foreignTableRow){
            if (!is_array($foreignTableRow)){
                continue;
            }

            $tableName = (string) ($foreignTableRow['table_name'] ?? '');
            $columnName = (string) ($foreignTableRow['column_name'] ?? '');

            if ($tableName === '' || $columnName === '' || $tableName === 'users'){
                continue;
            }

            if (preg_match('/^[a-zA-Z0-9_]+$/', $tableName) !== 1 || preg_match('/^[a-zA-Z0-9_]+$/', $columnName) !== 1){
                continue;
            }

            $deleteChildStatement = $pdo -> prepare(
                "DELETE FROM `{$tableName}` WHERE `{$columnName}` = :user_id"
            );
            $deleteChildStatement -> execute(['user_id' => $userId]);
        }

        $deleteUserStatement = $pdo -> prepare(
            'DELETE FROM users
             WHERE id = :id
             LIMIT 1'
        );
        $deleteUserStatement -> execute(['id' => $userId]);

        if ($deleteUserStatement -> rowCount() < 1){
            throw new RuntimeException('Пользователь не найден.');
        }

        $pdo ->commit();
    }catch (Throwable $exception){
        if ($pdo -> inTransaction()){
            $pdo -> rollBack();
        }

        $rawErrorMessage = (string) $exception->getMessage();
        $errorMessage = function_exists('mb_strtolower')
            ? mb_strtolower($rawErrorMessage, 'UTF-8')
            : strtolower($rawErrorMessage);

        $userMessage = 'Не удалось удалить аккаунт. Попробуйте еще раз.';

        if (str_contains($errorMessage, 'foreign key') || str_contains($errorMessage, 'constraint')){
            $userMessage = 'Не удалось удалить аккаунт: в базе есть связанные данные со старой схемой. Обновите database/schema.sql.';
        }elseif (str_contains($errorMessage, 'base table') || str_contains($errorMessage, "doesn't exist") || str_contains($errorMessage, 'table')){
            $userMessage = 'Не удалось удалить аккаунт: отсутствуют нужные таблицы в базе данных. Обновите database/schema.sql.';
        }elseif (str_contains($errorMessage, 'пользователь не найден')){
            $userMessage = 'Аккаунт уже удален или не найден.';
        }

        set_flash_message('error', $userMessage);
        safe_redirect(ROUTE_SETTINGS);
    }

    $deleteFileByWebPath = static function (string $path, string $baseWebPath, string $baseDir): void{
        $path = trim($path);
        if ($path === ''){
            return;
        }

        $prefix = rtrim($baseWebPath, '/') . '/';
        if (!str_starts_with($path, $prefix)){
            return;
        }

        $fileName = basename($path);
        if ($fileName === ''){
            return;
        }

        $absolutePath = rtrim($baseDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $fileName;
        if (is_file($absolutePath)){
            @unlink($absolutePath);
        }
    };

    $deleteFileByWebPath($avatarPath, AVATAR_UPLOADS_WEB_PATH, AVATAR_UPLOADS_DIR);
    foreach ($wishImages as $wishImage){
        $deleteFileByWebPath($wishImage, WISH_UPLOADS_WEB_PATH, WISH_UPLOADS_DIR);
    }

    logout_user();
    safe_redirect(ROUTE_HOME);
?>