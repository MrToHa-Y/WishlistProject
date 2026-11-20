<?php

    declare(strict_types=1);

    if (!isset($catalogItem) || !is_array($catalogItem)){
        return;
    }

    $itemId = (int) ($catalogItem['id'] ?? 0);
    $title = trim((string) ($catalogItem['title'] ?? ''));
    $description = trim((string) ($catalogItem['description'] ?? ''));
    $image = trim((string) ($catalogItem['image'] ?? ''));
    $productLink = trim((string) ($catalogItem['product_link'] ?? ''));
    $promoText = trim((string) ($catalogItem['promo_text'] ?? ''));
    $price = (float) ($catalogItem['price'] ?? 0);
    $catalogCardInteractive = !isset($catalogCardInteractive) || $catalogCardInteractive === true;
    $catalogCardTriggerType = isset($catalogCardTriggerType) ? (string) $catalogCardTriggerType : 'open';
    $catalogCardExtraData = isset($catalogCardExtraData) && is_array($catalogCardExtraData)
        ? $catalogCardExtraData
        : [];

    if ($title === '' || $image === ''){
        return;
    }

    $renderCatalogIcon = static function (string $iconFileName): string{
        $iconsDir = __DIR__ . '/../assets/images/icons/';
        $nameWithoutExt = preg_replace('/\.svg$/i', '', $iconFileName) ?: $iconFileName;
        $candidates = [
            $iconsDir . $iconFileName,
            $iconsDir . $nameWithoutExt,
        ];

        foreach ($candidates as $iconPath){
            if (!is_file($iconPath)) {
                continue;
            }

            $svg = file_get_contents($iconPath);
            if (is_string($svg) && trim($svg) !== ''){
                return $svg;
            }
        }

        return '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><circle cx="12" cy="12" r="12" fill="currentColor"/></svg>';
    };

    $promoLabel = $promoText;
    $promoValue = '';
    if ($promoText !== '' && preg_match('/^(.+?)\s+(\d+\s*%.*)$/u', $promoText, $promoMatches) === 1){
        $promoLabel = trim((string) ($promoMatches[1] ?? $promoText));
        $promoValue = trim((string) ($promoMatches[2] ?? ''));
    }

    $promoIconSvg = $renderCatalogIcon('percent_icon.svg');
    $priceText = number_format($price, 0, '.', ' ') . ' ₸';
    $priceValue = number_format($price, 2, '.', '');

    $triggerAttribute = 'data-open-card-modal-trigger';
    if ($catalogCardTriggerType === 'reserve'){
        $triggerAttribute = 'data-reserve-wish-trigger';
    }

    $extraDataAttributes = '';
    foreach ($catalogCardExtraData as $extraKey => $extraValue){
        if (!is_string($extraKey) || $extraKey === ''){
            continue;
        }

        if (preg_match('/^[a-z0-9_-]+$/i', $extraKey) !== 1){
            continue;
        }

        if (!is_scalar($extraValue) && $extraValue !== null){
            continue;
        }

        $extraDataAttributes .= ' data-' . $extraKey . '="' . e((string) $extraValue) . '"';
    }
?>

<article class="catalog-card" data-catalog-card>
    <?php if ($catalogCardInteractive): ?>
        <button class="catalog-card__action" type="button" <?= $triggerAttribute ?> data-item-id="<?= e((string) $itemId) ?>" data-item-title="<?= e($title) ?>" data-item-description="<?= e($description) ?>" data-item-image="<?= e($image) ?>" data-item-price="<?= e($priceText) ?>" data-item-price-value="<?= e($priceValue) ?>" data-item-link="<?= e($productLink) ?>" data-item-promo="<?= e($promoText) ?>"<?= $extraDataAttributes ?>>
            <span class="catalog-card__media">
                <img src="<?= e($image) ?>" alt="<?= e($title) ?>" loading="lazy">
            </span>
            <span class="catalog-card__title"><?= e($title) ?></span>
            <span class="catalog-card__price"><?= e($priceText) ?></span>
            <?php if ($promoText !== ''): ?>
                <span class="catalog-card__promo">
                    <span class="catalog-card__promo-icon" aria-hidden="true"><?= $promoIconSvg ?></span>
                    <span><?= e($promoLabel) ?></span>
                    <?php if ($promoValue !== ''): ?>
                        <span class="catalog-card__promo-separator" aria-hidden="true"></span>
                        <span><?= e($promoValue) ?></span>
                    <?php endif; ?>
                </span>
            <?php endif; ?>
        </button>
    <?php else: ?>
        <div class="catalog-card__action catalog-card__action--static">
            <span class="catalog-card__media">
                <img src="<?= e($image) ?>" alt="<?= e($title) ?>" loading="lazy">
            </span>
            <span class="catalog-card__title"><?= e($title) ?></span>
            <span class="catalog-card__price"><?= e($priceText) ?></span>
            <?php if ($promoText !== ''): ?>
                <span class="catalog-card__promo">
                    <span class="catalog-card__promo-icon" aria-hidden="true"><?= $promoIconSvg ?></span>
                    <span><?= e($promoLabel) ?></span>
                    <?php if ($promoValue !== ''): ?>
                        <span class="catalog-card__promo-separator" aria-hidden="true"></span>
                        <span><?= e($promoValue) ?></span>
                    <?php endif; ?>
                </span>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</article>