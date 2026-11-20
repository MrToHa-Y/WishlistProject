<?php

    declare(strict_types=1);

    if (!isset($validationErrors) || !is_array($validationErrors)) {
        $validationErrors = [];
    }

    if (!isset($updateProfileValues) || !is_array($updateProfileValues)) {
        $updateProfileValues = [
            'username' => '',
            'first_name' => '',
            'last_name' => '',
            'description' => '',
            'birth_date' => '',
            'gender' => ''
        ];
    }

    $profileError = field_error($validationErrors, 'profile');
    $avatarError = field_error($validationErrors, 'avatar');
    $usernameError = field_error($validationErrors, 'username');
    $firstNameError = field_error($validationErrors, 'first_name');
    $lastNameError = field_error($validationErrors, 'last_name');
    $descriptionError = field_error($validationErrors, 'description');
    $birthDateError = field_error($validationErrors, 'birth_date');
    $genderError = field_error($validationErrors, 'gender');

    $summaryErrors = array_values(array_filter([
        $profileError,
        $avatarError,
        $usernameError,
        $firstNameError,
        $lastNameError,
        $descriptionError,
        $birthDateError,
        $genderError
    ], static fn (?string $value): bool => is_string($value) && trim($value) !== ''));
?>

<div class="settings-modal update-profile-modal" data-update-profile-modal data-open-on-load="<?= !empty($openUpdateProfileModal) ? '1' : '0' ?>" data-has-avatar="<?= !empty($hasCustomAvatar) ? '1' : '0' ?>" hidden >
    <div class="settings-modal__backdrop" data-update-profile-close></div>

    <section class="settings-modal__dialog update-profile-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="update-profile-title">
        <button class="settings-modal__close" type="button" data-update-profile-close>
            <svg width="36" height="36" viewBox="0 0 36 36" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="18" cy="18" r="18" fill="#313132"/>
                <path fill-rule="evenodd" clip-rule="evenodd" d="M26.4853 10.9288C26.8758 10.5383 26.8758 9.9051 26.4853 9.51458C26.0948 9.12405 25.4616 9.12405 25.0711 9.51458L17.9999 16.5858L10.9288 9.51469C10.5383 9.12417 9.90513 9.12417 9.5146 9.51469C9.12408 9.90522 9.12408 10.5384 9.5146 10.9289L16.5857 18L9.51472 25.0709C9.12419 25.4614 9.12419 26.0946 9.51472 26.4851C9.90524 26.8757 10.5384 26.8757 10.9289 26.4851L17.9999 19.4142L25.071 26.4853C25.4615 26.8758 26.0946 26.8758 26.4852 26.4853C26.8757 26.0947 26.8757 25.4616 26.4852 25.071L19.4141 18L26.4853 10.9288Z" fill="white"/>
            </svg>
        </button>

        <h2 class="settings-modal__title" id="update-profile-title">Общая информация</h2>

        <?php if ($summaryErrors !== []): ?>
            <section class="settings-modal__summary" data-update-profile-summary role="alert">
                <p>Исправьте ошибки в форме:</p>
                <ul class="settings-modal__summary-list">
                    <?php foreach ($summaryErrors as $summaryError): ?>
                        <li><?= e((string) $summaryError) ?></li>
                    <?php endforeach; ?>
                </ul>
            </section>
        <?php else: ?>
            <section class="settings-modal__summary settings-modal__summary--hidden" data-update-profile-summary hidden></section>
        <?php endif; ?>

        <div class="update-profile-modal__avatar-row<?= $avatarError ? ' is-invalid' : '' ?>" data-update-field="avatar">
            <form class="update-profile-modal__avatar-action" method="post" action="<?= e(ROUTE_UPLOAD_AVATAR_HANDLER) ?>" enctype="multipart/form-data" data-avatar-upload-form>
                <input type="hidden" name="avatar_action" value="upload">
                <input type="hidden" name="redirect_to" value="<?= e(ROUTE_SETTINGS . '?modal=update-profile') ?>">
                <input class="update-profile-modal__avatar-file" type="file" name="avatar" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" data-avatar-upload-input>
                <button class="update-profile-modal__avatar-button" type="button" data-avatar-upload-trigger>
                    <span class="update-profile-modal__avatar-action-icon" aria-hidden="true"><?= $renderInlineIcon('edit_avatar_icon.svg') ?></span>
                    <span>Изменить</span>
                </button>
            </form>

            <img class="update-profile-modal__avatar-preview" src="<?= e($profileAvatar) ?>" alt="<?= e($displayName) ?>">

            <form class="update-profile-modal__avatar-action" method="post" action="<?= e(ROUTE_UPLOAD_AVATAR_HANDLER) ?>">
                <input type="hidden" name="avatar_action" value="delete">
                <input type="hidden" name="redirect_to" value="<?= e(ROUTE_SETTINGS . '?modal=update-profile') ?>">
                <button class="update-profile-modal__avatar-button" type="submit">
                    <span class="update-profile-modal__avatar-action-icon" aria-hidden="true"><?= $renderInlineIcon('delete_icon.svg') ?></span>
                    <span>Удалить</span>
                </button>
            </form>
        </div>
        <p class="update-profile-modal__field-error" data-update-field-error-for="avatar"><?= e((string) $avatarError) ?></p>

        <form class="update-profile-modal__form" method="post" action="<?= e(ROUTE_UPDATE_PROFILE_HANDLER) ?>" novalidate data-update-profile-form>
            <input type="hidden" name="redirect_to" value="<?= e(ROUTE_SETTINGS . '?modal=update-profile') ?>">

            <h3 class="update-profile-modal__subtitle">О тебе</h3>

            <div class="update-profile-modal__field<?= $usernameError ? ' is-invalid' : '' ?>" data-update-field="username">
                <input class="update-profile-modal__input" type="text" id="settings_username" name="username" maxlength="<?= e((string) USERNAME_MAX_LENGTH) ?>" placeholder=" " value="<?= e((string) ($updateProfileValues['username'] ?? '')) ?>" required >
                <label class="update-profile-modal__label" for="settings_username">@username</label>
                <p class="update-profile-modal__field-error" data-update-field-error-for="username"><?= e((string) $usernameError) ?></p>
            </div>

            <div class="update-profile-modal__field<?= $firstNameError ? ' is-invalid' : '' ?>" data-update-field="first_name">
                <input class="update-profile-modal__input" type="text" id="settings_first_name" name="first_name" maxlength="<?= e((string) NAME_MAX_LENGTH) ?>" placeholder=" " value="<?= e((string) ($updateProfileValues['first_name'] ?? '')) ?>" required >
                <label class="update-profile-modal__label" for="settings_first_name">Имя</label>
                <p class="update-profile-modal__field-error" data-update-field-error-for="first_name"><?= e((string) $firstNameError) ?></p>
            </div>

            <div class="update-profile-modal__field<?= $lastNameError ? ' is-invalid' : '' ?>" data-update-field="last_name">
                <input class="update-profile-modal__input" type="text" id="settings_last_name" name="last_name" maxlength="<?= e((string) NAME_MAX_LENGTH) ?>" placeholder=" " value="<?= e((string) ($updateProfileValues['last_name'] ?? '')) ?>" required >
                <label class="update-profile-modal__label" for="settings_last_name">Фамилия</label>
                <p class="update-profile-modal__field-error" data-update-field-error-for="last_name"><?= e((string) $lastNameError) ?></p>
            </div>

            <div class="update-profile-modal__field<?= $descriptionError ? ' is-invalid' : '' ?>" data-update-field="description">
                <textarea class="update-profile-modal__textarea" id="settings_description" name="description" maxlength="1000" rows="4" placeholder=" "><?= e((string) ($updateProfileValues['description'] ?? '')) ?></textarea>
                <label class="update-profile-modal__label" for="settings_description">Описание профиля</label>
                <p class="update-profile-modal__field-error" data-update-field-error-for="description"><?= e((string) $descriptionError) ?></p>
            </div>

            <div class="update-profile-modal__meta-grid">
                <div class="update-profile-modal__field<?= $genderError ? ' is-invalid' : '' ?>" data-update-field="gender">
                    <p class="update-profile-modal__meta-title">Пол</p>
                    <div class="update-profile-modal__gender">
                        <input type="hidden" name="gender" value="">

                        <label class="update-profile-modal__gender-option">
                            <input type="radio" name="gender" value="male" <?= (($updateProfileValues['gender'] ?? '') === 'male') ? 'checked' : '' ?>>
                            <span>Мужской</span>
                        </label>

                        <label class="update-profile-modal__gender-option">
                            <input type="radio" name="gender" value="female" <?= (($updateProfileValues['gender'] ?? '') === 'female') ? 'checked' : '' ?>>
                            <span>Женский</span>
                        </label>
                    </div>
                    <p class="update-profile-modal__field-error" data-update-field-error-for="gender"><?= e((string) $genderError) ?></p>
                </div>

                <div class="update-profile-modal__field<?= $birthDateError ? ' is-invalid' : '' ?>" data-update-field="birth_date">
                    <p class="update-profile-modal__meta-title">Дата рождения</p>
                    <input class="update-profile-modal__input update-profile-modal__input--date" type="date" id="settings_birth_date" name="birth_date" value="<?= e((string) ($updateProfileValues['birth_date'] ?? '')) ?>">
                    <p class="update-profile-modal__field-error" data-update-field-error-for="birth_date"><?= e((string) $birthDateError) ?></p>
                </div>
            </div>

            <button class="update-profile-modal__submit" type="submit">Сохранить</button>
        </form>
    </section>
</div>