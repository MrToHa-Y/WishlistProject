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

    function fillModal(modal, trigger){
        const itemId = trigger.dataset.itemId || ''
        const title = trigger.dataset.itemTitle || ''
        const description = trigger.dataset.itemDescription || ''
        const image = trigger.dataset.itemImage || ''
        const price = trigger.dataset.itemPrice || ''
        const productLink = trigger.dataset.itemLink || ''
        const promoText = trigger.dataset.itemPromo || ''

        const titleNode = modal.querySelector('[data-open-card-modal-title]')
        const descriptionNode = modal.querySelector('[data-open-card-modal-description]')
        const descriptionToggleNode = modal.querySelector('[data-open-card-modal-description-toggle]')
        const imageNode = modal.querySelector('[data-open-card-modal-image]')
        const priceNode = modal.querySelector('[data-open-card-modal-price]')
        const linkNode = modal.querySelector('[data-open-card-modal-link]')
        const buyRowNode = modal.querySelector('[data-open-card-modal-buy-row]')
        const promoNode = modal.querySelector('[data-open-card-modal-promo]')
        const promoLabelNode = modal.querySelector('[data-open-card-modal-promo-label]')
        const promoValueNode = modal.querySelector('[data-open-card-modal-promo-value]')
        const promoSeparatorNode = modal.querySelector('[data-open-card-modal-promo-separator]')
        const saveItemIdNode = modal.querySelector('[data-open-card-modal-save-item-id]')

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

        if (saveItemIdNode){
            saveItemIdNode.value = itemId
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
    }

    function initOpenCardModal(){
        const modal = document.querySelector('[data-open-card-modal]')
        if (!modal || modal.dataset.ready === 'true'){
            return
        }

        modal.dataset.ready = 'true'
        const modalFx = window.WishlistModalFx || null

        const saveForm = modal.querySelector('[data-open-card-modal-save-form]')
        const isAuthenticated = modal.dataset.authenticated === '1'
        const loginUrl = modal.dataset.loginUrl || '/pages/login.php'

        const openModal = function (trigger){
            fillModal(modal, trigger)
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
            if (modalFx && typeof modalFx.hide === 'function'){
                modalFx.hide(modal, {
                    backdropSelector: '.open-card-modal__backdrop',
                    dialogSelector: '.open-card-modal__dialog'
                })
                return
            }

            modal.classList.remove('is-open')
            modal.hidden = true
            document.body.classList.remove('is-modal-open')
        }

        document.addEventListener('click', function (event){
            const descriptionToggle = event.target.closest('[data-open-card-modal-description-toggle]')
            if (descriptionToggle){
                const descriptionNode = modal.querySelector('[data-open-card-modal-description]')
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

            const closeTrigger = event.target.closest('[data-open-card-modal-close]')
            if (closeTrigger){
                closeModal()
            }
        })

        document.addEventListener('keydown', function (event){
            if (event.key === 'Escape' && !modal.hidden){
                closeModal()
            }
        })

        if (saveForm){
            saveForm.addEventListener('submit', function (event){
                if (isAuthenticated){
                    return
                }

                event.preventDefault()
                window.location.href = loginUrl
            })
        }
    }

    window.WishlistOpenCardModal = {
        init: initOpenCardModal
    }
})()