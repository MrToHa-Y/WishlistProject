<?php

    declare(strict_types=1);

    require_once __DIR__ . '/../config/constants.php';
    require_once __DIR__ . '/../core/functions.php';
    require_once __DIR__ . '/../core/auth.php';
    require_once __DIR__ . '/../core/csrf.php';

    $currentPath = normalize_redirect_path((string) ($_SERVER['REQUEST_URI'] ?? ROUTE_SEARCH));
    if ($currentPath === ''){
        $currentPath = ROUTE_SEARCH;
    }

    $loginWithRedirect = make_route_with_redirect(ROUTE_LOGIN, $currentPath);
    $isAuthenticated = is_authenticated();

    $linkIconSvg = '';
    if (isset($renderInlineIcon) && is_callable($renderInlineIcon)){
        $linkIconSvg = (string) $renderInlineIcon('link_icon.svg');
    }

    $promoIconSvg = '';
    if (isset($renderInlineIcon) && is_callable($renderInlineIcon)){
        $promoIconSvg = (string) $renderInlineIcon('percent_icon.svg');
    }

    $defaultAvatar = ASSETS_PATH . '/images/default_avatar.png';
?>

<div class="open-card-modal open-card-modal--reserve" data-reserve-wish-modal data-authenticated="<?= $isAuthenticated ? '1' : '0' ?>" data-login-url="<?= e($loginWithRedirect) ?>" data-default-avatar="<?= e($defaultAvatar) ?>" hidden >
    <div class="open-card-modal__backdrop" data-reserve-wish-modal-close></div>

    <section class="open-card-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="reserve-wish-modal-title">
        <button class="open-card-modal__close" type="button" data-reserve-wish-modal-close>
            <svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="18" cy="18" r="18" fill="#313132"/>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M26.4853 10.9288C26.8758 10.5383 26.8758 9.9051 26.4853 9.51458C26.0948 9.12405 25.4616 9.12405 25.0711 9.51458L17.9999 16.5858L10.9288 9.51469C10.5383 9.12417 9.90513 9.12417 9.5146 9.51469C9.12408 9.90522 9.12408 10.5384 9.5146 10.9289L16.5857 18L9.51472 25.0709C9.12419 25.4614 9.12419 26.0946 9.51472 26.4851C9.90524 26.8757 10.5384 26.8757 10.9289 26.4851L17.9999 19.4142L25.071 26.4853C25.4615 26.8758 26.0946 26.8758 26.4852 26.4853C26.8757 26.0947 26.8757 25.4616 26.4852 25.071L19.4141 18L26.4853 10.9288Z" fill="white"/>
            </svg>
        </button>

        <div class="open-card-modal__grid">
            <div class="open-card-modal__media-wrap">
                <img class="open-card-modal__media" src="" alt="" data-reserve-wish-modal-image>
            </div>

            <div class="open-card-modal__content">
                <a class="reserve-wish-modal__owner" href="#" data-reserve-wish-modal-owner-link>
                    <img class="reserve-wish-modal__owner-avatar" src="<?= e($defaultAvatar) ?>" alt="" data-reserve-wish-modal-owner-avatar>
                    <span class="reserve-wish-modal__owner-name" data-reserve-wish-modal-owner-name></span>
                </a>

                <h2 class="open-card-modal__title" id="reserve-wish-modal-title" data-reserve-wish-modal-title></h2>
                <p class="open-card-modal__description" data-reserve-wish-modal-description></p>
                <button class="open-card-modal__description-toggle" type="button" data-reserve-wish-modal-description-toggle hidden>Подробнее</button>

                <div class="open-card-modal__buy-row" data-reserve-wish-modal-buy-row>
                    <div class="open-card-modal__price-wrap">
                        <p class="open-card-modal__price" data-reserve-wish-modal-price></p>
                        <p class="open-card-modal__promo" data-reserve-wish-modal-promo hidden>
                            <span class="open-card-modal__promo-icon" aria-hidden="true"><?= $promoIconSvg ?></span>
                            <span data-reserve-wish-modal-promo-label></span>
                            <span class="open-card-modal__promo-separator" data-reserve-wish-modal-promo-separator hidden></span>
                            <span data-reserve-wish-modal-promo-value></span>
                        </p>
                    </div>

                    <a class="open-card-modal__link" href="#" target="_blank" rel="noopener noreferrer" data-reserve-wish-modal-link>
                        <span>Где купить</span>
                        <span class="open-card-modal__link-icon" aria-hidden="true"><?= $linkIconSvg ?></span>
                    </a>
                </div>

                <form class="open-card-modal__save-form" action="<?= e(ROUTE_RESERVE_WISH_HANDLER) ?>" method="post" data-reserve-wish-modal-reserve-form>
                    <?= csrf_field() ?>
                    <input type="hidden" name="wish_id" value="" data-reserve-wish-modal-wish-id>
                    <input type="hidden" name="redirect_to" value="<?= e($currentPath) ?>">
                    <button class="open-card-modal__save" type="submit" data-reserve-wish-modal-reserve-button>Забронировать</button>
                </form>

                <form class="open-card-modal__save-form" action="<?= e(ROUTE_UNRESERVE_WISH_HANDLER) ?>" method="post" data-reserve-wish-modal-unreserve-form hidden>
                    <?= csrf_field() ?>
                    <input type="hidden" name="wish_id" value="" data-reserve-wish-modal-unreserve-id>
                    <input type="hidden" name="redirect_to" value="<?= e($currentPath) ?>">
                    <button class="open-card-modal__save open-card-modal__save--danger" type="submit">Отменить бронирование</button>
                </form>

            </div>
        </div>
    </section>
</div>