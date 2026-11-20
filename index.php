<?php
    declare(strict_types=1);

    require_once __DIR__ . '/config/constants.php';
    require_once __DIR__ . '/config/database.php';
    require_once __DIR__ . '/core/session.php';
    require_once __DIR__ . '/core/functions.php';
    require_once __DIR__ . '/core/auth.php';
    require_once __DIR__ . '/core/seo.php';

    $seoMeta = [
        'title' => 'Ohmywishes — сервис вишлистов и идей подарков',
        'description' => 'Главная страница с подборками брендов, рекомендациями и идеями подарков для вашего вишлиста.',
        'robots' => 'index, follow',
        'url' => ROUTE_HOME,
        'og_type' => 'website',
        'og_image' => ASSETS_PATH . '/images/icons/icon_logo.png',
        'og_site_name' => 'Ohmywishes'];

    $renderInlineIcon = static function (string $iconFileName): string{
        $iconsDir = __DIR__ . '/assets/images/icons/';
        $nameWithoutExt = preg_replace('/\.svg$/i', '', $iconFileName) ?: $iconFileName;
        $candidates = [
            $iconsDir . $iconFileName,
             
            $iconsDir . $nameWithoutExt];

        foreach ($candidates as $iconPath){
            if (!is_file($iconPath)){
                continue;
            }

            $svg = file_get_contents($iconPath);
            if (is_string($svg) && trim($svg) !== ''){
                return $svg;
            }
        }

        return '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true"><rect width="24" height="24" fill="currentColor" fill-opacity="0.35"/></svg>';
    };

    $leftSidebarSections = [
        ['title' => 'Для вас', 
         'is_title' => true, 
         'icon' => 'for_you_icon.svg',],
        ['title' => 'Рекомендации',
         'icon' => 'featured_selections_icon.svg'],
        ['title' => '1 апреля', 
         'icon' => 'first_april_icon.svg'],
        ['title' => 'Идеи с промокодами', 
         'icon' => 'Ideas_promo_codes_icon.svg'],
        ['title' => 'Образование', 
         'icon' => 'education_icon.svg'],
        ['title' => 'Красота и здоровье', 
         'icon' => 'beauty_and_health_icon.svg'],
        ['title' => 'Одежда и обувь', 
         'icon' => 'clothing_and_shoes_icon.svg'],
        ['title' => 'Украшения и часы', 
         'icon' => 'jewelry_and_watches_icon.svg'],
        ['title' => 'Электроника', 
         'icon' => 'electronics_icon.svg'],
        ['title' => 'Книги', 
         'icon' => 'books_icon.svg'],
        ['title' => 'Путешествия', 
         'icon' => 'travel_icon.svg'],
        ['title' => 'Спорт и отдых', 
         'icon' => 'sports_and_recreation_icon.svg'],
        ['title' => 'Хобби и творчество', 
         'icon' => 'hobby_and_creativity_icon.svg'],
        ['title' => 'Дом и сад', 
         'icon' => 'home_and_garden_icon.svg'],
        ['title' => 'Еда и напитки', 
         'icon' => 'food_and_drinks_icon.svg'],
        ['title' => 'Детям и родителям', 
         'icon' => 'children_and_parents_icon.svg'],
        ['title' => 'Для авто', 
         'icon' => 'for_auto_icon.svg'],
        ['title' => 'Питомцам', 
         'icon' => 'pets_icon.svg'],
        ['title' => 'Подарочные сертификаты', 
         'icon' => 'gift_certificates_icon.svg']];

    $leftSidebarApps = [
        ['title' => 'For business', 
         'icon' => 'for_business_icon.svg', 
         'accent' => true],
        ['title' => 'Download for iOS', 
         'icon' => 'download_for_iOS_icon.svg'],
        ['title' => 'Download for Android', 
         'icon' => 'download_for_Android_icon.svg'],
        ['title' => 'Download on AppGallery', 
         'icon' => 'download_on_AppGallery_icon.svg'],
        ['title' => 'Chrome extension', 
         'icon' => 'chrome_extension_icon.svg']];


    $brands = [
        ['name' => 'SOKOLOV BEAUTY', 
         'logo' => '/assets/images/socolov_beauty.webp', 
         'previews' => ['/assets/images/socolov_beauty_1.webp', 
                        '/assets/images/socolov_beauty_2.webp']],
        ['name' => 'LITTLE BLACK', 
         'logo' => '/assets/images/little_black.webp', 
         'previews' => ['/assets/images/little_black_1.webp', 
                        '/assets/images/little_black_2.webp']],
        ['name' => 'Aprell', 
         'logo' => '/assets/images/aprell.webp', 
         'previews' => ['/assets/images/aprell_1.webp', 
                        '/assets/images/aprell_2.webp']],
        ['name' => 'LOVEMONO', 
         'logo' => '/assets/images/lovemono.webp', 
         'previews' => ['/assets/images/lovemono_1.webp', 
                        '/assets/images/lovemono_2.webp']],
        ['name' => 'moonswoon', 
         'logo' => '/assets/images/moonswoon.webp', 
         'previews' => ['/assets/images/moonswoon_1.webp', 
                        '/assets/images/moonswoon_2.webp']],
        ['name' => 'biblioteka aromatov', 
         'logo' => '/assets/images/biblioteka_aromatov.webp', 
         'previews' => ['/assets/images/biblioteka_aromatov_1.webp', 
                        '/assets/images/biblioteka_aromatov_2.webp']],
        ['name' => 'SHU SHA', 
         'logo' => '/assets/images/shu_sha.webp', 
         'previews' => ['/assets/images/shu_sha_1.webp', 
                        '/assets/images/shu_sha_2.webp']],
        ['name' => 'Poison Drop', 
         'logo' => '/assets/images/poison_drop.webp', 
         'previews' => ['/assets/images/poison_drop_1.webp', 
                        '/assets/images/poison_drop_2.webp']],
        ['name' => 'PaperShoot', 
         'logo' => '/assets/images/papershoot.webp', 
         'previews' => ['/assets/images/papershoot_1.webp', 
                        '/assets/images/papershoot_2.webp']],
        ['name' => 'SOKOLOV', 
         'logo' => '/assets/images/sokolov.webp', 
         'previews' => ['/assets/images/sokolov_1.webp', 
                        '/assets/images/sokolov_2.webp']],
        ['name' => 'Moon Soul', 
         'logo' => '/assets/images/moon_soul.webp', 
         'previews' => ['/assets/images/moon_soul_1.webp', 
                        '/assets/images/moon_soul_2.webp']],
        ['name' => '10.GRAN', 
         'logo' => '/assets/images/10_gran.webp', 
         'previews' => ['/assets/images/10_gran_1.webp', 
                        '/assets/images/10_gran_2.webp']]];

    $featuredSelections = [
        ['title' => 'ГИМ', 
         'description' => 'Подарки, вдохновленные историей', 
         'logo' => '/assets/images/hymn.webp'],
        ['title' => 'Дом Тома', 
         'description' => 'Дофаминовые подарки для себя и близких', 
         'logo' => '/assets/images/home_tome.webp'],
        ['title' => 'Не Гербалайф.', 
         'description' => '20 вещей, чтобы каждый день на кухне был праздником', 
         'logo' => '/assets/images/not_herbalife.webp'],
        ['title' => 'Глазами инженера', 
         'description' => 'Для заботы о себе и прогулок по городу', 
         'logo' => '/assets/images/the_engineers_perspective.webp'],
        ['title' => 'Севкабель Порт', 
         'description' => 'Подарки с петербургским флером', 'logo' => '/assets/images/sewcable_port.webp'],
        ['title' => 'Сила территории', 
         'description' => 'Для опытных походников и новичков в путешествиях', 
         'logo' => '/assets/images/power_of_territory.webp'],
        ['title' => 'Rep Chess', 
         'description' => 'То, что порадует шахматиста так же, как и победа в турнире', 
         'logo' => '/assets/images/rep_chees.webp'],
        ['title' => 'Лиза Зорка', 
         'description' => 'Для путешественниц и авантюристок', 
         'logo' => '/assets/images/liza_zorka.webp'],
        ['title' => 'Музей транспорта Москвы', 
         'description' => 'Для исследователей городской среды', 
         'logo' => '/assets/images/moscow_transport_museum.webp']];

    $catalogLimit = 9;
    $catalogTotal = 0;
    $catalogItems = [];
    $catalogDbReady = false;

    try{
        $pdo = db();
        $catalogDbReady = true;

        $countStatement = $pdo -> query('SELECT COUNT(*) 
                                       FROM catalog_items 
                                       WHERE is_active = 1');
        $catalogTotal = (int) $countStatement->fetchColumn();

        $itemsStatement = $pdo -> prepare(
            'SELECT id, title, description, image, product_link, price, promo_text
             FROM catalog_items
             WHERE is_active = 1
             ORDER BY id DESC
             LIMIT :limit OFFSET 0'
        );
        $itemsStatement -> bindValue(':limit', $catalogLimit, PDO::PARAM_INT);
        $itemsStatement -> execute();
        $catalogItems = $itemsStatement -> fetchAll();
    }catch (Throwable $exception){
        $catalogDbReady = false;
    }

    $hasMoreCatalogItems = $catalogDbReady && $catalogTotal > count($catalogItems);
    $catalogIsEmpty = $catalogItems === [];

    $versionedAsset = static function (string $publicPath): string {
        $normalizedPath = '/' . ltrim($publicPath, '/');
        $absolutePath = __DIR__ . '/..' . $normalizedPath;
        $version = is_file($absolutePath) ? (string) filemtime($absolutePath) : '1';

        return $normalizedPath . '?v=' . $version;
    };
?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <?= render_seo_meta($seoMeta) ?>
        <link rel="icon" href="/assets/images/icons/icon_logo.png">
        <link rel="stylesheet" href="<?= e(versioned_asset('/css/main.css')) ?>">
        <script src="<?= e(versioned_asset(ASSETS_PATH . '/scripts/alerts.js')) ?>" defer></script>
        <script src="<?= e(versioned_asset(ASSETS_PATH . '/scripts/modal.js')) ?>" defer></script>
        <script src="<?= e(versioned_asset(ASSETS_PATH . '/scripts/open_modal_card.js')) ?>" defer></script>
        <script src="<?= e(versioned_asset(ASSETS_PATH . '/scripts/infinite_scroll.js')) ?>" defer></script>
        <script src="<?= e(versioned_asset(ASSETS_PATH . '/scripts/main.js')) ?>" defer></script>
    </head>
    <body class="home-page">
        <?php
            $currentNav = 'ideas';
            require __DIR__ . '/includes/header.php';?>

        <main class="home-content home-main-page">
            <?php require __DIR__ . '/includes/flash_message.php'; ?>

            <div class="home-layout">
                <?php require __DIR__ . '/includes/left_sidebar.php'; ?>

                <section class="home-main">
                    <section class="brand-catalog">
                        <h2 class="home-section-title">Бренды</h2>
                        <div class="brand-catalog__grid">
                            <?php foreach ($brands as $brand): ?>
                                <a class="brand-card" href="/pages/error.php">
                                    <span class="brand-card__logo-wrap">
                                        <img src="<?= e($brand['logo']) ?>" alt="<?= e($brand['name']) ?>">
                                    </span>
                                    <span class="brand-card__name"><?= e($brand['name']) ?></span>
                                    <span class="brand-card__previews">
                                        <?php foreach ($brand['previews'] as $preview): ?>
                                            <img src="<?= e($preview) ?>" alt="" loading="lazy">
                                        <?php endforeach; ?>
                                    </span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <section class="featured-catalog">
                        <div class="featured-catalog__header">
                            <h2 class="home-section-title">Рекомендуемые предложения</h2>
                            <a class="featured-catalog__all" href="/pages/error.php">Показать все</a>
                        </div>

                        <div class="featured-catalog__grid">
                            <?php foreach ($featuredSelections as $feature): ?>
                                <a class="featured-card" href="/pages/error.php">
                                    <img class="featured-card__logo" src="<?= e($feature['logo']) ?>" alt="<?= e($feature['title']) ?>">
                                    <span class="featured-card__title">
                                        <?= e($feature['title']) ?>
                                        <span class="featured-card__influencer" aria-hidden="true"><?= $renderInlineIcon('influencer_icon.svg') ?></span>
                                    </span>
                                    <span class="featured-card__description"><?= e($feature['description']) ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <section class="editorial-mobile">
                        <h2 class="home-section-title">Каталог тем для подарков</h2>
                        <div class="editorial-mobile__grid">
                            <?php foreach (array_values(array_filter($leftSidebarSections, static fn(array $section): bool => empty($section['is_title']))) as $section): ?>
                                <?php
                                    $editorialCardClasses = ['editorial-mobile__card'];
                                    if (($section['icon'] ?? '') === 'first_april_icon.svg'){
                                        $editorialCardClasses[] = 'editorial-mobile__card--fixed-april-icon';
                                    }
                                ?>
                                <a class="<?= e(implode(' ', $editorialCardClasses)) ?>" href="/pages/error.php">
                                    <span><?= e($section['title']) ?></span>
                                    <span class="editorial-mobile__icon"><?= $renderInlineIcon($section['icon']) ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <section class="wish-catalog">
                        <div class="wish-catalog__header">
                            <div>
                                <h2 class="wish-catalog__title">Идеи для подарков <span><?= e((string) $catalogTotal) ?></span></h2>
                                <p class="wish-catalog__description">
                                    Мы собрали для вас оригинальные идеи подарков на все случаи жизни — от недорогих и классных до необычных и креативных.
                                    Здесь легко вдохновиться и найти прикольные идеи для подарка себе или близким.
                                    С таким вишлистом подарков вы будете готовы к любому празднику!
                                </p>
                            </div>
                        </div>

                        <div class="wish-catalog__grid" data-catalog-grid data-endpoint="/actions/catalog/load_more_catalog.php" data-limit="<?= e((string) $catalogLimit) ?>" data-offset="<?= e((string) count($catalogItems)) ?>">
                            <?php if ($catalogIsEmpty): ?>
                                <p class="wish-catalog__empty">Пока нет карточек в каталоге.</p>
                            <?php else: ?>
                                <?php foreach ($catalogItems as $catalogItem): ?>
                                    <?php require __DIR__ . '/includes/catalog_card.php'; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <div class="wish-catalog__sensor" data-catalog-sensor<?= $hasMoreCatalogItems ? '' : ' hidden' ?>>
                            Загружаем еще идеи...
                        </div>
                    </section>
                </section>

                <?php require __DIR__ . '/includes/right_sidebar.php'; ?>
            </div>

            <?php require __DIR__ . '/includes/open_modal_card.php'; ?>
        </main>
    </body>
</html>
