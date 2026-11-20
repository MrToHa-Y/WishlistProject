<?php

    declare(strict_types=1);

    require_once __DIR__ . '/../config/constants.php';
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../core/session.php';
    require_once __DIR__ . '/../core/functions.php';
    require_once __DIR__ . '/../core/auth.php';
    require_once __DIR__ . '/../core/seo.php';

    require_auth(make_route_with_redirect(ROUTE_LOGIN, ROUTE_SEARCH));
    $viewer = auth_user();
    $viewerUserId = (int) ($viewer['id'] ?? 0);

    $requestedUserId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    if (!is_int($requestedUserId) || $requestedUserId <= 0){
        set_flash_message('warning', 'Укажите пользователя для просмотра профиля.');
        safe_redirect(ROUTE_SEARCH);
    }

    $renderInlineIcon = static function (string $iconFileName): string{
        $iconsDir = __DIR__ . '/../assets/images/icons/';
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
         'is_active' => true]];

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
         'icon' => 'gift_certificates_icon.svg']];

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
         'href' => '/pages/error.php']];

    $defaultAvatar = ASSETS_PATH . '/images/default_avatar.png';
    $reservationByWishId = [];
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

        $absolutePath = __DIR__ . '/..' . $webPath;
        if (!is_file($absolutePath)){
            return $defaultAvatar;
        }

        return $webPath;
    };

    try{
        $pdo = db();
    }catch (Throwable $exception){
        $pdo = null;
    }

    if (!$pdo instanceof PDO){
        set_flash_message('error', 'База данных временно недоступна. Попробуйте позже.');
        safe_redirect(ROUTE_SEARCH);
    }

    $targetUser = null;
    try{
        $userStatement = $pdo -> prepare(
            'SELECT id, username, first_name, last_name, avatar, birth_date, description
             FROM users
             WHERE id = :id
             LIMIT 1'
        );
        $userStatement -> execute(['id' => $requestedUserId]);
        $targetUser = $userStatement -> fetch();
    }catch (Throwable $exception){
        $targetUser = null;
    }

    if (!is_array($targetUser)){
        set_flash_message('warning', 'Пользователь не найден.');
        safe_redirect(ROUTE_SEARCH);
    }

    $catalogItems = [];
    try{
        $wishesStatement = $pdo -> prepare(
            'SELECT id, title, description, image, product_link, price, NULL AS promo_text
             FROM wishes
             WHERE user_id = :user_id
               AND is_active = 1
             ORDER BY id DESC'
        );
        $wishesStatement -> execute(['user_id' => (int) $targetUser['id']]);
        $catalogItems = $wishesStatement -> fetchAll();
    }catch (Throwable $exception){
        $catalogItems = [];
    }

    try{
        $reservationStatement = $pdo -> prepare(
            'SELECT wish_id, reserved_by_user_id, created_at
             FROM wish_reservations
             WHERE wish_owner_id = :wish_owner_id
               AND created_at >= (NOW() - INTERVAL 90 DAY)'
        );
        $reservationStatement -> execute(['wish_owner_id' => (int) $targetUser['id']]);
        $reservationRows = $reservationStatement -> fetchAll();

        foreach ($reservationRows as $reservationRow){
            if (!is_array($reservationRow)){
                continue;
            }

            $wishId = (int) ($reservationRow['wish_id'] ?? 0);
            if ($wishId <= 0){
                continue;
            }

            $reservationByWishId[$wishId] = [
                'reserved_by_user_id' => (int) ($reservationRow['reserved_by_user_id'] ?? 0),
                'created_at' => (string) ($reservationRow['created_at'] ?? ''),
            ];
        }
    }catch (Throwable $exception){
        $reservationByWishId = [];
    }

    $profileUsername = trim((string) ($targetUser['username'] ?? ''));
    if ($profileUsername === ''){
        $profileUsername = trim((string) (($targetUser['first_name'] ?? '') . ' ' . ($targetUser['last_name'] ?? '')));
    }
    if ($profileUsername === ''){
        $profileUsername = 'User';
    }

    $profileAvatar = $resolveAvatarPath((string) ($targetUser['avatar'] ?? ''));
    $profileDescription = trim((string) ($targetUser['description'] ?? ''));
    $profileDescriptionLabel = $profileDescription !== '' ? $profileDescription : 'Описание профиля не указано';

    $profileBirthDate = trim((string) ($targetUser['birth_date'] ?? ''));
    $profileBirthDateLabel = 'Дата рождения не указана';
    $birthdayCountdownLabel = null;
    if ($profileBirthDate !== ''){
        $birthDate = DateTimeImmutable::createFromFormat('Y-m-d', $profileBirthDate);
        if ($birthDate instanceof DateTimeImmutable){
            $monthNames = [
                1 => 'января',
                2 => 'февраля',
                3 => 'марта',
                4 => 'апреля',
                5 => 'мая',
                6 => 'июня',
                7 => 'июля',
                8 => 'августа',
                9 => 'сентября',
                10 => 'октября',
                11 => 'ноября',
                12 => 'декабря',
            ];

            $monthNumber = (int) $birthDate -> format('n');
            $profileBirthDateLabel = $birthDate -> format('j') . ' ' . ($monthNames[$monthNumber] ?? $birthDate -> format('m'));

            $today = new DateTimeImmutable('today');
            $nextBirthday = $birthDate -> setDate(
                (int) $today -> format('Y'),
                (int) $birthDate -> format('m'),
                (int) $birthDate -> format('d')
            );

            if ($nextBirthday < $today){
                $nextBirthday = $nextBirthday -> modify('+1 year');
            }

            $daysLeft = (int) $today -> diff($nextBirthday) -> format('%a');
            if ($daysLeft === 0){
                $birthdayCountdownLabel = 'С днем рождения!';
            }elseif ($daysLeft === 1){
                $birthdayCountdownLabel = 'Через 1 день';
            }elseif ($daysLeft >= 2 && $daysLeft <= 4){
                $birthdayCountdownLabel = 'Через ' . $daysLeft . ' дня';
            }else{
                $birthdayCountdownLabel = 'Через ' . $daysLeft . ' дней';
            }
        }
    }

    $wishCount = count($catalogItems);
    $seoMeta = [
        'title' => $profileUsername . ' — профиль пользователя',
        'description' => 'Профиль пользователя с описанием и списком его желаний.',
        'robots' => 'noindex, nofollow',
        'url' => ROUTE_USER . '?id=' . (int) $targetUser['id'],
        'og_type' => 'profile',
        'og_image' => $profileAvatar,
        'og_site_name' => 'Ohmywishes'];

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
        <script src="<?= e(versioned_asset(ASSETS_PATH . '/scripts/reserve_wish_modal.js')) ?>" defer></script>
        <script src="<?= e(versioned_asset(ASSETS_PATH . '/scripts/main.js')) ?>" defer></script>
    </head>
    <body class="home-page profile-page user-page">
        <?php
            $currentNav = 'friends';
            require __DIR__ . '/../includes/header.php';?>

        <main class="home-content profile-content user-content">
            <?php require __DIR__ . '/../includes/flash_message.php'; ?>

            <div class="home-layout profile-layout user-layout">
                <?php require __DIR__ . '/../includes/left_sidebar.php'; ?>

                <section class="profile-main user-main">
                    <section class="profile-hero user-hero">
                        <img class="profile-hero__avatar" src="<?= e($profileAvatar) ?>" alt="<?= e($profileUsername) ?>">
                        <h1 class="profile-hero__username"><?= e($profileUsername) ?></h1>
                        <p class="profile-hero__description"><?= e($profileDescriptionLabel) ?></p>
                        <div class="profile-hero__birthday-row">
                            <span class="profile-hero__birthday-main">
                                <span class="profile-hero__birthday-label">День рождение</span>
                                <span class="profile-hero__birthday-icon" aria-hidden="true"><?= $renderInlineIcon('birth_icon.svg') ?></span>
                                <span class="profile-hero__birthday-date"><?= e($profileBirthDateLabel) ?></span>
                            </span>
                            <?php if ($birthdayCountdownLabel !== null): ?>
                                <span class="profile-hero__birthday-countdown"><?= e($birthdayCountdownLabel) ?></span>
                            <?php endif; ?>
                        </div>
                    </section>

                    <section class="profile-wishes user-wishes">
                        <div class="profile-wishes__header">
                            <h2 class="profile-wishes__title">
                                <span>Все желания</span>
                                <span class="profile-wishes__count"><?= e((string) $wishCount) ?></span>
                            </h2>
                        </div>

                        <div class="profile-wishes__grid wish-catalog__grid user-wishes__grid">
                            <?php if ($wishCount === 0): ?>
                                <p class="wish-catalog__empty">У этого пользователя пока нет публичных желаний.</p>
                            <?php else: ?>
                                <?php foreach ($catalogItems as $catalogItem): ?>
                                    <?php
                                        $wishId = (int) ($catalogItem['id'] ?? 0);
                                        $reservationMeta = $reservationByWishId[$wishId] ?? null;
                                        $reservedByUserId = (int) (($reservationMeta['reserved_by_user_id'] ?? 0));
                                        $isReserved = $reservedByUserId > 0;
                                        $isReservedByCurrentUser = $isReserved && $reservedByUserId === $viewerUserId;
                                        $isReservedByAnotherUser = $isReserved && !$isReservedByCurrentUser;

                                        $catalogCardTriggerType = 'reserve';
                                        $catalogCardInteractive = !$isReservedByAnotherUser;
                                        $catalogCardExtraData = [
                                            'owner-id' => (string) ((int) ($targetUser['id'] ?? 0)),
                                            'owner-username' => (string) $profileUsername,
                                            'owner-avatar' => (string) $profileAvatar,
                                            'owner-profile-url' => ROUTE_USER . '?id=' . (int) ($targetUser['id'] ?? 0),
                                            'is-reserved' => $isReserved ? '1' : '0',
                                            'reserved-by-current-user' => $isReservedByCurrentUser ? '1' : '0',
                                        ];
                                    ?>
                                    <div class="reservation-card<?= $isReservedByAnotherUser ? ' reservation-card--locked' : '' ?>">
                                        <?php require __DIR__ . '/../includes/catalog_card.php'; ?>
                                        <?php if ($isReservedByAnotherUser): ?>
                                            <span class="reservation-card__overlay" aria-hidden="true">
                                                <span class="reservation-card__lock"><?= $renderInlineIcon('lock_icon.svg') ?></span>
                                                <span class="reservation-card__text">Бронирование снимется через 90 дней</span>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <?php
                                        $catalogCardTriggerType = 'open';
                                        $catalogCardInteractive = true;
                                        $catalogCardExtraData = [];
                                    ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </section>
                </section>

                <?php require __DIR__ . '/../includes/right_sidebar.php'; ?>
            </div>
            <?php require __DIR__ . '/../includes/reserve_wish_modal.php'; ?>
        </main>
    </body>
</html>
