<?php

    declare(strict_types=1);
    
    require_once __DIR__ . '/../core/functions.php';
    
    $flashMessages = get_flash_messages();
    
    if ($flashMessages === []) {
        return;
    }
?>

<div class="flash-stack" data-alerts>
    <?php foreach ($flashMessages as $type => $messages): ?>
        <?php
            $safeType = in_array($type, ['success', 'error', 'warning', 'info'], true) ? $type : 'info';
            $items = is_array($messages) ? $messages : [$messages];
        ?>
        <?php foreach ($items as $message): ?>
            <article class="flash-message flash-message--<?= e($safeType) ?>" data-alert data-auto-hide="6000" role="status">
                <p class="flash-message__text"><?= e((string) $message) ?></p>
                <button class="flash-message__close" type="button" data-alert-close>&times;</button>
            </article>
        <?php endforeach; ?>
    <?php endforeach; ?>
</div>