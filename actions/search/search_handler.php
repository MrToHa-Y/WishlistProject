<?php

    declare(strict_types=1);

    require_once __DIR__ . '/../../config/constants.php';
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../core/session.php';
    require_once __DIR__ . '/../../core/functions.php';
    require_once __DIR__ . '/../../core/auth.php';

    header('Content-Type: application/json; charset=UTF-8');

    $respond = static function (int $statusCode, array $payload): void{
        http_response_code($statusCode);
        echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    };

    if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? '')) !== 'GET'){
        $respond(405, [
            'success' => false,
            'message' => 'Метод не поддерживается.',
            'users' => [],
        ]);
    }

    if (!is_authenticated()){
        $respond(401, [
            'success' => false,
            'message' => 'Требуется авторизация.',
            'users' => [],
        ]);
    }

    $query = trim((string) ($_GET['q'] ?? ''));
    $query = preg_replace('/\s+/u', ' ', $query) ?? '';
    if ($query === ''){
        $respond(200, [
            'success' => true,
            'query' => '',
            'users' => [],
        ]);
    }

    if (mb_strlen($query) > USERNAME_MAX_LENGTH){
        $query = mb_substr($query, 0, USERNAME_MAX_LENGTH);
    }

    $defaultAvatar = ASSETS_PATH . '/images/default_avatar.png';
    $resolveAvatarPath = static function (?string $rawAvatarPath) use ($defaultAvatar): string{
        $rawAvatarPath = trim((string) $rawAvatarPath);
        if ($rawAvatarPath === ''){
            return $defaultAvatar;
        }

        if (preg_match('~^https?://~i', $rawAvatarPath) === 1){
            return $rawAvatarPath;
        }

        $webPath = str_starts_with($rawAvatarPath, '/')
            ? $rawAvatarPath
            : '/' . ltrim($rawAvatarPath, '/');

        if (preg_match('/\.(png|jpe?g|webp|gif|svg)$/i', $webPath) !== 1){
            return $defaultAvatar;
        }

        $absolutePath = __DIR__ . '/../..' . $webPath;
        if (!is_file($absolutePath)){
            return $defaultAvatar;
        }

        return $webPath;
    };

    try{
        $pdo = db();
        $statement = $pdo -> prepare(
            'SELECT id, username, avatar
             FROM users
             WHERE username LIKE :query
             ORDER BY username ASC
             LIMIT 30'
        );
        $statement -> execute(['query' => '%' . $query . '%']);
        $rows = $statement -> fetchAll();
    }catch (Throwable $exception){
        $respond(500, [
            'success' => false,
            'message' => 'Ошибка базы данных во время поиска.',
            'users' => [],
        ]);
    }

    $users = [];
    foreach ($rows as $row){
        if (!is_array($row)){
            continue;
        }

        $id = (int) ($row['id'] ?? 0);
        $username = trim((string) ($row['username'] ?? ''));

        if ($id <= 0 || $username === ''){
            continue;
        }

        $users[] = [
            'id' => $id,
            'username' => $username,
            'avatar' => $resolveAvatarPath((string) ($row['avatar'] ?? '')),
        ];
    }

    $respond(200, [
        'success' => true,
        'query' => $query,
        'users' => $users,
    ]);
?>