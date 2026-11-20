<?php
    declare(strict_types=1);
?>

<div class="settings-modal confirm-modal" data-confirm-modal data-open-on-load="<?= !empty($openDeleteConfirmModal) ? '1' : '0' ?>" hidden >
    <div class="settings-modal__backdrop" data-confirm-close></div>

    <section class="settings-modal__dialog confirm-modal__dialog" role="dialog" aria-modal="true" aria-labelledby="confirm-delete-title">
        <h2 class="settings-modal__title" id="confirm-delete-title">Подтвердить удаление учетной записи</h2>
        <p class="confirm-modal__text">
            Вы уверены, что хотите удалить свою учетную запись? Это действие нельзя отменить.
            Все данные, связанные с этой учетной записью, будут удалены.
        </p>

        <div class="confirm-modal__actions">
            <button class="confirm-modal__button confirm-modal__button--cancel" type="button" data-confirm-close>Отменить</button>

            <form method="post" action="<?= e(ROUTE_DELETE_PROFILE_HANDLER) ?>">
                <input type="hidden" name="redirect_to" value="<?= e(ROUTE_HOME) ?>">
                <input type="hidden" name="confirm_delete" value="1">
                <button class="confirm-modal__button confirm-modal__button--danger" type="submit">Подтвердить</button>
            </form>
        </div>
    </section>
</div>