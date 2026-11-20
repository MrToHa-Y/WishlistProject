<?php

    declare(strict_types=1);
    
    require_once __DIR__ . '/../config/constants.php';
    require_once __DIR__ . '/../core/session.php';
    require_once __DIR__ . '/../core/functions.php';
    require_once __DIR__ . '/../core/auth.php';
    require_once __DIR__ . '/../core/seo.php';
    
    $refererPath = '';
    $rawReferer = trim((string) ($_SERVER['HTTP_REFERER'] ?? ''));
    if ($rawReferer !== ''){
        $refererParts = parse_url($rawReferer);
        if (is_array($refererParts)){
            $refererHost = (string) ($refererParts['host'] ?? '');
            $currentHost = (string) ($_SERVER['HTTP_HOST'] ?? '');
    
            if ($refererHost === '' || strcasecmp($refererHost, $currentHost) === 0){
                $path = (string) ($refererParts['path'] ?? '');
                if ($path !== ''){
                    $refererPath = $path;
                    if (!empty($refererParts['query'])){
                        $refererPath .= '?' . $refererParts['query'];
                    }
                }
            }
        }
    }
    
    $sourcePath = normalize_redirect_path((string) ($_GET['from'] ?? ''));
    if ($sourcePath === ''){
        $sourcePath = normalize_redirect_path($refererPath);
    }
    if ($sourcePath === ''){
        $sourcePath = ROUTE_HOME;
    }
    
    $sourcePathOnly = (string) (parse_url($sourcePath, PHP_URL_PATH) ?? '');
    if ($sourcePathOnly === '/pages/error.php'){
        $sourcePath = ROUTE_HOME;
        $sourcePathOnly = ROUTE_HOME;
    }
    
    $wishesPaths = [
        ROUTE_PROFILE,
        ROUTE_SETTINGS,
        ROUTE_FULFILLED,
        '/pages/reservations.php'];
    
    $currentNav = 'ideas';
    if ($sourcePathOnly === '/pages/search.php'){
        $currentNav = 'friends';
    } elseif (in_array($sourcePathOnly, $wishesPaths, true)){
        $currentNav = 'wishes';
    }
    
    $navOverride = (string) ($_GET['nav'] ?? '');
    if (in_array($navOverride, ['ideas', 'wishes', 'friends', 'secret_santa'], true)){
        $currentNav = $navOverride;
    }
    
    $backUrl = $sourcePath;
    $iconLogoPath = __DIR__ . '/../assets/images/icons/icon_logo.svg';
    $iconLogoSrc = ASSETS_PATH . '/images/icons/icon_logo.svg';
    if (is_file($iconLogoPath)){
        $iconLogoSrc .= '?v=' . (string) filemtime($iconLogoPath);
    }
    
    $seoMeta = [
        'title' => '404 — страница в разработке | Ohmywishes',
        'description' => 'Эта страница пока находится в разработке. Вернитесь на предыдущую страницу и продолжите работу с сервисом.',
        'robots' => 'noindex, nofollow',
        'url' => '/pages/error.php',
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
        <script src="<?= e(versioned_asset(ASSETS_PATH . '/scripts/modal.js')) ?>" defer></script>
        <script src="<?= e(versioned_asset(ASSETS_PATH . '/scripts/background_photo_animation.js')) ?>" defer></script>
        <script src="<?= e(versioned_asset(ASSETS_PATH . '/scripts/main.js')) ?>" defer></script>
    </head>
    <body class="home-page error-page">
        <?php require __DIR__ . '/../includes/header.php'; ?>

        <main class="home-content error-page__content">
            <section class="error-page__main" aria-labelledby="error-title">
                <div class="error-page__code" aria-hidden="true">
                    <span class="error-page__digit">4</span>

                    <span class="error-page__logo-zero">
                        <img class="error-page__logo-bg" src="<?= e(ASSETS_PATH . '/images/icons/parts_main_logo/bacground_part.svg') ?>" alt="">
                        <img class="error-page__logo-name" src="<?= e(ASSETS_PATH . '/images/icons/parts_main_logo/name_part.svg') ?>" alt="">
                        <img class="error-page__logo-heart" src="<?= e(ASSETS_PATH . '/images/icons/parts_main_logo/heart_part.svg') ?>" alt="">
                    </span>

                    <span class="error-page__digit">4</span>
                </div>

                <h1 class="error-page__title" id="error-title">Сайт находится в разработке</h1>
                <p class="error-page__subtitle">
                    <span>С уважением сервис Ohmywishes</span>
                    <img class="error-page__subtitle-logo" src="<?= e($iconLogoSrc) ?>" alt="" aria-hidden="true">
                </p>

                <a class="error-page__back-button" href="<?= e($backUrl) ?>">Вернуться на прошлую страницу</a>
            </section>
        </main>

    </body>
</html>
