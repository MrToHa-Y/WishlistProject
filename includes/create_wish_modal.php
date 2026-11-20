<?php

    declare(strict_types=1);

    if (!isset($validationErrors) || !is_array($validationErrors)){
        $validationErrors = [];
    }

    $wishTitleError = field_error($validationErrors, 'title');
    $wishDescriptionError = field_error($validationErrors, 'description');
    $wishLinkError = field_error($validationErrors, 'product_link');
    $wishPriceError = field_error($validationErrors, 'price');
    $wishImageError = field_error($validationErrors, 'image');
    $wishGeneralError = field_error($validationErrors, 'wish');

    $wishOldValues = [
        'title' => old('wish_title'),
        'description' => old('wish_description'),
        'product_link' => old('wish_product_link'),
        'price' => old('wish_price')];

    $wishErrorsList = array_values(array_filter(
        [
            $wishGeneralError,
            $wishTitleError,
            $wishLinkError,
            $wishDescriptionError,
            $wishPriceError,
            $wishImageError,
        ], static fn ($value): bool => is_string($value) && trim($value) !== ''
    ));
?>

<div class="create-wish-modal" data-create-wish-modal data-open-on-load="<?= !empty($openCreateWishModal) ? '1' : '0' ?>" hidden >
    <div class="create-wish-modal__backdrop" data-create-wish-close></div>

    <section class="create-wish-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="create-wish-title">
        <button class="create-wish-modal__close" type="button" data-create-wish-close>
            <svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="18" cy="18" r="18" fill="#313132"/>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M26.4853 10.9288C26.8758 10.5383 26.8758 9.9051 26.4853 9.51458C26.0948 9.12405 25.4616 9.12405 25.0711 9.51458L17.9999 16.5858L10.9288 9.51469C10.5383 9.12417 9.90513 9.12417 9.5146 9.51469C9.12408 9.90522 9.12408 10.5384 9.5146 10.9289L16.5857 18L9.51472 25.0709C9.12419 25.4614 9.12419 26.0946 9.51472 26.4851C9.90524 26.8757 10.5384 26.8757 10.9289 26.4851L17.9999 19.4142L25.071 26.4853C25.4615 26.8758 26.0946 26.8758 26.4852 26.4853C26.8757 26.0947 26.8757 25.4616 26.4852 25.071L19.4141 18L26.4853 10.9288Z" fill="white"/>
            </svg>
        </button>

        <h2 class="create-wish-modal__title" id="create-wish-title">Новое желание</h2>

        <?php if ($wishErrorsList !== []): ?>
            <section class="create-wish-modal__summary" data-create-wish-summary role="alert">
                <p>Исправьте ошибки в форме:</p>
                <ul class="create-wish-modal__summary-list">
                    <?php foreach ($wishErrorsList as $wishErrorMessage): ?>
                        <li><?= e((string) $wishErrorMessage) ?></li>
                    <?php endforeach; ?>
                </ul>
            </section>
        <?php else: ?>
            <section class="create-wish-modal__summary create-wish-modal__summary--hidden" data-create-wish-summary hidden></section>
        <?php endif; ?>

        <form class="create-wish-form" method="post" action="<?= e(ROUTE_CREATE_WISH_HANDLER) ?>" enctype="multipart/form-data" novalidate data-create-wish-form data-max-image-size="<?= e((string) WISH_IMAGE_MAX_SIZE_BYTES) ?>" >
            <div class="create-wish-form__grid">
                <div class="create-wish-form__media<?= $wishImageError ? ' is-invalid' : '' ?>" data-wish-field="image">
                    <input class="create-wish-form__file-input" id="wish_image" name="image" type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" data-create-wish-file required >

                    <label class="create-wish-form__dropzone" for="wish_image">
                        <span class="create-wish-form__preview" data-create-wish-preview>
                            <span class="create-wish-form__preview-icon" aria-hidden="true">+</span>
                            <span class="create-wish-form__preview-text">Перетащите изображение или нажмите для загрузки</span>
                            <span class="create-wish-form__preview-hint">JPG, PNG или WEBP, до 8MB</span>
                        </span>
                    </label>
                    <p class="create-wish-form__field-error" data-wish-field-error-for="image">
                        <?= e((string) $wishImageError) ?>
                    </p>
                </div>

                <div class="create-wish-form__fields">
                    <div class="create-wish-form__field<?= $wishTitleError ? ' is-invalid' : '' ?>" data-wish-field="title">
                        <input class="create-wish-form__input" id="wish_title" name="title" type="text" maxlength="255" placeholder=" " value="<?= e($wishOldValues['title']) ?>" required >
                        <label class="create-wish-form__label" for="wish_title">Название желания</label>
                        <p class="create-wish-form__field-error" data-wish-field-error-for="title">
                            <?= e((string) $wishTitleError) ?>
                        </p>
                    </div>

                    <div class="create-wish-form__field<?= $wishLinkError ? ' is-invalid' : '' ?>" data-wish-field="product_link">
                        <input class="create-wish-form__input" id="wish_product_link" name="product_link" type="url" maxlength="2048" placeholder=" " value="<?= e($wishOldValues['product_link']) ?>" >
                        <label class="create-wish-form__label" for="wish_product_link">Ссылка на товар</label>
                        <p class="create-wish-form__field-error" data-wish-field-error-for="product_link">
                            <?= e((string) $wishLinkError) ?>
                        </p>
                    </div>

                    <div class="create-wish-form__field<?= $wishDescriptionError ? ' is-invalid' : '' ?>" data-wish-field="description">
                        <textarea class="create-wish-form__textarea" id="wish_description" name="description" rows="4" maxlength="2000" placeholder=" "><?= e($wishOldValues['description']) ?></textarea>
                        <label class="create-wish-form__label" for="wish_description">Описание</label>
                        <p class="create-wish-form__field-error" data-wish-field-error-for="description">
                            <?= e((string) $wishDescriptionError) ?>
                        </p>
                    </div>

                    <div class="create-wish-form__field<?= $wishPriceError ? ' is-invalid' : '' ?>" data-wish-field="price">
                        <input class="create-wish-form__input" id="wish_price" name="price" type="number" min="0" max="99999999" step="0.01" placeholder=" " value="<?= e($wishOldValues['price']) ?>" required >
                        <label class="create-wish-form__label" for="wish_price">Цена</label>
                        <span class="create-wish-form__currency">₸</span>
                        <p class="create-wish-form__field-error" data-wish-field-error-for="price">
                            <?= e((string) $wishPriceError) ?>
                        </p>
                    </div>

                    <button class="create-wish-form__submit" type="submit">Создать желание</button>
                </div>
            </div>
        </form>
    </section>
</div>