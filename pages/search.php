<?php

    declare(strict_types=1);

    require_once __DIR__ . '/../config/constants.php';
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../core/session.php';
    require_once __DIR__ . '/../core/functions.php';
    require_once __DIR__ . '/../core/auth.php';
    require_once __DIR__ . '/../core/seo.php';

    require_auth(make_route_with_redirect(ROUTE_LOGIN, ROUTE_SEARCH));

    $renderInlineIcon = static function (string $iconFileName): string{
        $iconsDir = __DIR__ . '/../assets/images/icons/';
        $nameWithoutExt = preg_replace('/\.svg$/i', '', $iconFileName) ?: $iconFileName;
        $candidates = [
            $iconsDir . $iconFileName,
            $iconsDir . $nameWithoutExt,
        ];

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
        ['title' => 'Подписки',
         'icon' => 'following_icon.svg',
         'href' => '/pages/error.php'],
        ['title' => 'Подписчики',
         'icon' => 'followers_icon.svg',
         'href' => '/pages/error.php'],
        ['is_divider' => true],
        ['title' => 'Найти друзей',
         'icon' => 'find_friends_icon.svg',
         'href' => ROUTE_SEARCH,
         'is_active' => true],
    ];

    $defaultSidebarSections = [
        ['title' => 'Для вас',
         'is_title' => true,
         'icon' => 'for_you_icon.svg',
         'href' => ROUTE_HOME],
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
         'icon' => 'gift_certificates_icon.svg'],
    ];

    $leftSidebarSections = array_merge(
        $leftSidebarSections,
        [
            ['is_divider' => true],
        ],
        $defaultSidebarSections
    );

    $leftSidebarApps = [
        ['title' => 'For business',
         'icon' => 'for_business_icon.svg',
         'href' => '/pages/error.php'],
        ['title' => 'Download for iOS',
         'icon' => 'download_for_iOS_icon.svg',
         'href' => '/pages/error.php'],
        ['title' => 'Download for Android',
         'icon' => 'download_for_Android_icon.svg',
         'href' => '/pages/error.php'],
        ['title' => 'Download on AppGallery',
         'icon' => 'download_on_AppGallery_icon.svg',
         'href' => '/pages/error.php'],
        ['title' => 'Chrome extension',
         'icon' => 'chrome_extension_icon.svg',
         'href' => '/pages/error.php'],
    ];

    $seoMeta = [
        'title' => 'Поиск друзей — Ohmywishes',
        'description' => 'Страница поиска пользователей по username с быстрыми результатами и переходом в профиль.',
        'robots' => 'noindex, nofollow',
        'url' => ROUTE_SEARCH,
        'og_type' => 'website',
        'og_image' => ASSETS_PATH . '/images/icons/icon_logo.png',
        'og_site_name' => 'Ohmywishes',
    ];

    $versionedAsset = static function (string $publicPath): string{
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
        <script src="<?= e(versioned_asset(ASSETS_PATH . '/scripts/search_user.js')) ?>" defer></script>
        <script src="<?= e(versioned_asset(ASSETS_PATH . '/scripts/main.js')) ?>" defer></script>
    </head>
    <body class="home-page search-page">
        <?php
            $currentNav = 'friends';
            require __DIR__ . '/../includes/header.php';
        ?>

        <main class="home-content search-content">
            <?php require __DIR__ . '/../includes/flash_message.php'; ?>

            <div class="home-layout search-layout">
                <?php require __DIR__ . '/../includes/left_sidebar.php'; ?>

                <section class="search-main" data-search-users data-search-endpoint="<?= e(ROUTE_SEARCH_HANDLER) ?>" data-user-url-template="<?= e(ROUTE_USER . '?id={id}') ?>" data-default-avatar="<?= e(ASSETS_PATH . '/images/default_avatar.png') ?>" >
                    <label class="search-main__input-wrap" for="search-users-input">
                        <span class="search-main__input-icon" aria-hidden="true"><?= $renderInlineIcon('search_icon.svg') ?></span>
                        <input class="search-main__input" id="search-users-input" type="text" name="search" placeholder="Поиск" autocomplete="off" data-search-input>
                    </label>

                    <div class="search-main__body">
                        <p class="search-main__state" data-search-state="empty">Введите имя в поле поиска что бы найти друзей</p>
                        <p class="search-main__state" data-search-state="loading" hidden>Поиск пользователей...</p>
                        <p class="search-main__state" data-search-state="no-results" hidden>Пользователи не найдены</p>
                        <p class="search-main__state" data-search-state="error" hidden>Ошибка поиска. Попробуйте еще раз</p>

                        <div class="search-main__results" data-search-results hidden></div>
                    </div>
                </section>

                <?php require __DIR__ . '/../includes/right_sidebar.php'; ?>
            </div>
        </main>
    </body>
</html>
