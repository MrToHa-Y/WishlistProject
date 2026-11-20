(function (){
    'use strict'

    const EMAIL_REGEX = /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/
    const WISH_ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/webp']

    function renderSummary(summaryNode, title, messages, listClassName){
        if (!summaryNode){
            return
        }

        if (!messages.length){
            summaryNode.hidden = true
            summaryNode.classList.add(
                'register-form__summary--hidden',
                'login-form__summary--hidden',
                'create-wish-modal__summary--hidden',
                'settings-modal__summary--hidden'
            )
            summaryNode.innerHTML = ''
            return
        }

        const uniqueMessages = Array.from(new Set(messages))
        const visibleMessages = uniqueMessages.slice(0, 4)
        const listItems = visibleMessages.map(function (message){
            return '<li>' + message + '</li>'
        })

        if (uniqueMessages.length > visibleMessages.length){
            listItems.push('<li>И еще ' + (uniqueMessages.length - visibleMessages.length) + ' ошибок.</li>')
        }

        summaryNode.hidden = false
        summaryNode.classList.remove(
            'register-form__summary--hidden',
            'login-form__summary--hidden',
            'create-wish-modal__summary--hidden',
            'settings-modal__summary--hidden'
        )
        summaryNode.innerHTML = '<p>' + title + '</p><ul class="' + listClassName + '">' + listItems.join('') + '</ul>'
    }

    function clearFieldError(form, fieldName, fieldAttr, errorAttr){
        const block = form.querySelector('[' + fieldAttr + '="' + fieldName + '"]')
        const errorNode = form.querySelector('[' + errorAttr + '="' + fieldName + '"]')

        if (block){
            block.classList.remove('is-invalid')
        }

        if (errorNode){
            errorNode.textContent = ''
        }
    }

    function setFieldError(form, fieldName, message, fieldAttr, errorAttr){
        const block = form.querySelector('[' + fieldAttr + '="' + fieldName + '"]')
        const errorNode = form.querySelector('[' + errorAttr + '="' + fieldName + '"]')

        if (block){
            block.classList.add('is-invalid')
        }

        if (errorNode){
            errorNode.textContent = message
        }
    }

    function clearRegisterFieldError(form, fieldName){
        clearFieldError(form, fieldName, 'data-field', 'data-field-error-for')
    }

    function setRegisterFieldError(form, fieldName, message){
        setFieldError(form, fieldName, message, 'data-field', 'data-field-error-for')
    }

    function validateRegisterForm(form){
        const errors = []
        const minPasswordLength = Number(form.dataset.passwordMinLength || 8)
        const requiredFields = ['first_name', 'last_name', 'username', 'email', 'password', 'confirm_password']

        requiredFields.forEach(function (fieldName){
            clearRegisterFieldError(form, fieldName)
        })

        requiredFields.forEach(function (fieldName){
            const input = form.querySelector('[name="' + fieldName + '"]')
            if (!input){
                return
            }

            if (String(input.value).trim() === ''){
                const message = 'Поле обязательно для заполнения.'
                errors.push(message)
                setRegisterFieldError(form, fieldName, message)
            }
        })

        const emailInput = form.querySelector('[name="email"]')
        if (emailInput && String(emailInput.value).trim() !== '' && !EMAIL_REGEX.test(emailInput.value.trim())){
            const message = 'Введите корректный email.'
            errors.push(message)
            setRegisterFieldError(form, 'email', message)
        }

        const passwordInput = form.querySelector('[name="password"]')
        if (passwordInput && passwordInput.value.length > 0 && passwordInput.value.length < minPasswordLength){
            const message = 'Пароль должен содержать минимум ' + minPasswordLength + ' символов.'
            errors.push(message)
            setRegisterFieldError(form, 'password', message)
        }

        const confirmPasswordInput = form.querySelector('[name="confirm_password"]')
        if (
            passwordInput &&
            confirmPasswordInput &&
            confirmPasswordInput.value.length > 0 &&
            passwordInput.value !== confirmPasswordInput.value
        ){
            const message = 'Пароли не совпадают.'
            errors.push(message)
            setRegisterFieldError(form, 'confirm_password', message)
        }

        const summaryNode = form.querySelector('[data-form-error-summary]') || document.querySelector('[data-form-error-summary]')
        renderSummary(summaryNode, 'Исправьте ошибки в выделенных полях:', errors, 'register-form__summary-list')

        return errors.length === 0
    }

    function clearLoginFieldError(form, fieldName){
        clearFieldError(form, fieldName, 'data-login-field', 'data-login-field-error-for')
    }

    function setLoginFieldError(form, fieldName, message){
        setFieldError(form, fieldName, message, 'data-login-field', 'data-login-field-error-for')
    }

    function validateLoginForm(form){
        const errors = []
        const emailInput = form.querySelector('[name="email"]')
        const passwordInput = form.querySelector('[name="password"]')

        clearLoginFieldError(form, 'email')
        clearLoginFieldError(form, 'password')

        if (!emailInput || String(emailInput.value).trim() === ''){
            const message = 'Введите email.'
            errors.push(message)
            setLoginFieldError(form, 'email', message)
        }else if (!EMAIL_REGEX.test(emailInput.value.trim())){
            const message = 'Введите корректный email.'
            errors.push(message)
            setLoginFieldError(form, 'email', message)
        }

        if (!passwordInput || String(passwordInput.value).trim() === ''){
            const message = 'Введите пароль.'
            errors.push(message)
            setLoginFieldError(form, 'password', message)
        }

        const summaryNode = form.querySelector('[data-login-form-error-summary]') || document.querySelector('[data-login-form-error-summary]')
        renderSummary(summaryNode, 'Исправьте ошибки в форме входа:', errors, 'login-form__summary-list')

        return errors.length === 0
    }

    function clearCreateWishFieldError(form, fieldName){
        clearFieldError(form, fieldName, 'data-wish-field', 'data-wish-field-error-for')
    }

    function setCreateWishFieldError(form, fieldName, message){
        setFieldError(form, fieldName, message, 'data-wish-field', 'data-wish-field-error-for')
    }

    function isValidUrl(url){
        const normalizedUrl = String(url || '').trim()
        if (normalizedUrl === ''){
            return false
        }

        if (normalizedUrl.startsWith('/')){
            return true
        }

        try{
            const parsed = new URL(normalizedUrl)
            return parsed.protocol === 'http:' || parsed.protocol === 'https:'
        }catch (error){
            return false
        }
    }

    function getCreateWishSummaryNode(form){
        return form.closest('[data-create-wish-modal]')?.querySelector('[data-create-wish-summary]') || null
    }

    function getEditWishSummaryNode(form){
        return form.closest('.open-card-modal__edit-dialog')?.querySelector('[data-edit-wish-summary]') || null
    }

    function clearWishValidationState(form, summaryNode){
        ['title', 'product_link', 'price', 'image'].forEach(function (fieldName){
            clearCreateWishFieldError(form, fieldName)
        })

        renderSummary(summaryNode, '', [], 'create-wish-modal__summary-list')
    }

    function validateWishForm(form, summaryNode, options){
        const errors = []
        const config = options || {}
        const maxImageSize = Number(form.dataset.maxImageSize || 8388608)
        const titleInput = form.querySelector('[name="title"]')
        const linkInput = form.querySelector('[name="product_link"]')
        const priceInput = form.querySelector('[name="price"]')
        const imageInput = form.querySelector('[name="image"]')
        const hasExistingImage = String(form.dataset.currentImage || '').trim() !== ''
        const requireImage = config.requireImage !== false

        ['title', 'product_link', 'price', 'image'].forEach(function (fieldName){
            clearCreateWishFieldError(form, fieldName)
        })

        if (!titleInput || String(titleInput.value).trim() === ''){
            const message = 'Укажите название желания.'
            errors.push(message)
            setCreateWishFieldError(form, 'title', message)
        }

        if (linkInput && String(linkInput.value).trim() !== '' && !isValidUrl(String(linkInput.value).trim())){
            const message = 'Введите корректную ссылку.'
            errors.push(message)
            setCreateWishFieldError(form, 'product_link', message)
        }

        const priceRaw = priceInput ? String(priceInput.value).trim() : ''
        const normalizedPrice = priceRaw.replace(',', '.')
        const numericPrice = Number(normalizedPrice)

        if (!priceInput || priceRaw === ''){
            const message = 'Укажите цену.'
            errors.push(message)
            setCreateWishFieldError(form, 'price', message)
        }else if (!Number.isFinite(numericPrice) || numericPrice < 0){
            const message = 'Цена должна быть числом больше или равна 0.'
            errors.push(message)
            setCreateWishFieldError(form, 'price', message)
        }

        const file = imageInput && imageInput.files && imageInput.files[0] ? imageInput.files[0] : null
        if (!file){
            if (requireImage && !hasExistingImage){
                const message = 'Добавьте изображение.'
                errors.push(message)
                setCreateWishFieldError(form, 'image', message)
            }
        }else{
            if (file.size > maxImageSize){
                const message = 'Размер изображения не должен превышать 8MB.'
                errors.push(message)
                setCreateWishFieldError(form, 'image', message)
            }

            if (file.type && WISH_ALLOWED_IMAGE_TYPES.indexOf(file.type) === -1){
                const message = 'Разрешены только JPG, PNG и WEBP.'
                errors.push(message)
                setCreateWishFieldError(form, 'image', message)
            }
        }

        renderSummary(summaryNode, 'Исправьте ошибки в форме:', errors, 'create-wish-modal__summary-list')
        return errors.length === 0
    }

    function validateCreateWishForm(form){
        return validateWishForm(form, getCreateWishSummaryNode(form), {
            requireImage: true
        })
    }

    function validateEditWishForm(form){
        return validateWishForm(form, getEditWishSummaryNode(form), {
            requireImage: true
        })
    }

    function clearUpdateFieldError(form, fieldName){
        clearFieldError(form, fieldName, 'data-update-field', 'data-update-field-error-for')
    }

    function setUpdateFieldError(form, fieldName, message){
        setFieldError(form, fieldName, message, 'data-update-field', 'data-update-field-error-for')
    }

    function getUpdateProfileSummaryNode(form){
        return form.closest('.update-profile-modal__dialog')?.querySelector('[data-update-profile-summary]') || null
    }

    function validateUpdateProfileForm(form){
        const errors = []
        const summaryNode = getUpdateProfileSummaryNode(form)
        const usernameInput = form.querySelector('[name="username"]')
        const firstNameInput = form.querySelector('[name="first_name"]')
        const lastNameInput = form.querySelector('[name="last_name"]')
        const birthDateInput = form.querySelector('[name="birth_date"]')
        const modal = form.closest('[data-update-profile-modal]')
        const avatarInput = modal ? modal.querySelector('[data-avatar-upload-input]') : null
        const hasAvatar = modal ? modal.dataset.hasAvatar === '1' : false
        const hasSelectedAvatar = Boolean(avatarInput && avatarInput.files && avatarInput.files.length > 0)

        ['username', 'first_name', 'last_name', 'description', 'birth_date', 'gender', 'avatar'].forEach(function (fieldName){
            clearUpdateFieldError(form, fieldName)
        })

        if (!usernameInput || String(usernameInput.value).trim() === ''){
            const message = 'Укажите username.'
            errors.push(message)
            setUpdateFieldError(form, 'username', message)
        }else{
            const username = String(usernameInput.value).trim()
            if (!/^[A-Za-z0-9_]+$/.test(username)){
                const message = 'Username может содержать только латиницу, цифры и _.'
                errors.push(message)
                setUpdateFieldError(form, 'username', message)
            }
        }

        if (!firstNameInput || String(firstNameInput.value).trim() === ''){
            const message = 'Укажите имя.'
            errors.push(message)
            setUpdateFieldError(form, 'first_name', message)
        }

        if (!lastNameInput || String(lastNameInput.value).trim() === ''){
            const message = 'Укажите фамилию.'
            errors.push(message)
            setUpdateFieldError(form, 'last_name', message)
        }

        if (birthDateInput && String(birthDateInput.value).trim() !== ''){
            const selectedDate = new Date(String(birthDateInput.value))
            const today = new Date()
            today.setHours(0, 0, 0, 0)

            if (Number.isNaN(selectedDate.getTime())){
                const message = 'Укажите корректную дату рождения.'
                errors.push(message)
                setUpdateFieldError(form, 'birth_date', message)
            }else if (selectedDate > today){
                const message = 'Дата рождения не может быть в будущем.'
                errors.push(message)
                setUpdateFieldError(form, 'birth_date', message)
            }
        }

        if (!hasAvatar && !hasSelectedAvatar){
            const message = 'Добавьте аватарку профиля.'
            errors.push(message)
            setUpdateFieldError(form, 'avatar', message)
        }

        renderSummary(summaryNode, 'Исправьте ошибки в форме:', errors, 'settings-modal__summary-list')
        return errors.length === 0
    }

    function clearChangeEmailFieldError(form, fieldName){
        clearFieldError(form, fieldName, 'data-change-email-field', 'data-change-email-field-error-for')
    }

    function setChangeEmailFieldError(form, fieldName, message){
        setFieldError(form, fieldName, message, 'data-change-email-field', 'data-change-email-field-error-for')
    }

    function getChangeEmailSummaryNode(form){
        return form.closest('.change-email-modal__dialog')?.querySelector('[data-change-email-summary]') || null
    }

    function validateChangeEmailForm(form){
        const errors = []
        const emailInput = form.querySelector('[name="email"]')

        clearChangeEmailFieldError(form, 'email')

        if (!emailInput || String(emailInput.value).trim() === ''){
            const message = 'Укажите новую почту.'
            errors.push(message)
            setChangeEmailFieldError(form, 'email', message)
        }else if (!EMAIL_REGEX.test(String(emailInput.value).trim())){
            const message = 'Введите корректный email.'
            errors.push(message)
            setChangeEmailFieldError(form, 'email', message)
        }

        renderSummary(getChangeEmailSummaryNode(form), 'Исправьте ошибки в форме:', errors, 'settings-modal__summary-list')
        return errors.length === 0
    }

    function clearChangePasswordFieldError(form, fieldName){
        clearFieldError(form, fieldName, 'data-change-password-field', 'data-change-password-field-error-for')
    }

    function setChangePasswordFieldError(form, fieldName, message){
        setFieldError(form, fieldName, message, 'data-change-password-field', 'data-change-password-field-error-for')
    }

    function getChangePasswordSummaryNode(form){
        return form.closest('.change-password-modal__dialog')?.querySelector('[data-change-password-summary]') || null
    }

    function validateChangePasswordForm(form){
        const errors = []
        const currentPasswordInput = form.querySelector('[name="current_password"]')
        const newPasswordInput = form.querySelector('[name="new_password"]')
        const confirmPasswordInput = form.querySelector('[name="confirm_password"]')
        const minLength = Number(form.dataset.passwordMinLength || 8)
        const maxLength = Number(form.dataset.passwordMaxLength || 72)

        ['current_password', 'new_password', 'confirm_password'].forEach(function (fieldName){
            clearChangePasswordFieldError(form, fieldName)
        })

        const currentPassword = currentPasswordInput ? String(currentPasswordInput.value) : ''
        const newPassword = newPasswordInput ? String(newPasswordInput.value) : ''
        const confirmPassword = confirmPasswordInput ? String(confirmPasswordInput.value) : ''

        if (currentPassword.trim() === ''){
            const message = 'Укажите текущий пароль.'
            errors.push(message)
            setChangePasswordFieldError(form, 'current_password', message)
        }

        if (newPassword.trim() === ''){
            const message = 'Укажите новый пароль.'
            errors.push(message)
            setChangePasswordFieldError(form, 'new_password', message)
        }else if (newPassword.length < minLength){
            const message = 'Новый пароль должен содержать минимум ' + minLength + ' символов.'
            errors.push(message)
            setChangePasswordFieldError(form, 'new_password', message)
        }else if (newPassword.length > maxLength){
            const message = 'Новый пароль не должен превышать ' + maxLength + ' символов.'
            errors.push(message)
            setChangePasswordFieldError(form, 'new_password', message)
        }else if (currentPassword !== '' && newPassword === currentPassword){
            const message = 'Новый пароль должен отличаться от текущего.'
            errors.push(message)
            setChangePasswordFieldError(form, 'new_password', message)
        }

        if (confirmPassword.trim() === ''){
            const message = 'Подтвердите новый пароль.'
            errors.push(message)
            setChangePasswordFieldError(form, 'confirm_password', message)
        }else if (newPassword !== '' && newPassword !== confirmPassword){
            const message = 'Пароли не совпадают.'
            errors.push(message)
            setChangePasswordFieldError(form, 'confirm_password', message)
        }

        renderSummary(getChangePasswordSummaryNode(form), 'Исправьте ошибки в форме:', errors, 'settings-modal__summary-list')
        return errors.length === 0
    }

    function initRegisterValidation(form){
        if (!form || form.dataset.validationReady === 'true'){
            return
        }

        form.dataset.validationReady = 'true'

        form.querySelectorAll('.register-form__input').forEach(function (input){
            input.addEventListener('input', function (){
                clearRegisterFieldError(form, input.name)
                const summaryNode = form.querySelector('[data-form-error-summary]') || document.querySelector('[data-form-error-summary]')
                renderSummary(summaryNode, '', [], 'register-form__summary-list')
            })
        })

        form.addEventListener('submit', function (event){
            if (!validateRegisterForm(form)){
                event.preventDefault()
            }
        })
    }

    function initLoginValidation(form){
        if (!form || form.dataset.validationReady === 'true'){
            return
        }

        form.dataset.validationReady = 'true'

        form.querySelectorAll('.login-form__input').forEach(function (input){
            input.addEventListener('input', function (){
                clearLoginFieldError(form, input.name)
                const summaryNode = form.querySelector('[data-login-form-error-summary]') || document.querySelector('[data-login-form-error-summary]')
                renderSummary(summaryNode, '', [], 'login-form__summary-list')
            })
        })

        form.addEventListener('submit', function (event){
            if (!validateLoginForm(form)){
                event.preventDefault()
            }
        })
    }

    function initCreateWishValidation(form){
        if (!form || form.dataset.validationReady === 'true'){
            return
        }

        form.dataset.validationReady = 'true'
        const summaryNode = getCreateWishSummaryNode(form)

        form.querySelectorAll('.create-wish-form__input, .create-wish-form__textarea').forEach(function (input){
            input.addEventListener('input', function (){
                clearCreateWishFieldError(form, input.name)
                renderSummary(summaryNode, '', [], 'create-wish-modal__summary-list')
            })
        })

        const imageInput = form.querySelector('[name="image"]')
        if (imageInput){
            imageInput.addEventListener('change', function (){
                clearCreateWishFieldError(form, 'image')
                renderSummary(summaryNode, '', [], 'create-wish-modal__summary-list')
            })
        }

        form.addEventListener('submit', function (event){
            if (!validateCreateWishForm(form)){
                event.preventDefault()
            }
        })
    }

    function initEditWishValidation(form){
        if (!form || form.dataset.validationReady === 'true'){
            return
        }

        form.dataset.validationReady = 'true'
        const summaryNode = getEditWishSummaryNode(form)

        form.querySelectorAll('.create-wish-form__input, .create-wish-form__textarea').forEach(function (input){
            input.addEventListener('input', function (){
                clearCreateWishFieldError(form, input.name)
                renderSummary(summaryNode, '', [], 'create-wish-modal__summary-list')
            })
        })

        const imageInput = form.querySelector('[name="image"]')
        if (imageInput){
            imageInput.addEventListener('change', function (){
                clearCreateWishFieldError(form, 'image')
                renderSummary(summaryNode, '', [], 'create-wish-modal__summary-list')
            })
        }

        form.addEventListener('submit', function (event){
            if (!validateEditWishForm(form)){
                event.preventDefault()
            }
        })

        form.addEventListener('reset-validation-state', function (){
            clearWishValidationState(form, summaryNode)
        })
    }

    function initUpdateProfileValidation(form){
        if (!form || form.dataset.validationReady === 'true'){
            return
        }

        form.dataset.validationReady = 'true'
        const summaryNode = getUpdateProfileSummaryNode(form)

        form.querySelectorAll('.update-profile-modal__input, .update-profile-modal__textarea').forEach(function (input){
            input.addEventListener('input', function (){
                clearUpdateFieldError(form, input.name)
                renderSummary(summaryNode, '', [], 'settings-modal__summary-list')
            })

            input.addEventListener('change', function (){
                clearUpdateFieldError(form, input.name)
                renderSummary(summaryNode, '', [], 'settings-modal__summary-list')
            })
        })

        const avatarInput = form.closest('[data-update-profile-modal]')?.querySelector('[data-avatar-upload-input]')
        if (avatarInput){
            avatarInput.addEventListener('change', function (){
                clearUpdateFieldError(form, 'avatar')
                renderSummary(summaryNode, '', [], 'settings-modal__summary-list')
            })
        }

        form.addEventListener('submit', function (event){
            if (!validateUpdateProfileForm(form)){
                event.preventDefault()
            }
        })
    }

    function initChangeEmailValidation(form){
        if (!form || form.dataset.validationReady === 'true'){
            return
        }

        form.dataset.validationReady = 'true'
        const summaryNode = getChangeEmailSummaryNode(form)

        form.querySelectorAll('.change-email-modal__input').forEach(function (input){
            input.addEventListener('input', function (){
                clearChangeEmailFieldError(form, input.name)
                renderSummary(summaryNode, '', [], 'settings-modal__summary-list')
            })
        })

        form.addEventListener('submit', function (event){
            if (!validateChangeEmailForm(form)){
                event.preventDefault()
            }
        })
    }

    function initChangePasswordValidation(form){
        if (!form || form.dataset.validationReady === 'true'){
            return
        }

        form.dataset.validationReady = 'true'
        const summaryNode = getChangePasswordSummaryNode(form)

        form.querySelectorAll('.change-password-modal__input').forEach(function (input){
            input.addEventListener('input', function (){
                clearChangePasswordFieldError(form, input.name)
                renderSummary(summaryNode, '', [], 'settings-modal__summary-list')
            })
        })

        form.addEventListener('submit', function (event){
            if (!validateChangePasswordForm(form)){
                event.preventDefault()
            }
        })
    }

    window.WishlistFormValidation = {
        initRegisterValidation: initRegisterValidation,
        initLoginValidation: initLoginValidation,
        initCreateWishValidation: initCreateWishValidation,
        initEditWishValidation: initEditWishValidation,
        initUpdateProfileValidation: initUpdateProfileValidation,
        initChangeEmailValidation: initChangeEmailValidation,
        initChangePasswordValidation: initChangePasswordValidation,
        validateCreateWishForm: validateCreateWishForm,
        validateEditWishForm: validateEditWishForm
    }
})()