(function (){
    'use strict'

    function initProfileModal(){
        const toggleButton = document.querySelector('[data-profile-toggle]')
        const modal = document.querySelector('[data-profile-modal]')

        if (!toggleButton || !modal){
            return
        }

        const closeButton = modal.querySelector('[data-profile-close]')

        const openModal = function (){
            modal.hidden = false
            modal.classList.add('is-open')
            toggleButton.setAttribute('aria-expanded', 'true')
        }

        const closeModal = function (){
            modal.hidden = true
            modal.classList.remove('is-open')
            toggleButton.setAttribute('aria-expanded', 'false')
        }

        toggleButton.addEventListener('click', function (){
            if (modal.hidden){
                openModal()
                return
            }

            closeModal()
        })

        if (closeButton){
            closeButton.addEventListener('click', closeModal)
        }

        document.addEventListener('click', function (event){
            if (modal.hidden){
                return
            }

            if (!modal.contains(event.target) && !toggleButton.contains(event.target)){
                closeModal()
            }
        })

        document.addEventListener('keydown', function (event){
            if (event.key === 'Escape' && !modal.hidden){
                closeModal()
            }
        })
    }

    function initHomeHeaderModal(){
        const header = document.querySelector('[data-home-header]')
        if (!header){
            return
        }

        const searchToggle = header.querySelector('[data-header-search-toggle]')
        const searchLayer = document.querySelector('[data-header-search-layer]')
        const searchPanel = document.querySelector('[data-header-search-panel]')
        const searchInput = document.querySelector('[data-header-search-input]')
        const userToggle = header.querySelector('[data-header-user-toggle]')
        const userMenu = header.querySelector('[data-header-user-menu]')

        const openSearch = function (){
            if (!searchLayer){
                return
            }

            searchLayer.hidden = false
            if (searchToggle){
                searchToggle.setAttribute('aria-expanded', 'true')
            }

            if (searchInput){
                window.setTimeout(function (){
                    searchInput.focus()
                }, 10)
            }
        }

        const closeSearch = function (){
            if (!searchLayer){
                return
            }

            searchLayer.hidden = true
            if (searchToggle){
                searchToggle.setAttribute('aria-expanded', 'false')
            }
        }

        const openUserMenu = function (){
            if (!userMenu || !userToggle){
                return
            }

            userMenu.hidden = false
            userToggle.setAttribute('aria-expanded', 'true')
        }

        const closeUserMenu = function (){
            if (!userMenu || !userToggle){
                return
            }

            userMenu.hidden = true
            userToggle.setAttribute('aria-expanded', 'false')
        }

        if (searchToggle && searchLayer){
            searchToggle.addEventListener('click', function (){
                if (searchLayer.hidden){
                    openSearch()
                    return
                }

                closeSearch()
            })
        }

        if (searchLayer && searchPanel){
            searchLayer.addEventListener('click', function (event){
                if (!searchPanel.contains(event.target)){
                    closeSearch()
                }
            })
        }

        if (userToggle && userMenu){
            userToggle.addEventListener('click', function (){
                if (userMenu.hidden){
                    openUserMenu()
                    return
                }

                closeUserMenu()
            })

            document.addEventListener('click', function (event){
                if (userMenu.hidden){
                    return
                }

                if (!userMenu.contains(event.target) && !userToggle.contains(event.target)){
                    closeUserMenu()
                }
            })
        }

        document.addEventListener('keydown', function (event){
            if (event.key !== 'Escape'){
                return
            }

            if (searchLayer && !searchLayer.hidden){
                closeSearch()
            }

            if (userMenu && !userMenu.hidden){
                closeUserMenu()
            }
        })
    }

    window.WishlistModal = {
        initProfileModal: initProfileModal,
        initHomeHeaderModal: initHomeHeaderModal
    }
})()
