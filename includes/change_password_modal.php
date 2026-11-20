<?php

    declare(strict_types=1);
    
    if (!isset($validationErrors) || !is_array($validationErrors)) {
        $validationErrors = [];
    }
    
    $changePasswordGeneralError = field_error($validationErrors, 'change_password');
    $currentPasswordError = field_error($validationErrors, 'current_password');
    $newPasswordError = field_error($validationErrors, 'new_password');
    $confirmPasswordError = field_error($validationErrors, 'confirm_password');
    
    $changePasswordSummaryErrors = array_values(array_filter([
        $changePasswordGeneralError,
        $currentPasswordError,
        $newPasswordError,
        $confirmPasswordError,
    ], static fn (?string $value): bool => is_string($value) && trim($value) !== ''));
?>

<div class="settings-modal change-password-modal" data-change-password-modal data-open-on-load="<?= !empty($openChangePasswordModal) ? '1' : '0' ?>" hidden >
    <div class="settings-modal__backdrop" data-change-password-close></div>

    <section class="settings-modal__dialog change-password-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="change-password-title">
        <button class="settings-modal__close" type="button" data-change-password-close>
            <svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="18" cy="18" r="18" fill="#313132"/>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M26.4853 10.9288C26.8758 10.5383 26.8758 9.9051 26.4853 9.51458C26.0948 9.12405 25.4616 9.12405 25.0711 9.51458L17.9999 16.5858L10.9288 9.51469C10.5383 9.12417 9.90513 9.12417 9.5146 9.51469C9.12408 9.90522 9.12408 10.5384 9.5146 10.9289L16.5857 18L9.51472 25.0709C9.12419 25.4614 9.12419 26.0946 9.51472 26.4851C9.90524 26.8757 10.5384 26.8757 10.9289 26.4851L17.9999 19.4142L25.071 26.4853C25.4615 26.8758 26.0946 26.8758 26.4852 26.4853C26.8757 26.0947 26.8757 25.4616 26.4852 25.071L19.4141 18L26.4853 10.9288Z" fill="white"/>
            </svg>
        </button>

        <h2 class="settings-modal__title" id="change-password-title">Изменение пароля</h2>

        <?php if ($changePasswordSummaryErrors !== []): ?>
            <section class="settings-modal__summary" data-change-password-summary role="alert">
                <p>Исправьте ошибки в форме:</p>
                <ul class="settings-modal__summary-list">
                    <?php foreach ($changePasswordSummaryErrors as $summaryError): ?>
                        <li><?= e((string) $summaryError) ?></li>
                    <?php endforeach; ?>
                </ul>
            </section>
        <?php else: ?>
            <section class="settings-modal__summary settings-modal__summary--hidden" data-change-password-summary hidden></section>
        <?php endif; ?>

        <form class="change-password-modal__form" method="post" action="<?= e(ROUTE_CHANGE_PASSWORD_HANDLER) ?>" novalidate data-change-password-form data-password-min-length="<?= e((string) PASSWORD_MIN_LENGTH) ?>" data-password-max-length="<?= e((string) PASSWORD_MAX_LENGTH) ?>" >
            <input type="hidden" name="redirect_to" value="<?= e(ROUTE_SETTINGS . '?modal=change-password') ?>">
            <?= csrf_field() ?>

            <div class="change-password-modal__field<?= $currentPasswordError ? ' is-invalid' : '' ?>" data-change-password-field="current_password">
                <input class="change-password-modal__input" type="password" id="settings_current_password" name="current_password" placeholder=" " autocomplete="current-password" required >
                <label class="change-password-modal__label" for="settings_current_password">Текущий пароль</label>
                <p class="change-password-modal__field-error" data-change-password-field-error-for="current_password"><?= e((string) $currentPasswordError) ?></p>
            </div>

            <div class="change-password-modal__field<?= $newPasswordError ? ' is-invalid' : '' ?>" data-change-password-field="new_password">
                <input class="change-password-modal__input" type="password" id="settings_new_password" name="new_password" placeholder=" " autocomplete="new-password" required >
                <label class="change-password-modal__label" for="settings_new_password">Новый пароль</label>
                <p class="change-password-modal__field-error" data-change-password-field-error-for="new_password"><?= e((string) $newPasswordError) ?></p>
            </div>

            <div class="change-password-modal__field<?= $confirmPasswordError ? ' is-invalid' : '' ?>" data-change-password-field="confirm_password">
                <input class="change-password-modal__input" type="password" id="settings_confirm_password" name="confirm_password" placeholder=" " autocomplete="new-password" required >
                <label class="change-password-modal__label" for="settings_confirm_password">Подтверждение пароля</label>
                <p class="change-password-modal__field-error" data-change-password-field-error-for="confirm_password"><?= e((string) $confirmPasswordError) ?></p>
            </div>

            <button class="change-password-modal__submit" type="submit">Сохранить</button>
        </form>
    </section>
</div>