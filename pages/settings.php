<?php

    declare(strict_types=1);

    require_once __DIR__ . '/../config/constants.php';
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../core/session.php';
    require_once __DIR__ . '/../core/functions.php';
    require_once __DIR__ . '/../core/auth.php';
    require_once __DIR__ . '/../core/csrf.php';
    require_once __DIR__ . '/../core/seo.php';

    require_auth(make_route_with_redirect(ROUTE_LOGIN, ROUTE_SETTINGS));

    $user = auth_user();
    $userId = (int) ($user['id'] ?? 0);

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

    $resolveAvatarPath = static function (?string $rawAvatarPath) use ($defaultAvatar): string {
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

    $profileData = [
        'username' => trim((string) ($user['username'] ?? '')),
        'first_name' => trim((string) ($user['first_name'] ?? '')),
        'last_name' => trim((string) ($user['last_name'] ?? '')),
        'email' => trim((string) ($user['email'] ?? '')),
        'avatar' => '',
        'birth_date' => '',
        'gender' => '',
        'description' => ''];

    try{
        $pdo = db();
    }catch (Throwable $exception){
        $pdo = null;
    }

    if ($pdo instanceof PDO && $userId > 0){
        try {
            $statement = $pdo -> prepare(
                'SELECT username, first_name, last_name, email, avatar, birth_date, gender, description
                 FROM users
                 WHERE id = :id
                 LIMIT 1'
            );
            $statement -> execute(['id' => $userId]);
            $dbUser = $statement -> fetch();

            if (is_array($dbUser)){
                $profileData['username'] = trim((string) ($dbUser['username'] ?? $profileData['username']));
                $profileData['first_name'] = trim((string) ($dbUser['first_name'] ?? $profileData['first_name']));
                $profileData['last_name'] = trim((string) ($dbUser['last_name'] ?? $profileData['last_name']));
                $profileData['email'] = trim((string) ($dbUser['email'] ?? $profileData['email']));
                $profileData['avatar'] = trim((string) ($dbUser['avatar'] ?? ''));
                $profileData['birth_date'] = trim((string) ($dbUser['birth_date'] ?? ''));
                $profileData['gender'] = trim((string) ($dbUser['gender'] ?? ''));
                $profileData['description'] = trim((string) ($dbUser['description'] ?? ''));
            }
        }catch (Throwable $exception){
           
        }
    }

    $validationErrors = get_validation_errors();
    $requestedModal = trim((string) ($_GET['modal'] ?? ''));

    $updateProfileErrorKeys = ['profile', 
                               'username', 
                               'first_name', 
                               'last_name', 
                               'description', 
                               'birth_date', 
                               'gender', 
                               'avatar'];
    $changeEmailErrorKeys = ['change_email', 
                             'change_email_email'];
    $changePasswordErrorKeys = ['change_password', 
                                'current_password', 
                                'new_password', 
                                'confirm_password'];

    $hasErrorFromKeys = static function (array $errors, array $keys): bool {
        foreach ($keys as $key){
            if (!empty($errors[$key])){
                return true;
            }
        }

        return false;
    };

    $openUpdateProfileModal = $requestedModal === 'update-profile' || $hasErrorFromKeys($validationErrors, $updateProfileErrorKeys);
    $openChangeEmailModal = $requestedModal === 'change-email' || $hasErrorFromKeys($validationErrors, $changeEmailErrorKeys);
    $openChangePasswordModal = $requestedModal === 'change-password' || $hasErrorFromKeys($validationErrors, $changePasswordErrorKeys);
    $openDeleteConfirmModal = $requestedModal === 'confirm-delete';

    $profileAvatar = $resolveAvatarPath($profileData['avatar']);
    $hasCustomAvatar = $profileData['avatar'] !== '';
    $displayName = trim($profileData['first_name'] . ' ' . $profileData['last_name']);
    if ($displayName === ''){
        $displayName = $profileData['username'] !== '' ? $profileData['username'] : 'Пользователь';
    }

    $displayDescription = $profileData['description'] !== ''
        ? $profileData['description']
        : 'Описание профиля не указано';

    $birthDateValue = $profileData['birth_date'];
    $displayBirthDate = 'Дата рождения не указана';
    if ($birthDateValue !== ''){
        $birthDateTimestamp = strtotime($birthDateValue);
        if ($birthDateTimestamp !== false){
            $displayBirthDate = date('d.m.Y', $birthDateTimestamp);
        }
    }

    $genderValue = in_array($profileData['gender'], ['male', 'female'], true) ? $profileData['gender'] : '';
    $displayGender = match ($genderValue){
        'male' => 'Мужской',
        'female' => 'Женский',
        default => 'Пол не указан',
    };

    $updateProfileValues = [
        'username' => old('settings_username', $profileData['username']),
        'first_name' => old('settings_first_name', $profileData['first_name']),
        'last_name' => old('settings_last_name', $profileData['last_name']),
        'description' => old('settings_description', $profileData['description']),
        'birth_date' => old('settings_birth_date', $birthDateValue),
        'gender' => old('settings_gender', $genderValue)];

    $changeEmailValue = old('settings_new_email', $profileData['email']);

    $seoMeta = [
        'title' => 'Настройки профиля — Ohmywishes',
        'description' => 'Страница управления профилем: личные данные, смена почты, смена пароля и действия с аккаунтом.',
        'robots' => 'noindex, nofollow',
        'url' => ROUTE_SETTINGS,
        'og_type' => 'website',
        'og_image' => ASSETS_PATH . '/images/icons/icon_logo.png',
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
        <script src="<?= e(versioned_asset(ASSETS_PATH . '/scripts/update_profile_modal.js')) ?>" defer></script>
        <script src="<?= e(versioned_asset(ASSETS_PATH . '/scripts/change_email_modal.js')) ?>" defer></script>
        <script src="<?= e(versioned_asset(ASSETS_PATH . '/scripts/change_password_modal.js')) ?>" defer></script>
        <script src="<?= e(versioned_asset(ASSETS_PATH . '/scripts/confirm_modal.js')) ?>" defer></script>
        <script src="<?= e(versioned_asset(ASSETS_PATH . '/scripts/form_validation.js')) ?>" defer></script>
        <script src="<?= e(versioned_asset(ASSETS_PATH . '/scripts/main.js')) ?>" defer></script>
    </head>
    <body class="home-page settings-page">
        <?php
            $currentNav = 'ideas';
            require __DIR__ . '/../includes/header.php';?>

        <main class="home-content settings-content">
            <div class="home-layout settings-layout">
                <?php require __DIR__ . '/../includes/left_sidebar.php'; ?>

                <section class="settings-main">
                    <?php require __DIR__ . '/../includes/flash_message.php'; ?>

                    <section class="settings-section">
                        <h2 class="settings-section__title">Основная информация</h2>
                        <article class="settings-card settings-card--profile">
                            <img class="settings-card__avatar" src="<?= e($profileAvatar) ?>" alt="<?= e($displayName) ?>">

                            <div class="settings-card__profile-info">
                                <h3 class="settings-card__name"><?= e($displayName) ?></h3>
                                <p class="settings-card__description"><?= e($displayDescription) ?></p>

                                <div class="settings-card__meta">
                                    <span><?= e($profileData['username'] !== '' ? '@' . $profileData['username'] : 'username не указан') ?></span>
                                    <span><?= e($displayBirthDate) ?></span>
                                    <span><?= e($displayGender) ?></span>
                                </div>
                            </div>

                            <button class="settings-card__edit" type="button" data-update-profile-open>
                                <?= $renderInlineIcon('edit_icon.svg') ?>
                            </button>
                        </article>
                    </section>

                    <section class="settings-section">
                        <h2 class="settings-section__title">Управление данными</h2>
                        <article class="settings-card settings-card--management">
                            <div class="settings-management-row">
                                <span class="settings-management-row__icon settings-management-row__icon--email" aria-hidden="true"><?= $renderInlineIcon('email_icon.svg') ?></span>
                                <span class="settings-management-row__value"><?= e($profileData['email']) ?></span>
                                <button class="settings-management-row__action" type="button" data-change-email-open>Изменить</button>
                            </div>

                            <div class="settings-management-row">
                                <span class="settings-management-row__icon settings-management-row__icon--password" aria-hidden="true"><?= $renderInlineIcon('change_password_icon.svg') ?></span>
                                <span class="settings-management-row__value">Изменить пароль</span>
                                <button class="settings-management-row__action" type="button" data-change-password-open>
                                    Изменить
                                </button>
                            </div>
                        </article>
                    </section>

                    <section class="settings-section">
                        <h2 class="settings-section__title">Действия</h2>
                        <article class="settings-card settings-card--actions">
                            <form class="settings-signout-form" method="post" action="<?= e(ROUTE_LOGOUT_HANDLER) ?>">
                                <?= csrf_field() ?>
                                <button class="settings-signout" type="submit">
                                    <span class="settings-signout__icon" aria-hidden="true"><?= $renderInlineIcon('sign_out_icon.svg') ?></span>
                                    <span>Выход из аккаунта</span>
                                </button>
                            </form>
                        </article>

                        <button class="settings-delete-account" type="button" data-confirm-open>
                            <span class="settings-delete-account__icon" aria-hidden="true"><?= $renderInlineIcon('delete_icon.svg') ?></span>
                            <span>Удалить аккаунт</span>
                        </button>
                    </section>
                </section>

                <?php require __DIR__ . '/../includes/right_sidebar.php'; ?>
            </div>
        </main>

        <?php require __DIR__ . '/../includes/update_profile_modal.php'; ?>
        <?php require __DIR__ . '/../includes/change_email_modal.php'; ?>
        <?php require __DIR__ . '/../includes/change_password_modal.php'; ?>
        <?php require __DIR__ . '/../includes/confirm_modal.php'; ?>

        <?php clear_old_input(); ?>
    </body>
</html>
