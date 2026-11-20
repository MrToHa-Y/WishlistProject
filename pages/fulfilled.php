<?php

    declare(strict_types=1);

    require_once __DIR__ . '/../config/constants.php';
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../core/session.php';
    require_once __DIR__ . '/../core/functions.php';
    require_once __DIR__ . '/../core/auth.php';
    require_once __DIR__ . '/../core/seo.php';

    require_auth(DEFAULT_REDIRECT_LOGIN);

    $user = auth_user();
    $userId = (int) ($user['id'] ?? 0);

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

    $profileSidebarLinks = [
        ['title' => 'Создать желание',
         'icon' => 'create_new_wish_icon.svg',
         'href' => '#',
         'open_create_wish' => true,
         'is_profile_shortcut' => true],
        ['title' => 'Мои желания',
         'icon' => 'my_wishes_icon.svg',
         'href' => ROUTE_PROFILE,
         'is_profile_shortcut' => true],
        ['title' => 'Забронированные',
         'icon' => 'reserve_icon.svg',
         'href' => ROUTE_RESERVATIONS,
         'is_profile_shortcut' => true],
        ['title' => 'Исполненные желания',
         'icon' => 'fulfilled_wishes_icon.svg',
         'href' => ROUTE_FULFILLED,
         'is_active' => true,
         'is_profile_shortcut' => true]];

    $defaultSidebarSections = [
        ['title' => 'Для вас', 
         'icon' => 'for_you_icon.svg', 
         'is_title' => true, 
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
        $profileSidebarLinks,
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

    $validationErrors = get_validation_errors();
    $openCreateWishModal = $validationErrors !== [];

    $defaultAvatar = ASSETS_PATH . '/images/default_avatar.png';
    $profileUsername = trim((string) ($user['username'] ?? ''));
    $profileAvatar = $defaultAvatar;
    $profileDescription = trim((string) ($user['description'] ?? ''));
    $profileBirthDate = null;
    $fulfilledWishes = [];

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

    if ($pdo instanceof PDO){
        try{
            $profileStatement = $pdo -> prepare(
                'SELECT username, avatar, birth_date, description
                 FROM users
                 WHERE id = :id
                 LIMIT 1'
            );
            $profileStatement -> execute(['id' => $userId]);
            $profileData = $profileStatement -> fetch();

            if (is_array($profileData)){
                $dbUsername = trim((string) ($profileData['username'] ?? ''));
                if ($dbUsername !== ''){
                    $profileUsername = $dbUsername;
                }

                $profileAvatar = $resolveAvatarPath((string) ($profileData['avatar'] ?? ''));

                $dbBirthDate = trim((string) ($profileData['birth_date'] ?? ''));
                if ($dbBirthDate !== ''){
                    $profileBirthDate = $dbBirthDate;
                }

                $dbDescription = trim((string) ($profileData['description'] ?? ''));
                if ($dbDescription !== ''){
                    $profileDescription = $dbDescription;
                }
            }
        }catch (Throwable $exception){
            $profileAvatar = $defaultAvatar;
        }

        try{
            $fulfilledStatement = $pdo -> prepare(
                'SELECT id, title, description, image, product_link, price, promo_text
                 FROM fulfilled_wishes
                 WHERE user_id = :user_id
                   AND is_active = 1
                 ORDER BY id DESC'
            );
            $fulfilledStatement -> execute(['user_id' => $userId]);
            $fulfilledWishes = $fulfilledStatement -> fetchAll();
        }catch (Throwable $exception){
            $fulfilledWishes = [];
        }
    }

    $fulfilledCount = count($fulfilledWishes);

    if ($profileUsername === ''){
        $profileUsername = trim((string) (($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '')));
    }
    if ($profileUsername === ''){
        $profileUsername = 'User';
    }

    $profileBirthDateLabel = 'День рождения не указан';
    if ($profileBirthDate !== null){
        $birthdayTimestamp = strtotime($profileBirthDate);
        if ($birthdayTimestamp !== false){
            $profileBirthDateLabel = date('d.m.Y', $birthdayTimestamp);
        }
    }

    $seoMeta = [
        'title' => 'Исполненные желания — Ohmywishes',
        'description' => 'Каталог ваших исполненных желаний с возможностью просмотра и управления карточками.',
        'robots' => 'noindex, nofollow',
        'url' => ROUTE_FULFILLED,
        'og_type' => 'website',
        'og_image' => ASSETS_PATH . '/images/icons/icon_logo.png',
        'og_site_name' => 'Ohmywishes'];

    $profileDescriptionLabel = $profileDescription !== ''
        ? $profileDescription
        : 'Описание пользователя';

    $birthdayCountdownLabel = null;
    if ($profileBirthDate !== null){
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
                12 => 'декабря'];

            $monthNumber = (int) $birthDate -> format('n');
            $profileBirthDateLabel = $birthDate -> format('j') . ' ' . ($monthNames[$monthNumber] ?? $birthDate -> format('m'));

            $today = new DateTimeImmutable('today');
            $nextBirthday = $birthDate -> setDate(
                (int) $today -> format('Y'),
                (int) $birthDate -> format('m'),
                (int) $birthDate -> format('d')
            );

            if ($nextBirthday < $today) {
                $nextBirthday = $nextBirthday -> modify('+1 year');
            }

            $daysLeft = (int) $today -> diff($nextBirthday) -> format('%a');
            if ($daysLeft === 0){
                $birthdayCountdownLabel = 'С днем рождения!';
            }elseif ($daysLeft === 1){
                $birthdayCountdownLabel = 'Через 1 день';
            }elseif ($daysLeft >= 2 && $daysLeft <= 4){
                $birthdayCountdownLabel = 'Через ' . $daysLeft . ' дня';
            }else {
                $birthdayCountdownLabel = 'Через ' . $daysLeft . ' дней';
            }
        }else{
            $profileBirthDateLabel = 'Дата рождения не указана';
        }
    }

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
        <script src="<?= e(versioned_asset(ASSETS_PATH . '/scripts/open_modal_card_profile.js')) ?>" defer></script>
        <script src="<?= e(versioned_asset(ASSETS_PATH . '/scripts/create_wish_modal.js')) ?>" defer></script>
        <script src="<?= e(versioned_asset(ASSETS_PATH . '/scripts/form_validation.js')) ?>" defer></script>
        <script src="<?= e(versioned_asset(ASSETS_PATH . '/scripts/main.js')) ?>" defer></script>
    </head>
    <body class="home-page profile-page fulfilled-page">
        <?php
            $currentNav = 'wishes';
            require __DIR__ . '/../includes/header.php';?>

        <main class="home-content profile-content fulfilled-content">
            <div class="home-layout profile-layout fulfilled-layout">
                <?php require __DIR__ . '/../includes/left_sidebar.php'; ?>

                <section class="profile-main fulfilled-main">
                    <?php require __DIR__ . '/../includes/flash_message.php'; ?>

                    <section class="profile-hero">
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
                        <button class="profile-hero__create" type="button" data-create-wish-open>
                            <span class="profile-hero__create-icon" aria-hidden="true"><?= $renderInlineIcon('create_new_wish_icon.svg') ?></span>
                            <span>Создать желание</span>
                        </button>
                    </section>

                    <section class="profile-mobile-shortcuts">
                        <div class="profile-mobile-shortcuts__row">
                            <?php foreach ($profileSidebarLinks as $shortcut): ?>
                                <?php
                                    $shortcutClasses = ['profile-mobile-shortcuts__link'];
                                    $shortcutHref = (string) ($shortcut['href'] ?? '/pages/error.php');
                                    $isShortcutActive = !empty($shortcut['is_active']);
                                    if ($isShortcutActive){
                                        $shortcutClasses[] = 'profile-mobile-shortcuts__link--active';
                                    }
                                    $openCreateWishShortcut = !empty($shortcut['open_create_wish']);
                                ?>
                                <a class="<?= e(implode(' ', $shortcutClasses)) ?>" href="<?= e($shortcutHref) ?>" <?= $openCreateWishShortcut ? 'data-create-wish-open' : '' ?> <?= $isShortcutActive ? 'aria-current="page"' : '' ?>>
                                    <span class="profile-mobile-shortcuts__icon" aria-hidden="true"><?= $renderInlineIcon((string) ($shortcut['icon'] ?? 'for_you_icon.svg')) ?></span>
                                    <span class="profile-mobile-shortcuts__text"><?= e((string) ($shortcut['title'] ?? '')) ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <section class="profile-wishes fulfilled-wishes">
                        <div class="profile-wishes__header">
                            <h2 class="profile-wishes__title">
                                <span>Исполненные желания</span>
                                <span class="profile-wishes__count"><?= e((string) $fulfilledCount) ?></span>
                            </h2>
                        </div>

                        <div class="profile-wishes__grid wish-catalog__grid fulfilled-wishes__grid">
                            <?php if ($fulfilledCount === 0): ?>
                                <p class="wish-catalog__empty">Пока нет исполненных желаний.</p>
                            <?php else: ?>
                                <?php foreach ($fulfilledWishes as $catalogItem): ?>
                                    <div class="fulfilled-card">
                                        <?php require __DIR__ . '/../includes/catalog_card.php'; ?>
                                        <span class="fulfilled-card__overlay" aria-hidden="true">
                                            <span class="fulfilled-card__check"><?= $renderInlineIcon('fulfilled_wishes_icon.svg') ?></span>
                                        </span>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </section>
                </section>

                <?php require __DIR__ . '/../includes/right_sidebar.php'; ?>
            </div>

            <?php require __DIR__ . '/../includes/create_wish_modal.php'; ?>
            <?php require __DIR__ . '/../includes/open_modal_card_fulfilled.php'; ?>
        </main>

        <?php clear_old_input(); ?>
    </body>
</html>
