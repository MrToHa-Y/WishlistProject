(function (){
    'use strict'

    function initUpdateProfileModal(){
        const modal = document.querySelector('[data-update-profile-modal]')
        if (!modal || modal.dataset.ready === 'true'){
            return
        }

        modal.dataset.ready = 'true'
        const modalFx = window.WishlistModalFx || null

        const uploadForm = modal.querySelector('[data-avatar-upload-form]')
        const uploadInput = modal.querySelector('[data-avatar-upload-input]')
        const uploadTrigger = modal.querySelector('[data-avatar-upload-trigger]')
        const genderGroups = modal.querySelectorAll('.update-profile-modal__gender')
        const formFields = modal.querySelectorAll('.update-profile-modal__field')

        const syncGenderStates = function (){
            genderGroups.forEach(function (group){
                const checked = group.querySelector('.update-profile-modal__gender-option input:checked')
                group.classList.toggle('is-selected', Boolean(checked))
                group.classList.toggle('is-female', Boolean(checked && checked.value === 'female'))
            })
        }

        const syncFilledState = function (){
            formFields.forEach(function (field){
                const control = field.querySelector('.update-profile-modal__input, .update-profile-modal__textarea')
                if (!control){
                    return
                }

                const value = typeof control.value === 'string' ? control.value.trim() : ''
                field.classList.toggle('is-filled', value.length > 0)
            })
        }

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
            const openTrigger = event.target.closest('[data-update-profile-open]')
            if (openTrigger){
                event.preventDefault()
                openModal()
                return
            }

            const closeTrigger = event.target.closest('[data-update-profile-close]')
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

        modal.addEventListener('change', function (event){
            if (event.target.matches('.update-profile-modal__gender-option input')){
                syncGenderStates()
            }

            if (event.target.matches('.update-profile-modal__input, .update-profile-modal__textarea')){
                syncFilledState()
            }
        })

        modal.addEventListener('input', function (event){
            if (event.target.matches('.update-profile-modal__input, .update-profile-modal__textarea')){
                syncFilledState()
            }
        })

        if (uploadTrigger && uploadInput){
            uploadTrigger.addEventListener('click', function (event){
                event.preventDefault()
                uploadInput.click()
            })
        }

        if (uploadInput && uploadForm){
            uploadInput.addEventListener('change', function (){
                const hasFile = Boolean(uploadInput.files && uploadInput.files.length > 0)
                if (!hasFile){
                    return
                }

                modal.dataset.hasAvatar = '1'
                uploadForm.submit()
            })
        }

        if (modal.dataset.openOnLoad === '1'){
            openModal()
        }

        syncGenderStates()
        syncFilledState()
    }

    window.WishlistUpdateProfileModal = { init: initUpdateProfileModal}
})()