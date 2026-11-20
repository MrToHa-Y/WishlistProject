<?php

    declare(strict_types=1);

    if (!isset($validationErrors) || !is_array($validationErrors)) {
        $validationErrors = [];
    }

    $changeEmailFieldError = field_error($validationErrors, 'change_email_email');
    $changeEmailGeneralError = field_error($validationErrors, 'change_email');
    $changeEmailSummaryErrors = array_values(array_filter([
        $changeEmailGeneralError,
        $changeEmailFieldError,
    ], static fn (?string $value): bool => is_string($value) && trim($value) !== ''));
?>

<div class="settings-modal change-email-modal" data-change-email-modal data-open-on-load="<?= !empty($openChangeEmailModal) ? '1' : '0' ?>" hidden >
    <div class="settings-modal__backdrop" data-change-email-close></div>

    <section class="settings-modal__dialog change-email-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="change-email-title">
        <button class="settings-modal__close" type="button" data-change-email-close>
            <svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="18" cy="18" r="18" fill="#313132"/>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M26.4853 10.9288C26.8758 10.5383 26.8758 9.9051 26.4853 9.51458C26.0948 9.12405 25.4616 9.12405 25.0711 9.51458L17.9999 16.5858L10.9288 9.51469C10.5383 9.12417 9.90513 9.12417 9.5146 9.51469C9.12408 9.90522 9.12408 10.5384 9.5146 10.9289L16.5857 18L9.51472 25.0709C9.12419 25.4614 9.12419 26.0946 9.51472 26.4851C9.90524 26.8757 10.5384 26.8757 10.9289 26.4851L17.9999 19.4142L25.071 26.4853C25.4615 26.8758 26.0946 26.8758 26.4852 26.4853C26.8757 26.0947 26.8757 25.4616 26.4852 25.071L19.4141 18L26.4853 10.9288Z" fill="white"/>
            </svg>
        </button>

        <h2 class="settings-modal__title" id="change-email-title">Изменение почты</h2>

        <?php if ($changeEmailSummaryErrors !== []): ?>
            <section class="settings-modal__summary" data-change-email-summary role="alert">
                <p>Исправьте ошибки в форме:</p>
                <ul class="settings-modal__summary-list">
                    <?php foreach ($changeEmailSummaryErrors as $summaryError): ?>
                        <li><?= e((string) $summaryError) ?></li>
                    <?php endforeach; ?>
                </ul>
            </section>
        <?php else: ?>
            <section class="settings-modal__summary settings-modal__summary--hidden" data-change-email-summary hidden></section>
        <?php endif; ?>

        <form class="change-email-modal__form" method="post" action="<?= e(ROUTE_CHANGE_EMAIL_HANDLER) ?>" novalidate data-change-email-form >
            <input type="hidden" name="redirect_to" value="<?= e(ROUTE_SETTINGS . '?modal=change-email') ?>">
            <?= csrf_field() ?>

            <div class="change-email-modal__field<?= $changeEmailFieldError ? ' is-invalid' : '' ?>" data-change-email-field="email">
                <input class="change-email-modal__input" type="email" id="settings_new_email" name="email" maxlength="<?= e((string) EMAIL_MAX_LENGTH) ?>" placeholder=" " value="<?= e((string) $changeEmailValue) ?>" required >
                <label class="change-email-modal__label" for="settings_new_email">Новая почта</label>
                <p class="change-email-modal__field-error" data-change-email-field-error-for="email"><?= e((string) $changeEmailFieldError) ?></p>
            </div>

            <button class="change-email-modal__submit" type="submit">Сохранить</button>
        </form>
    </section>
</div>