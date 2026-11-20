(function (){
    'use strict'

    function parseTimeToMs(rawValue){
        const value = Number.parseFloat((rawValue || '').trim())
        if (!Number.isFinite(value)){
            return 0
        }

        if ((rawValue || '').includes('ms')){
            return value
        }

        return value * 1000
    }

    function getTransitionDurationMs(element){
        if (!element || !window.getComputedStyle){
            return 0
        }

        const styles = window.getComputedStyle(element)
        const durations = (styles.transitionDuration || '0s').split(',')
        const delays = (styles.transitionDelay || '0s').split(',')
        const partsCount = Math.max(durations.length, delays.length)
        let maxDuration = 0

        for (let index = 0; index < partsCount; index += 1){
            const durationMs = parseTimeToMs(durations[index] || durations[durations.length - 1] || '0s')
            const delayMs = parseTimeToMs(delays[index] || delays[delays.length - 1] || '0s')
            maxDuration = Math.max(maxDuration, durationMs + delayMs)
        }

        return maxDuration
    }

    function hasVisibleAnimatedModal(){
        return Boolean(document.querySelector('.create-wish-modal.is-open, .open-card-modal.is-open, .settings-modal.is-open'))
    }

    function showAnimatedModal(modal, options){
        if (!modal){
            return
        }

        const openClass = options && options.openClass ? options.openClass : 'is-open'
        const lockBody = !(options && options.lockBody === false)

        modal.hidden = false

        if (lockBody){
            document.body.classList.add('is-modal-open')
        }

        window.requestAnimationFrame(function (){
            window.requestAnimationFrame(function (){
                if (modal.hidden){
                    return
                }
                modal.classList.add(openClass)
            })
        })
    }

    function hideAnimatedModal(modal, options){
        if (!modal){
            return Promise.resolve()
        }

        const openClass = options && options.openClass ? options.openClass : 'is-open'
        const lockBody = !(options && options.lockBody === false)
        const selectors = []

        if (options && options.backdropSelector){
            selectors.push(options.backdropSelector)
        }
        if (options && options.dialogSelector){
            selectors.push(options.dialogSelector)
        }

        const durationCandidates = [getTransitionDurationMs(modal)]
        selectors.forEach(function (selector){
            modal.querySelectorAll(selector).forEach(function (node){
                durationCandidates.push(getTransitionDurationMs(node))
            })
        })

        const fallbackDuration = options && Number.isFinite(Number(options.fallbackDuration))
            ? Number(options.fallbackDuration)
            : 260
        const transitionDuration = Math.max(fallbackDuration, ...durationCandidates)

        return new Promise(function (resolve){
            let finished = false

            const finish = function (){
                if (finished){
                    return
                }

                finished = true
                modal.hidden = true
                modal.classList.remove(openClass)

                if (lockBody){
                    const shouldKeepBodyLocked = options && typeof options.shouldKeepBodyLocked === 'function'
                        ? options.shouldKeepBodyLocked()
                        : hasVisibleAnimatedModal()

                    if (!shouldKeepBodyLocked){
                        document.body.classList.remove('is-modal-open')
                    }
                }

                resolve()
            }

            if (modal.hidden || !modal.classList.contains(openClass)){
                finish()
                return
            }

            const transitionEndHandler = function (event){
                if (event.target !== modal){
                    return
                }
                finish()
            }

            modal.addEventListener('transitionend', transitionEndHandler, { once: true })
            window.setTimeout(finish, transitionDuration + 40)
            modal.classList.remove(openClass)
        })
    }

    window.WishlistModalFx = {
        show: showAnimatedModal,
        hide: hideAnimatedModal,
    }

    function initAuthCloseBehaviour(overlaySelector, closeSelector){
        const overlay = document.querySelector(overlaySelector)
        if (!overlay){
            return
        }

        const closeUrl = overlay.dataset.closeUrl || '/'

        const closePage = function (){
            window.location.href = closeUrl
        }

        overlay.addEventListener('click', function (event){
            if (event.target === overlay){
                closePage()
            }
        })

        document.querySelectorAll(closeSelector).forEach(function (closeControl){
            closeControl.addEventListener('click', function (event){
                event.preventDefault()
                closePage()
            })
        })

        document.addEventListener('keydown', function (event){
            if (event.key === 'Escape'){
                closePage()
            }
        })
    }

    document.addEventListener('DOMContentLoaded', function (){
        if (
            window.WishlistBackgroundPhotoAnimation &&
            typeof window.WishlistBackgroundPhotoAnimation.init === 'function'
        ){
            window.WishlistBackgroundPhotoAnimation.init()
        }

        if (window.WishlistAlerts && typeof window.WishlistAlerts.init === 'function'){
            window.WishlistAlerts.init(document)
        }

        const registerForm = document.querySelector('[data-register-form]')
        if (
            registerForm &&
            window.WishlistFormValidation &&
            typeof window.WishlistFormValidation.initRegisterValidation === 'function'
        ){
            window.WishlistFormValidation.initRegisterValidation(registerForm)
        }

        const loginForm = document.querySelector('[data-login-form]')
        if (
            loginForm &&
            window.WishlistFormValidation &&
            typeof window.WishlistFormValidation.initLoginValidation === 'function'
        ){
            window.WishlistFormValidation.initLoginValidation(loginForm)
        }

        const createWishForm = document.querySelector('[data-create-wish-form]')
        if (
            createWishForm &&
            window.WishlistFormValidation &&
            typeof window.WishlistFormValidation.initCreateWishValidation === 'function'
        ){
            window.WishlistFormValidation.initCreateWishValidation(createWishForm)
        }

        if (
            window.WishlistFormValidation &&
            typeof window.WishlistFormValidation.initEditWishValidation === 'function'
        ){
            document.querySelectorAll('[data-open-card-modal-profile-edit-form]').forEach(function (editForm){
                window.WishlistFormValidation.initEditWishValidation(editForm)
            })
        }

        initAuthCloseBehaviour('[data-register-overlay]', '[data-register-close]')
        initAuthCloseBehaviour('[data-login-overlay]', '[data-login-close]')

        if (window.WishlistModal && typeof window.WishlistModal.initProfileModal === 'function'){
            window.WishlistModal.initProfileModal()
        }

        if (window.WishlistModal && typeof window.WishlistModal.initHomeHeaderModal === 'function'){
            window.WishlistModal.initHomeHeaderModal()
        }

        if (window.WishlistOpenCardModal && typeof window.WishlistOpenCardModal.init === 'function'){
            window.WishlistOpenCardModal.init()
        }

        if (
            window.WishlistOpenCardProfileModal &&
            typeof window.WishlistOpenCardProfileModal.init === 'function'
        ){
            window.WishlistOpenCardProfileModal.init()
        }

        if (
            window.WishlistReserveWishModal &&
            typeof window.WishlistReserveWishModal.init === 'function'
        ){
            window.WishlistReserveWishModal.init()
        }

        if (window.WishlistCreateWishModal && typeof window.WishlistCreateWishModal.init === 'function'){
            window.WishlistCreateWishModal.init()
        }

        if (window.WishlistUpdateProfileModal && typeof window.WishlistUpdateProfileModal.init === 'function'){
            window.WishlistUpdateProfileModal.init()
        }

        if (window.WishlistChangeEmailModal && typeof window.WishlistChangeEmailModal.init === 'function'){
            window.WishlistChangeEmailModal.init()
        }

        if (window.WishlistChangePasswordModal && typeof window.WishlistChangePasswordModal.init === 'function'){
            window.WishlistChangePasswordModal.init()
        }

        if (window.WishlistConfirmModal && typeof window.WishlistConfirmModal.init === 'function'){
            window.WishlistConfirmModal.init()
        }

        const updateProfileForm = document.querySelector('[data-update-profile-form]')
        if (
            updateProfileForm &&
            window.WishlistFormValidation &&
            typeof window.WishlistFormValidation.initUpdateProfileValidation === 'function'
        ){
            window.WishlistFormValidation.initUpdateProfileValidation(updateProfileForm)
        }

        const changeEmailForm = document.querySelector('[data-change-email-form]')
        if (
            changeEmailForm &&
            window.WishlistFormValidation &&
            typeof window.WishlistFormValidation.initChangeEmailValidation === 'function'
        ){
            window.WishlistFormValidation.initChangeEmailValidation(changeEmailForm)
        }

        const changePasswordForm = document.querySelector('[data-change-password-form]')
        if (
            changePasswordForm &&
            window.WishlistFormValidation &&
            typeof window.WishlistFormValidation.initChangePasswordValidation === 'function'
        ){
            window.WishlistFormValidation.initChangePasswordValidation(changePasswordForm)
        }

        if (window.WishlistInfiniteScroll && typeof window.WishlistInfiniteScroll.init === 'function'){
            window.WishlistInfiniteScroll.init()
        }

        if (window.WishlistSearchUsers && typeof window.WishlistSearchUsers.init === 'function'){
            window.WishlistSearchUsers.init()
        }

    })
})()