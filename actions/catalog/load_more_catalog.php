<?php
    
    declare(strict_types=1);
    
    require_once __DIR__ . '/../../config/constants.php';
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../core/functions.php';
    require_once __DIR__ . '/../../core/session.php';
    
    header('Content-Type: text/html; charset=UTF-8');
    
    $offset = filter_input(INPUT_GET, 'offset', FILTER_VALIDATE_INT);
    $limit = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT);
    
    $offset = is_int($offset) && $offset >= 0 ? $offset : 0;
    $limit = is_int($limit) && $limit > 0 ? $limit : 9;
    $limit = min($limit, 24);
    
    try{
        $pdo = db();
    
        $statement = $pdo -> prepare(
            'SELECT id, title, description, image, product_link, price, promo_text
             FROM catalog_items
             WHERE is_active = 1
             ORDER BY id DESC
             LIMIT :limit OFFSET :offset'
        );
        $statement -> bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement -> bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement -> execute();
    
        $items = $statement -> fetchAll();
    }catch (Throwable $exception){
        http_response_code(500);
        exit;
    }
    
    if (!$items){
        exit;
    }
    
    foreach ($items as $catalogItem){
        require __DIR__ . '/../../includes/catalog_card.php';
    }
?>