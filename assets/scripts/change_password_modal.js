(function (){
    'use strict'

    function initChangePasswordModal(){
        const modal = document.querySelector('[data-change-password-modal]')
        if (!modal || modal.dataset.ready === 'true'){
            return
        }

        modal.dataset.ready = 'true'
        const modalFx = window.WishlistModalFx || null

        const openModal = function (){
            if (modalFx && typeof modalFx.show === 'function'){
                modalFx.show(modal, {
                    backdropSelector: '.settings-modal__backdrop',
                    dialogSelector: '.settings-modal__dialog'
                })
                return
            }

            modal.hidden = false
            modal.classList.add('is-open')
            document.body.classList.add('is-modal-open')
        }

        const closeModal = function (){
            if (modalFx && typeof modalFx.hide === 'function'){
                modalFx.hide(modal, {
                    backdropSelector: '.settings-modal__backdrop',
                    dialogSelector: '.settings-modal__dialog'
                })
                return
            }

            modal.classList.remove('is-open')
            modal.hidden = true
            document.body.classList.remove('is-modal-open')
        }

        document.addEventListener('click', function (event){
            const openTrigger = event.target.closest('[data-change-password-open]')
            if (openTrigger){
                event.preventDefault()
                openModal()
                return
            }

            const closeTrigger = event.target.closest('[data-change-password-close]')
            if (closeTrigger){
                event.preventDefault()
                closeModal()
            }
        })

        document.addEventListener('keydown', function (event){
            if (event.key === 'Escape' && !modal.hidden){
                closeModal()
            }
        })

        if (modal.dataset.openOnLoad === '1'){
            openModal()
        }
    }

    window.WishlistChangePasswordModal = {
        init: initChangePasswordModal
    }
})()