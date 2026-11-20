(function (){
    'use strict'

    function initInfiniteScroll(){
        const grid = document.querySelector('[data-catalog-grid]')
        const sensor = document.querySelector('[data-catalog-sensor]')

        if (!grid || !sensor || grid.dataset.infiniteReady === 'true'){
            return
        }

        const endpoint = grid.dataset.endpoint || '/actions/catalog/load_more_catalog.php'
        const limit = Number(grid.dataset.limit || 9)
        let offset = Number(grid.dataset.offset || grid.querySelectorAll('[data-catalog-card]').length || 0)
        let loading = false
        let finished = false

        grid.dataset.infiniteReady = 'true'

        const hideSensor = function (){
            sensor.hidden = true
        }

        const loadNextBatch = function (){
            if (loading || finished){
                return
            }

            loading = true
            sensor.dataset.state = 'loading'

            const url = endpoint + '?offset=' + encodeURIComponent(String(offset)) + '&limit=' + encodeURIComponent(String(limit))

            fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(function (response){
                    if (!response.ok){
                        throw new Error('Infinite scroll request failed')
                    }

                    return response.text()
                })
                .then(function (html){
                    const trimmed = html.trim()
                    if (trimmed === ''){
                        finished = true
                        hideSensor()
                        return
                    }

                    const wrapper = document.createElement('div')
                    wrapper.innerHTML = trimmed
                    const nodes = Array.from(wrapper.children)

                    if (!nodes.length){
                        finished = true
                        hideSensor()
                        return
                    }

                    nodes.forEach(function (node){
                        grid.appendChild(node)
                    })

                    offset += nodes.length
                    grid.dataset.offset = String(offset)
                })
                .catch(function (){
                    finished = true
                    hideSensor()
                })
                .finally(function (){
                    loading = false
                    if (!finished){
                        sensor.dataset.state = 'idle'
                    }
                })
        }

        const observer = new IntersectionObserver(
            function (entries){
                entries.forEach(function (entry){
                    if (entry.isIntersecting){
                        loadNextBatch()
                    }
                })
            },
            {
                rootMargin: '220px 0px'
            }
        )

        observer.observe(sensor)
    }

    window.WishlistInfiniteScroll = {
        init: initInfiniteScroll
    }
})()