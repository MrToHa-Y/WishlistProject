(function (){
    'use strict'

    let PAGE_CLASSES = ['register-page', 'login-page', 'error-page']
    let IMAGE_NAMES = [
        'photo_1.png',
        'photo_2.png',
        'photo_3.png',
        'photo_4.png',
        'photo_5.png',
        'photo_6.png',
        'photo_7.png',
        'photo_8.png',
        'photo_9.png',
        'photo_10.png']

    let BASE_PATH = '/assets/images/items_for_background_animation/'
    let DESKTOP_COUNT = 9
    let MOBILE_COUNT = 6
    let MIN_SPEED = 0.1
    let MAX_SPEED = 0.065

    function randomBetween(min, max){
        return Math.random() * (max - min) + min
    }

    function chooseRandom(list){
        return list[Math.floor(Math.random() * list.length)]
    }

    function resolveItemCount(){
        return window.matchMedia('(max-width: 860px)').matches ? MOBILE_COUNT : DESKTOP_COUNT
    }

    function resolveSize(){
        if (window.matchMedia('(max-width: 480px)').matches){
            return randomBetween(72, 136)
        }

        if (window.matchMedia('(max-width: 860px)').matches){
            return randomBetween(88, 164)
        }

        return randomBetween(110, 220)
    }

    function createItem(container, width, height){
        let size = resolveSize()
        let x = randomBetween(0, Math.max(width - size, 0))
        let y = randomBetween(0, Math.max(height - size, 0))
        let direction = randomBetween(0, Math.PI * 2)
        let speed = randomBetween(MIN_SPEED, MAX_SPEED)

        let item = document.createElement('div')
        item.className = 'bg-photo-animation__item'
        item.style.width = size + 'px'
        item.style.height = size + 'px'

        let image = document.createElement('img')
        image.src = BASE_PATH + chooseRandom(IMAGE_NAMES)
        image.alt = ''
        image.loading = 'lazy'
        image.decoding = 'async'

        item.appendChild(image)
        container.appendChild(item)

        return {
            element: item,
            size: size,
            x: x,
            y: y,
            vx: Math.cos(direction) * speed,
            vy: Math.sin(direction) * speed,
            angle: randomBetween(0, 360),
            spin: randomBetween(-0.012, 0.012)
        }
    }

    function isTargetPage(body){
        return PAGE_CLASSES.some(function (className){
            return body.classList.contains(className)
        })
    }

    function init(){
        let body = document.body
        if (!body || !isTargetPage(body) || body.dataset.bgPhotoAnimationReady === '1'){
            return
        }

        body.dataset.bgPhotoAnimationReady = '1'
        body.classList.add('has-photo-bg-animation')

        let container = document.createElement('div')
        container.className = 'bg-photo-animation'
        container.setAttribute('aria-hidden', 'true')
        body.insertBefore(container, body.firstChild)

        let width = window.innerWidth
        let height = window.innerHeight
        let items = []

        for (let index = 0; index < resolveItemCount(); index += 1){
            items.push(createItem(container, width, height))
        }

        function renderItem(item){
            item.element.style.transform = 'translate3d(' + item.x + 'px, ' + item.y + 'px, 0) rotate(' + item.angle + 'deg)'
        }

        items.forEach(renderItem)

        let reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches
        if (reduceMotion){
            return
        }

        function handleResize(){
            width = window.innerWidth
            height = window.innerHeight

            items.forEach(function (item){
                item.x = Math.min(Math.max(item.x, 0), Math.max(width - item.size, 0))
                item.y = Math.min(Math.max(item.y, 0), Math.max(height - item.size, 0))
                renderItem(item)
            })
        }

        window.addEventListener('resize', handleResize)

        let lastFrame = performance.now()

        function step(now){
            let delta = now - lastFrame
            lastFrame = now

            items.forEach(function (item){
                item.x += item.vx * delta
                item.y += item.vy * delta
                item.angle += item.spin * delta

                if (item.x <= 0){
                    item.x = 0
                    item.vx = Math.abs(item.vx)
                }else if (item.x + item.size >= width){
                    item.x = Math.max(width - item.size, 0)
                    item.vx = -Math.abs(item.vx)
                }

                if (item.y <= 0){
                    item.y = 0
                    item.vy = Math.abs(item.vy)
                }else if (item.y + item.size >= height){
                    item.y = Math.max(height - item.size, 0)
                    item.vy = -Math.abs(item.vy)
                }

                if (Math.random() < 0.002 * delta){
                    let direction = Math.atan2(item.vy, item.vx) + randomBetween(-0.6, 0.6)
                    let speed = randomBetween(MIN_SPEED, MAX_SPEED)
                    item.vx = Math.cos(direction) * speed
                    item.vy = Math.sin(direction) * speed
                }

                renderItem(item)
            })

            window.requestAnimationFrame(step)
        }

        window.requestAnimationFrame(step)
    }

    window.WishlistBackgroundPhotoAnimation = {
        init: init
    }
})()