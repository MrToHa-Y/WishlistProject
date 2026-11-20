(function (){
    'use strict'

    function shouldKeepBodyLocked(){
        const cardModal = document.querySelector('[data-open-card-modal]')
        return Boolean(cardModal && !cardModal.hidden)
    }

    function initCreateWishModal(){
        const modal = document.querySelector('[data-create-wish-modal]')
        if (!modal || modal.dataset.ready === 'true'){
            return
        }

        modal.dataset.ready = 'true'
        const modalFx = window.WishlistModalFx || null

        const fileInput = modal.querySelector('[data-create-wish-file]')
        const previewNode = modal.querySelector('[data-create-wish-preview]')
        const dropzone = modal.querySelector('.create-wish-form__dropzone')
        const createWishForm = modal.querySelector('[data-create-wish-form]')
        let objectUrl = ''

        const openModal = function (){
            if (modalFx && typeof modalFx.show === 'function'){
                modalFx.show(modal, {
                    backdropSelector: '.create-wish-modal__backdrop',
                    dialogSelector: '.create-wish-modal__dialog'
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
                    backdropSelector: '.create-wish-modal__backdrop',
                    dialogSelector: '.create-wish-modal__dialog',
                    shouldKeepBodyLocked: shouldKeepBodyLocked
                })
                return
            }

            modal.classList.remove('is-open')
            modal.hidden = true
            if (!shouldKeepBodyLocked()){
                document.body.classList.remove('is-modal-open')
            }
        }

        document.addEventListener('click', function (event){
            const openTrigger = event.target.closest('[data-create-wish-open]')
            if (openTrigger){
                event.preventDefault()
                openModal()
                return
            }

            const closeTrigger = event.target.closest('[data-create-wish-close]')
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

        const setPreview = function (file){
            if (!previewNode){
                return
            }

            if (objectUrl){
                URL.revokeObjectURL(objectUrl)
                objectUrl = ''
            }

            if (!file){
                previewNode.classList.remove('create-wish-form__preview--filled')
                previewNode.innerHTML = '<span class="create-wish-form__preview-icon" aria-hidden="true">+</span><span class="create-wish-form__preview-text">Перетащите изображение или нажмите для загрузки</span><span class="create-wish-form__preview-hint">JPG, PNG или WEBP, до 8MB</span>'
                return
            }

            objectUrl = URL.createObjectURL(file)
            previewNode.classList.add('create-wish-form__preview--filled')
            previewNode.innerHTML = '<img src="' + objectUrl + '" alt="Предпросмотр изображения">'
        }

        if (fileInput){
            fileInput.addEventListener('change', function (){
                const file = fileInput.files && fileInput.files[0] ? fileInput.files[0] : null
                setPreview(file)
            })
        }

        if (dropzone && fileInput){
            dropzone.addEventListener('dragover', function (event){
                event.preventDefault()
                dropzone.classList.add('is-dragover')
            })

            dropzone.addEventListener('dragleave', function (){
                dropzone.classList.remove('is-dragover')
            })

            dropzone.addEventListener('drop', function (event){
                event.preventDefault()
                dropzone.classList.remove('is-dragover')

                const files = event.dataTransfer && event.dataTransfer.files ? event.dataTransfer.files : null
                if (!files || !files.length){
                    return
                }

                if (typeof DataTransfer === 'function'){
                    const transfer = new DataTransfer()
                    transfer.items.add(files[0])
                    fileInput.files = transfer.files
                } else{
                    fileInput.files = files
                }
                const inputEvent = new Event('change', { bubbles: true })
                fileInput.dispatchEvent(inputEvent)
            })
        }

        if (modal.dataset.openOnLoad === '1'){
            openModal()
        }

        if (createWishForm){
            createWishForm.addEventListener('submit', function (){
                openModal()
            })
        }
    }

    window.WishlistCreateWishModal = {
        init: initCreateWishModal
    }
})()