(function () {
    'use strict';

    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            var manifestLink = document.querySelector('link[rel="manifest"]');
            if (!manifestLink) return;

            var parsedManifest = new URL(manifestLink.href, window.location.href);
            var baseSameOrigin = window.location.origin + parsedManifest.pathname;
            var swUrl = new URL('sw.js', baseSameOrigin);
            var scopeUrl = new URL('./', baseSameOrigin);

            navigator.serviceWorker.register(swUrl.href, {
                scope: scopeUrl.pathname
            }).catch(function (err) {
                console.warn('SIGI PWA: error al registrar el Service Worker', err);
            });
        });
    }

    var deferredPrompt = null;
    var isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;

    function toggleInstallButton(visible) {
        var btn = document.getElementById('btn-instalar-pwa');
        if (!btn) return;
        if (visible && !isStandalone) {
            btn.classList.remove('d-none');
        } else {
            btn.classList.add('d-none');
        }
    }

    window.addEventListener('beforeinstallprompt', function (event) {
        event.preventDefault();
        deferredPrompt = event;
        toggleInstallButton(true);
    });

    document.addEventListener('click', function (event) {
        var btn = event.target.closest('#btn-instalar-pwa');
        if (!btn || !deferredPrompt) return;

        deferredPrompt.prompt();
        deferredPrompt.userChoice.finally(function () {
            deferredPrompt = null;
            toggleInstallButton(false);
        });
    });

    window.addEventListener('appinstalled', function () {
        deferredPrompt = null;
        toggleInstallButton(false);
    });
})();
