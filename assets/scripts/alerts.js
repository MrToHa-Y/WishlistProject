(function (){
    'use strict'

    function hideAlert(alertNode){
        if (!alertNode){
            return
        }

        alertNode.classList.add('is-hidden')
        window.setTimeout(function (){
            if (alertNode.parentNode){
                alertNode.parentNode.removeChild(alertNode)
            }
        }, 250)
    }

    function initAlerts(rootNode){
        const root = rootNode || document
        const markerNode = root.documentElement || root

        if (markerNode.dataset && markerNode.dataset.alertsReady === 'true'){
            return
        }

        if (markerNode.dataset){
            markerNode.dataset.alertsReady = 'true'
        }

        root.addEventListener('click', function (event){
            const closeButton = event.target.closest('[data-alert-close]')
            if (!closeButton){
                return
            }

            const alertNode = closeButton.closest('[data-alert]')
            hideAlert(alertNode)
        })

        root.querySelectorAll('[data-alert]').forEach(function (alertNode){
            const autoHideMs = Number(alertNode.dataset.autoHide || 0)
            if (autoHideMs > 0){
                window.setTimeout(function (){
                    hideAlert(alertNode)
                }, autoHideMs)
            }
        })
    }

    window.WishlistAlerts = {
        init: initAlerts
    }
})()