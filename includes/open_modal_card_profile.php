<?php

    declare(strict_types=1);

    require_once __DIR__ . '/../config/constants.php';
    require_once __DIR__ . '/../core/functions.php';
    require_once __DIR__ . '/../core/auth.php';

    $currentPath = normalize_redirect_path((string) ($_SERVER['REQUEST_URI'] ?? ROUTE_PROFILE));
    if ($currentPath === ''){
        $currentPath = ROUTE_PROFILE;
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

    $editIconSvg = '';
    if (isset($renderInlineIcon) && is_callable($renderInlineIcon)){
        $editIconSvg = (string) $renderInlineIcon('edit_icon.svg');
    }

    $deleteIconSvg = '';
    if (isset($renderInlineIcon) && is_callable($renderInlineIcon)){
        $deleteIconSvg = (string) $renderInlineIcon('delete_icon.svg');
    }
?>

<div class="open-card-modal open-card-modal--profile" data-open-card-modal-profile data-authenticated="<?= $isAuthenticated ? '1' : '0' ?>" data-login-url="<?= e($loginWithRedirect) ?>" hidden>
    <div class="open-card-modal__backdrop" data-open-card-modal-profile-close></div>

    <section class="open-card-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="open-card-modal-profile-title">
        <button class="open-card-modal__close" type="button" data-open-card-modal-profile-close>
            <svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="18" cy="18" r="18" fill="#313132"/>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M26.4853 10.9288C26.8758 10.5383 26.8758 9.9051 26.4853 9.51458C26.0948 9.12405 25.4616 9.12405 25.0711 9.51458L17.9999 16.5858L10.9288 9.51469C10.5383 9.12417 9.90513 9.12417 9.5146 9.51469C9.12408 9.90522 9.12408 10.5384 9.5146 10.9289L16.5857 18L9.51472 25.0709C9.12419 25.4614 9.12419 26.0946 9.51472 26.4851C9.90524 26.8757 10.5384 26.8757 10.9289 26.4851L17.9999 19.4142L25.071 26.4853C25.4615 26.8758 26.0946 26.8758 26.4852 26.4853C26.8757 26.0947 26.8757 25.4616 26.4852 25.071L19.4141 18L26.4853 10.9288Z" fill="white"/>
            </svg>
        </button>

        <div class="open-card-modal__grid">
            <div class="open-card-modal__media-wrap">
                <img class="open-card-modal__media" src="" alt="" data-open-card-modal-profile-image>
            </div>

            <div class="open-card-modal__content">
                <h2 class="open-card-modal__title" id="open-card-modal-profile-title" data-open-card-modal-profile-title></h2>
                <p class="open-card-modal__description" data-open-card-modal-profile-description></p>
                <button class="open-card-modal__description-toggle" type="button" data-open-card-modal-profile-description-toggle hidden>Подробнее</button>

                <div class="open-card-modal__buy-row" data-open-card-modal-profile-buy-row>
                    <div class="open-card-modal__price-wrap">
                        <p class="open-card-modal__price" data-open-card-modal-profile-price></p>
                        <p class="open-card-modal__promo" data-open-card-modal-profile-promo hidden>
                            <span class="open-card-modal__promo-icon" aria-hidden="true"><?= $promoIconSvg ?></span>
                            <span data-open-card-modal-profile-promo-label></span>
                            <span class="open-card-modal__promo-separator" data-open-card-modal-profile-promo-separator hidden></span>
                            <span data-open-card-modal-profile-promo-value></span>
                        </p>
                    </div>
                    <a class="open-card-modal__link" href="#" target="_blank" rel="noopener noreferrer" data-open-card-modal-profile-link>
                        <span>Ссылка</span>
                        <span class="open-card-modal__link-icon" aria-hidden="true"><?= $linkIconSvg ?></span>
                    </a>
                </div>

                <form class="open-card-modal__save-form" action="<?= e(ROUTE_ADD_FULFILLED_WISH_HANDLER) ?>" method="post" data-open-card-modal-profile-fulfilled-form>
                    <input type="hidden" name="wish_id" value="" data-open-card-modal-profile-fulfilled-id>
                    <input type="hidden" name="redirect_to" value="<?= e($currentPath) ?>">
                    <button class="open-card-modal__save" type="submit" data-open-card-modal-profile-fulfilled>Исполнено</button>
                </form>

                <div class="open-card-modal__profile-actions">
                    <button class="open-card-modal__profile-action open-card-modal__profile-action--edit" type="button" data-open-card-modal-profile-edit-open>
                        <span class="open-card-modal__profile-action-icon" aria-hidden="true"><?= $editIconSvg ?></span>
                        Редактировать
                    </button>

                    <form class="open-card-modal__profile-delete-form" action="<?= e(ROUTE_DELETE_WISH_HANDLER) ?>" method="post" data-open-card-modal-profile-delete-form>
                        <input type="hidden" name="wish_id" value="" data-open-card-modal-profile-delete-id>
                        <input type="hidden" name="redirect_to" value="<?= e($currentPath) ?>">
                        <button class="open-card-modal__profile-action open-card-modal__profile-action--delete" type="submit">
                            <span class="open-card-modal__profile-action-icon" aria-hidden="true"><?= $deleteIconSvg ?></span>
                            Удалить
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="open-card-modal__edit-layer" data-open-card-modal-profile-edit-layer hidden>
            <div class="open-card-modal__edit-backdrop" data-open-card-modal-profile-edit-close></div>

            <section class="open-card-modal__edit-dialog" role="dialog" aria-modal="true" aria-labelledby="open-card-modal-profile-edit-title">
                <button class="open-card-modal__edit-close" type="button" aria-label="Закрыть редактирование" data-open-card-modal-profile-edit-close>
                    <span></span>
                    <span></span>
                </button>

                <h3 class="open-card-modal__edit-title" id="open-card-modal-profile-edit-title">Редактирование желания</h3>

                <section class="create-wish-modal__summary create-wish-modal__summary--hidden" data-edit-wish-summary hidden></section>

                <form
                    class="open-card-modal__edit-form create-wish-form"
                    action="<?= e(ROUTE_UPDATE_WISH_HANDLER) ?>"
                    method="post"
                    enctype="multipart/form-data"
                    novalidate
                    data-open-card-modal-profile-edit-form
                    data-max-image-size="<?= e((string) WISH_IMAGE_MAX_SIZE_BYTES) ?>"
                >
                    <input type="hidden" name="wish_id" value="" data-open-card-modal-profile-edit-id>
                    <input type="hidden" name="redirect_to" value="<?= e($currentPath) ?>">

                    <div class="create-wish-form__grid">
                        <div class="create-wish-form__media" data-wish-field="image">
                            <input
                                class="create-wish-form__file-input"
                                id="profile_wish_image"
                                name="image"
                                type="file"
                                accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                data-open-card-modal-profile-edit-image-input
                            >

                            <label class="create-wish-form__dropzone" for="profile_wish_image">
                                <span class="create-wish-form__preview" data-open-card-modal-profile-edit-preview>
                                    <span class="create-wish-form__preview-icon" aria-hidden="true">+</span>
                                    <span class="create-wish-form__preview-text">Перетащите изображение или нажмите для загрузки</span>
                                    <span class="create-wish-form__preview-hint">JPG, PNG или WEBP, до 8MB</span>
                                </span>
                            </label>
                            <p class="create-wish-form__field-error" data-wish-field-error-for="image"></p>
                        </div>

                        <div class="create-wish-form__fields">
                            <div class="create-wish-form__field" data-wish-field="title">
                                <input
                                    class="create-wish-form__input"
                                    id="profile_wish_title"
                                    name="title"
                                    type="text"
                                    maxlength="255"
                                    placeholder=" "
                                    required
                                    data-open-card-modal-profile-edit-title-input
                                >
                                <label class="create-wish-form__label" for="profile_wish_title">Название желания</label>
                                <p class="create-wish-form__field-error" data-wish-field-error-for="title"></p>
                            </div>

                            <div class="create-wish-form__field" data-wish-field="product_link">
                                <input
                                    class="create-wish-form__input"
                                    id="profile_wish_product_link"
                                    name="product_link"
                                    type="url"
                                    maxlength="2048"
                                    placeholder=" "
                                    data-open-card-modal-profile-edit-link-input
                                >
                                <label class="create-wish-form__label" for="profile_wish_product_link">Ссылка на товар</label>
                                <p class="create-wish-form__field-error" data-wish-field-error-for="product_link"></p>
                            </div>

                            <div class="create-wish-form__field" data-wish-field="description">
                                <textarea
                                    class="create-wish-form__textarea"
                                    id="profile_wish_description"
                                    name="description"
                                    rows="4"
                                    maxlength="2000"
                                    placeholder=" "
                                    data-open-card-modal-profile-edit-description-input
                                ></textarea>
                                <label class="create-wish-form__label" for="profile_wish_description">Описание</label>
                                <p class="create-wish-form__field-error" data-wish-field-error-for="description"></p>
                            </div>

                            <div class="create-wish-form__field" data-wish-field="price">
                                <input class="create-wish-form__input" id="profile_wish_price" name="price" type="number" min="0" max="99999999" step="0.01" placeholder=" " required data-open-card-modal-profile-edit-price-input >
                                <label class="create-wish-form__label" for="profile_wish_price">Цена</label>
                                <span class="create-wish-form__currency">₸</span>
                                <p class="create-wish-form__field-error" data-wish-field-error-for="price"></p>
                            </div>

                            <button class="create-wish-form__submit open-card-modal__edit-submit" type="submit">Редактировать</button>
                        </div>
                    </div>
                </form>
            </section>
        </div>
    </section>
</div>