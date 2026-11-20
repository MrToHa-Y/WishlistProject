(function (){
    'use strict'

    const DESCRIPTION_COLLAPSE_HEIGHT = 95

    function splitPromoText(rawPromo){
        const promo = (rawPromo || '').trim()
        if (!promo){
            return { label: '', value: '' }
        }

        const match = promo.match(/^(.+?)\s+(\d+\s*%.*)$/u)
        if (!match){
            return { label: promo, value: '' }
        }

        return {
            label: (match[1] || '').trim(),
            value: (match[2] || '').trim()
        }
    }

    function parsePriceValue(trigger){
        const direct = (trigger.dataset.itemPriceValue || '').trim()
        if (direct !== '' && Number.isFinite(Number(direct))){
            return direct
        }

        const raw = (trigger.dataset.itemPrice || '').replace(/[^\d.,-]/g, '').replace(',', '.').trim()
        if (raw !== '' && Number.isFinite(Number(raw))){
            return raw
        }

        return '0'
    }

    function setupDescriptionToggle(modal, descriptionNode, toggleNode){
        if (!descriptionNode || !toggleNode){
            return
        }

        descriptionNode.classList.remove('is-expanded')
        modal.classList.remove('is-description-expanded')
        toggleNode.textContent = 'Подробнее'
        toggleNode.setAttribute('aria-expanded', 'false')
        toggleNode.hidden = true

        const updateVisibility = function (){
            const needsToggle = descriptionNode.scrollHeight > DESCRIPTION_COLLAPSE_HEIGHT
            toggleNode.hidden = !needsToggle
        }

        if (typeof window.requestAnimationFrame === 'function'){
            window.requestAnimationFrame(updateVisibility)
            return
        }

        setTimeout(updateVisibility, 0)
    }

    function fillProfileModal(modal, trigger){
        const itemId = trigger.dataset.itemId || ''
        const title = trigger.dataset.itemTitle || ''
        const description = trigger.dataset.itemDescription || ''
        const image = trigger.dataset.itemImage || ''
        const price = trigger.dataset.itemPrice || ''
        const priceValue = parsePriceValue(trigger)
        const productLink = trigger.dataset.itemLink || ''
        const promoText = trigger.dataset.itemPromo || ''

        const titleNode = modal.querySelector('[data-open-card-modal-profile-title]')
        const descriptionNode = modal.querySelector('[data-open-card-modal-profile-description]')
        const descriptionToggleNode = modal.querySelector('[data-open-card-modal-profile-description-toggle]')
        const imageNode = modal.querySelector('[data-open-card-modal-profile-image]')
        const priceNode = modal.querySelector('[data-open-card-modal-profile-price]')
        const linkNode = modal.querySelector('[data-open-card-modal-profile-link]')
        const buyRowNode = modal.querySelector('[data-open-card-modal-profile-buy-row]')

        const promoNode = modal.querySelector('[data-open-card-modal-profile-promo]')
        const promoLabelNode = modal.querySelector('[data-open-card-modal-profile-promo-label]')
        const promoValueNode = modal.querySelector('[data-open-card-modal-profile-promo-value]')
        const promoSeparatorNode = modal.querySelector('[data-open-card-modal-profile-promo-separator]')

        const deleteWishIdNode = modal.querySelector('[data-open-card-modal-profile-delete-id]')
        const editWishIdNode = modal.querySelector('[data-open-card-modal-profile-edit-id]')
        const fulfilledWishIdNode = modal.querySelector('[data-open-card-modal-profile-fulfilled-id]')
        const editFormNode = modal.querySelector('[data-open-card-modal-profile-edit-form]')
        const editTitleNode = modal.querySelector('[data-open-card-modal-profile-edit-title-input]')
        const editLinkNode = modal.querySelector('[data-open-card-modal-profile-edit-link-input]')
        const editDescriptionNode = modal.querySelector('[data-open-card-modal-profile-edit-description-input]')
        const editPriceNode = modal.querySelector('[data-open-card-modal-profile-edit-price-input]')
        const editImageInputNode = modal.querySelector('[data-open-card-modal-profile-edit-image-input]')
        const editPreviewNode = modal.querySelector('[data-open-card-modal-profile-edit-preview]')

        if (titleNode){
            titleNode.textContent = title
        }

        if (descriptionNode){
            descriptionNode.textContent = description || 'Описание пока не добавлено.'
            setupDescriptionToggle(modal, descriptionNode, descriptionToggleNode)
        }

        if (imageNode){
            imageNode.src = image
            imageNode.alt = title
        }

        if (priceNode){
            priceNode.textContent = price || '—'
        }

        if (linkNode){
            if (productLink){
                linkNode.hidden = false
                linkNode.href = productLink
            }else{
                linkNode.hidden = true
                linkNode.removeAttribute('href')
            }
        }

        if (promoNode && promoLabelNode && promoValueNode){
            if (promoText){
                const promoParts = splitPromoText(promoText)
                promoLabelNode.textContent = promoParts.label
                promoValueNode.textContent = promoParts.value

                if (promoSeparatorNode){
                    promoSeparatorNode.hidden = promoParts.value === ''
                }

                promoNode.hidden = false
                if (buyRowNode){
                    buyRowNode.classList.add('open-card-modal__buy-row--promo')
                }
            }else{
                promoLabelNode.textContent = ''
                promoValueNode.textContent = ''
                if (promoSeparatorNode){
                    promoSeparatorNode.hidden = true
                }
                promoNode.hidden = true
                if (buyRowNode){
                    buyRowNode.classList.remove('open-card-modal__buy-row--promo')
                }
            }
        }

        if (deleteWishIdNode){
            deleteWishIdNode.value = itemId
        }

        if (editWishIdNode){
            editWishIdNode.value = itemId
        }

        if (fulfilledWishIdNode){
            fulfilledWishIdNode.value = itemId
        }

        if (editTitleNode){
            editTitleNode.value = title
        }

        if (editLinkNode){
            editLinkNode.value = productLink
        }

        if (editDescriptionNode){
            editDescriptionNode.value = description
        }

        if (editPriceNode){
            editPriceNode.value = priceValue
        }

        if (editFormNode){
            editFormNode.dataset.currentImage = image || ''
            editFormNode.dispatchEvent(new CustomEvent('reset-validation-state'))
        }

        if (editImageInputNode){
            editImageInputNode.value = ''
        }

        if (editPreviewNode){
            if (image){
                editPreviewNode.classList.add('create-wish-form__preview--filled')
                editPreviewNode.innerHTML = '<img src="' + image + '" alt="' + title + '">'
            }else{
                editPreviewNode.classList.remove('create-wish-form__preview--filled')
                editPreviewNode.innerHTML = '<span class="create-wish-form__preview-icon" aria-hidden="true">+</span><span class="create-wish-form__preview-text">Перетащите изображение или нажмите для загрузки</span><span class="create-wish-form__preview-hint">JPG, PNG или WEBP, до 8MB</span>'
            }
        }
    }

    function initOpenCardProfileModal(){
        const modal = document.querySelector('[data-open-card-modal-profile]')
        if (!modal || modal.dataset.ready === 'true'){
            return
        }

        modal.dataset.ready = 'true'
        const modalFx = window.WishlistModalFx || null

        const editLayer = modal.querySelector('[data-open-card-modal-profile-edit-layer]')
        const editForm = modal.querySelector('[data-open-card-modal-profile-edit-form]')
        const deleteForm = modal.querySelector('[data-open-card-modal-profile-delete-form]')
        const fulfilledForm = modal.querySelector('[data-open-card-modal-profile-fulfilled-form]')
        const editImageInput = modal.querySelector('[data-open-card-modal-profile-edit-image-input]')
        const editPreview = modal.querySelector('[data-open-card-modal-profile-edit-preview]')

        const isAuthenticated = modal.dataset.authenticated === '1'
        const loginUrl = modal.dataset.loginUrl || '/pages/login.php'
        let editObjectUrl = ''

        const openModal = function (trigger){
            fillProfileModal(modal, trigger)
            if (editLayer){
                editLayer.hidden = true
                editLayer.classList.remove('is-open')
            }

            if (modalFx && typeof modalFx.show === 'function'){
                modalFx.show(modal, {
                    backdropSelector: '.open-card-modal__backdrop',
                    dialogSelector: '.open-card-modal__dialog'
                })
                return
            }

            modal.hidden = false
            modal.classList.add('is-open')
            document.body.classList.add('is-modal-open')
        }

        const closeModal = function (){
            modal.classList.remove('is-description-expanded')
            if (editLayer){
                closeEditLayer()
            }

            if (modalFx && typeof modalFx.hide === 'function'){
                modalFx.hide(modal, {
                    backdropSelector: '.open-card-modal__backdrop',
                    dialogSelector: '.open-card-modal__dialog'
                })
                return
            }

            modal.classList.remove('is-open')
            modal.hidden = true
            if (editLayer){
                editLayer.classList.remove('is-open')
                editLayer.hidden = true
            }
            document.body.classList.remove('is-modal-open')
        }

        const openEditLayer = function (){
            if (editLayer){
                if (modalFx && typeof modalFx.show === 'function'){
                    modalFx.show(editLayer, {
                        openClass: 'is-open',
                        backdropSelector: '.open-card-modal__edit-backdrop',
                        dialogSelector: '.open-card-modal__edit-dialog',
                        lockBody: false
                    })
                    return
                }

                editLayer.hidden = false
                editLayer.classList.add('is-open')
            }
        }

        const closeEditLayer = function (){
            if (editLayer){
                if (modalFx && typeof modalFx.hide === 'function'){
                    modalFx.hide(editLayer, {
                        openClass: 'is-open',
                        backdropSelector: '.open-card-modal__edit-backdrop',
                        dialogSelector: '.open-card-modal__edit-dialog',
                        lockBody: false,
                        fallbackDuration: 220
                    })
                    return
                }

                editLayer.classList.remove('is-open')
                editLayer.hidden = true
            }
        }

        document.addEventListener('click', function (event){
            const descriptionToggle = event.target.closest('[data-open-card-modal-profile-description-toggle]')
            if (descriptionToggle){
                const descriptionNode = modal.querySelector('[data-open-card-modal-profile-description]')
                if (!descriptionNode){
                    return
                }

                const isExpanded = descriptionNode.classList.toggle('is-expanded')
                modal.classList.toggle('is-description-expanded', isExpanded)
                descriptionToggle.textContent = isExpanded ? 'Свернуть' : 'Подробнее'
                descriptionToggle.setAttribute('aria-expanded', isExpanded ? 'true' : 'false')
                return
            }

            const trigger = event.target.closest('[data-open-card-modal-trigger]')
            if (trigger){
                openModal(trigger)
                return
            }

            const closeTrigger = event.target.closest('[data-open-card-modal-profile-close]')
            if (closeTrigger){
                closeModal()
                return
            }

            const editOpenTrigger = event.target.closest('[data-open-card-modal-profile-edit-open]')
            if (editOpenTrigger){
                openEditLayer()
                return
            }

            const editCloseTrigger = event.target.closest('[data-open-card-modal-profile-edit-close]')
            if (editCloseTrigger){
                closeEditLayer()
            }
        })

        document.addEventListener('keydown', function (event){
            if (event.key !== 'Escape' || modal.hidden){
                return
            }

            if (editLayer && !editLayer.hidden){
                closeEditLayer()
                return
            }

            closeModal()
        })

        if (deleteForm){
            deleteForm.addEventListener('submit', function (event){
                if (isAuthenticated){
                    return
                }

                event.preventDefault()
                window.location.href = loginUrl
            })
        }

        if (editForm){
            editForm.addEventListener('submit', function (event){
                if (!isAuthenticated){
                    event.preventDefault()
                    window.location.href = loginUrl
                    return
                }

                let isValid = false

                if (
                    window.WishlistFormValidation &&
                    typeof window.WishlistFormValidation.validateEditWishForm === 'function'
                ){
                    isValid = window.WishlistFormValidation.validateEditWishForm(editForm)
                }else{
                    isValid = editForm.checkValidity()
                }

                if (!isValid){
                    event.preventDefault()
                    openEditLayer()

                    const editDialog = editForm.closest('.open-card-modal__edit-dialog')
                    if (editDialog){
                        editDialog.scrollTop = 0
                    }
                }
            })
        }

        if (fulfilledForm){
            fulfilledForm.addEventListener('submit', function (event){
                if (isAuthenticated){
                    return
                }

                event.preventDefault()
                window.location.href = loginUrl
            })
        }

        if (editImageInput && editPreview){
            editImageInput.addEventListener('change', function (){
                const file = editImageInput.files && editImageInput.files[0] ? editImageInput.files[0] : null

                if (editObjectUrl){
                    URL.revokeObjectURL(editObjectUrl)
                    editObjectUrl = ''
                }

                if (!file){
                    return
                }

                editObjectUrl = URL.createObjectURL(file)
                editPreview.classList.add('create-wish-form__preview--filled')
                editPreview.innerHTML = '<img src="' + editObjectUrl + '" alt="Предпросмотр изображения">'
            })
        }
    }

    window.WishlistOpenCardProfileModal = {
        init: initOpenCardProfileModal
    }
})()
