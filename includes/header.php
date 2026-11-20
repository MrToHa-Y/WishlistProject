<?php

    declare(strict_types=1);

    require_once __DIR__ . '/../config/constants.php';
    require_once __DIR__ . '/../core/session.php';
    require_once __DIR__ . '/../core/functions.php';
    require_once __DIR__ . '/../core/auth.php';
    require_once __DIR__ . '/../core/csrf.php';

    $currentNav = isset($currentNav) ? (string) $currentNav : 'ideas';
    $isAuthenticated = is_authenticated();

    $currentRequest = normalize_redirect_path((string) ($_SERVER['REQUEST_URI'] ?? ''));
    if ($currentRequest === ''){
        $currentRequest = ROUTE_HOME;
    }

    $guardedHref = static function (string $privateRoute) use ($isAuthenticated): string{
        if ($isAuthenticated){
            return $privateRoute;
        }

        return make_route_with_redirect(ROUTE_LOGIN, $privateRoute);
    };

    $signinHref = make_route_with_redirect(ROUTE_LOGIN, $currentRequest);

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

    $navItems = [
        ['key' => 'ideas',
         'label' => 'Идеи',
         'icon' => 'ideas_icon.svg',
         'active_icon' => 'ideas_active_icon.svg',
         'href' => ROUTE_HOME],
        ['key' => 'wishes',
         'label' => 'Мои желания',
         'icon' => 'my_wishes_icon.svg',
         'active_icon' => 'my_wishes_active_icon.svg',
         'href' => $guardedHref('/pages/profile.php')],
        ['key' => 'friends',
         'label' => 'Друзья',
         'icon' => 'friends_icon.svg',
         'active_icon' => 'friends_active_icon.svg',
         'href' => $guardedHref(ROUTE_SEARCH)],
        ['key' => 'secret_santa',
         'label' => 'Секретный Санта',
         'icon' => 'secret_santa_icon.svg',
         'href' => $guardedHref('/pages/error.php')]];

    $menuLinks = [
        ['label' => 'Найти друзей',
         'icon' => 'find_friends_icon.svg',
         'href' => ROUTE_SEARCH],
        ['label' => 'Поделиться желаниями',
         'icon' => 'share_your_wishes.svg',
         'href' => '/pages/profile.php'],
        ['label' => 'Настройки',
         'icon' => 'settings_icon.svg',
         'href' => '/pages/settings.php'],
        ['label' => 'For business',
         'icon' => 'for_business_icon.svg',
         'href' => '/pages/error.php',
         'is_accent' => true]];

    $storeLinks = [
        ['label' => 'App Store', 
         'image' => ASSETS_PATH . '/images/app_store.svg', 
         'href' => 'https://www.apple.com/app-store/'],
        ['label' => 'Google Play', 
         'image' => ASSETS_PATH . '/images/google_play.svg', 
         'href' => 'https://play.google.com/store'],
        ['label' => 'RuStore', 
         'image' => ASSETS_PATH . '/images/ru_store.svg', 
         'href' => 'https://www.rustore.ru/'],
        ['label' => 'AppGallery', 
         'image' => ASSETS_PATH . '/images/app_gallery.svg', 
         'href' => 'https://appgallery.huawei.com/']];
?>

<header class="home-header" data-home-header>
    <div class="home-header__inner">
        <a class="home-header__logo" href="<?= e(ROUTE_HOME) ?>">
            <img src="<?= e(ASSETS_PATH . '/images/logo_with_name.svg') ?>" alt="Ohmywishes logo">
        </a>

        <div class="home-header__center">
            <nav class="home-header__nav">
                <?php foreach ($navItems as $item): ?>
                    <?php $navIcon = $currentNav === $item['key'] ? ($item['active_icon'] ?? $item['icon']) : $item['icon']; ?>
                    <a class="home-header__nav-link<?= $currentNav === $item['key'] ? ' home-header__nav-link--active' : '' ?>"href="<?= e($item['href']) ?>"data-tooltip="<?= e($item['label']) ?>">
                        <span class="home-header__icon" aria-hidden="true"><?= $renderInlineIcon($navIcon) ?></span>
                    </a>
                <?php endforeach; ?>
            </nav>

            <button class="home-header__search-toggle" type="button" data-header-search-toggle data-tooltip="Поиск">
                <span class="home-header__icon" aria-hidden="true"><?= $renderInlineIcon('search_icon.svg') ?></span>
            </button>
        </div>

        <div class="home-header__right">
            <?php if (!$isAuthenticated): ?>
                <a class="home-header__signin" href="<?= e($signinHref) ?>">Вход</a>
            <?php else: ?>
                <button class="home-header__menu-toggle" type="button" data-header-user-toggle aria-expanded="false" >
                    <span></span>
                    <span></span>
                </button>

                <div class="home-header__user-menu" data-header-user-menu hidden>
                    <nav class="home-header__user-nav">
                        <?php foreach ($menuLinks as $link): ?>
                            <a class="home-header__user-link<?= !empty($link['is_accent']) ? ' home-header__user-link--accent' : '' ?>" href="<?= e($link['href']) ?>" >
                                <span class="home-header__icon home-header__icon--menu" aria-hidden="true"><?= $renderInlineIcon($link['icon']) ?></span>
                                <span><?= e($link['label']) ?></span>
                            </a>
                        <?php endforeach; ?>
                    </nav>

                    <form class="home-header__signout-form" method="post" action="<?= e(ROUTE_LOGOUT_HANDLER) ?>">
                        <?= csrf_field() ?>
                        <button class="home-header__signout" type="submit">
                            <span class="home-header__icon home-header__icon--menu" aria-hidden="true"><?= $renderInlineIcon('sign_out_icon.svg') ?></span>
                            <span>Выход из аккаунта</span>
                        </button>
                    </form>

                    <div class="home-header__stores">
                        <?php foreach ($storeLinks as $store): ?>
                            <a class="home-header__store-link" href="<?= e($store['href']) ?>" target="_blank" rel="noopener noreferrer">
                                <img src="<?= e($store['image']) ?>" alt="<?= e($store['label']) ?>">
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</header>

<div class="home-header__search-layer" data-header-search-layer hidden>
    <div class="home-header__search-panel" data-header-search-panel>
        <span class="home-header__icon" aria-hidden="true"><?= $renderInlineIcon('search_icon.svg') ?></span>
        <input type="text" placeholder="Поиск" data-header-search-input>
    </div>
</div>