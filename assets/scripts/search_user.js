(function (){
    'use strict'

    function debounce(callback, delay){
        let timerId = null

        return function (...args){
            if (timerId !== null){
                window.clearTimeout(timerId)
            }

            timerId = window.setTimeout(function (){
                callback.apply(null, args)
            }, delay)
        }
    }

    function renderResultItem(container, user, userUrlTemplate, fallbackAvatar){
        const link = document.createElement('a')
        link.className = 'search-main__result'
        link.href = userUrlTemplate.replace('{id}', encodeURIComponent(String(user.id || '')))

        const avatar = document.createElement('img')
        avatar.className = 'search-main__result-avatar'
        avatar.loading = 'lazy'
        avatar.src = user.avatar || fallbackAvatar
        avatar.alt = user.username || 'User avatar'
        avatar.addEventListener('error', function (){
            avatar.src = fallbackAvatar
        })

        const name = document.createElement('span')
        name.className = 'search-main__result-name'
        name.textContent = user.username || 'User'

        link.appendChild(avatar)
        link.appendChild(name)
        container.appendChild(link)
    }

    function initContainer(container){
        const input = container.querySelector('[data-search-input]')
        const results = container.querySelector('[data-search-results]')
        const endpoint = (container.dataset.searchEndpoint || '').trim()
        const userUrlTemplate = (container.dataset.userUrlTemplate || '/pages/user.php?id={id}').trim()
        const fallbackAvatar = (container.dataset.defaultAvatar || '/assets/images/default_avatar.png').trim()
        const stateNodes = {
            empty: container.querySelector('[data-search-state="empty"]'),
            loading: container.querySelector('[data-search-state="loading"]'),
            noResults: container.querySelector('[data-search-state="no-results"]'),
            error: container.querySelector('[data-search-state="error"]'),
        }

        if (!input || !results || endpoint === ''){
            return
        }

        let requestId = 0
        let activeController = null

        const showState = function (stateKey){
            Object.keys(stateNodes).forEach(function (key){
                const node = stateNodes[key]
                if (!node){
                    return
                }
                node.hidden = key !== stateKey
            })
        }

        const clearResults = function (){
            results.innerHTML = ''
            results.hidden = true
        }

        const showResults = function (users){
            results.innerHTML = ''

            users.forEach(function (user){
                renderResultItem(results, user, userUrlTemplate, fallbackAvatar)
            })

            results.hidden = users.length === 0
        }

        const searchUsers = debounce(function (){
            const query = input.value.trim()

            if (query === ''){
                if (activeController){
                    activeController.abort()
                    activeController = null
                }

                clearResults()
                showState('empty')
                return
            }

            requestId += 1
            const localRequestId = requestId

            if (activeController){
                activeController.abort()
            }

            activeController = new AbortController()
            showState('loading')

            fetch(endpoint + '?q=' + encodeURIComponent(query), {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                signal: activeController.signal,
            })
                .then(function (response){
                    if (!response.ok){
                        throw new Error('HTTP_' + response.status)
                    }

                    return response.json()
                })
                .then(function (payload){
                    if (localRequestId !== requestId){
                        return
                    }

                    if (!payload || payload.success !== true || !Array.isArray(payload.users)){
                        clearResults()
                        showState('error')
                        return
                    }

                    if (payload.users.length === 0){
                        clearResults()
                        showState('noResults')
                        return
                    }

                    showResults(payload.users)
                    showState('')
                })
                .catch(function (error){
                    if (error && error.name === 'AbortError'){
                        return
                    }

                    if (localRequestId !== requestId){
                        return
                    }

                    clearResults()
                    showState('error')
                })
        }, 320)

        input.addEventListener('input', searchUsers)
        showState('empty')
    }

    function init(){
        document.querySelectorAll('[data-search-users]').forEach(initContainer)
    }

    window.WishlistSearchUsers = {
        init: init,
    }
})()