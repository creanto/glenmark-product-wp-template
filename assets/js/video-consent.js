(function () {
    'use strict';

    var CONSENT_COOKIE = 'cookieyes-consent';
    var PENDING_SELECTOR = '.gln-video-consent:not(.is-loaded)';
    var pollTimer = null;

    function getConsentMap() {
        var match = document.cookie.match(/(?:^|;\s*)cookieyes-consent=([^;]*)/);

        if (!match) {
            return {};
        }

        var map = {};

        decodeURIComponent(match[1]).split(',').forEach(function (pair) {
            var parts = pair.split(':');

            if (2 === parts.length) {
                map[parts[0].trim()] = parts[1].trim();
            }
        });

        return map;
    }

    function hasConsent(category) {
        return 'yes' === getConsentMap()[category];
    }

    function loadVideo(placeholder) {
        var src = placeholder.getAttribute('data-src');

        if (!src || placeholder.classList.contains('is-loaded')) {
            return;
        }

        var iframe = document.createElement('iframe');
        iframe.className = 'elementor-video-iframe';
        iframe.setAttribute('src', src);
        iframe.setAttribute('allow', 'accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture');
        iframe.setAttribute('allowfullscreen', '');
        iframe.setAttribute('title', placeholder.getAttribute('data-title') || '');

        placeholder.innerHTML = '';
        placeholder.appendChild(iframe);
        placeholder.classList.add('is-loaded');
    }

    function resolvePending() {
        var pending = document.querySelectorAll(PENDING_SELECTOR);

        Array.prototype.forEach.call(pending, function (placeholder) {
            if (hasConsent(placeholder.getAttribute('data-category') || 'advertisement')) {
                loadVideo(placeholder);
            }
        });

        if (!document.querySelector(PENDING_SELECTOR) && pollTimer) {
            window.clearInterval(pollTimer);
            pollTimer = null;
        }
    }

    function openConsentBanner() {
        if ('function' === typeof window.revisitCkyConsent) {
            window.revisitCkyConsent();

            return;
        }

        var revisit = document.querySelector('.cky-btn-revisit-wrapper button, .cky-btn-revisit');

        if (revisit) {
            revisit.click();
        }
    }

    function onClick(event) {
        var button = event.target.closest('.gln-video-consent__button');

        if (!button) {
            return;
        }

        event.preventDefault();

        var placeholder = button.closest('.gln-video-consent');

        if (hasConsent(placeholder.getAttribute('data-category') || 'advertisement')) {
            loadVideo(placeholder);

            return;
        }

        openConsentBanner();

        // CookieYes has no reliable "consent given" event across versions, so poll the cookie.
        if (!pollTimer) {
            pollTimer = window.setInterval(resolvePending, 500);
        }
    }

    function init() {
        if (!document.querySelector('.gln-video-consent')) {
            return;
        }

        resolvePending();
        document.addEventListener('click', onClick);
        document.addEventListener('cookieyes_consent_update', resolvePending);
    }

    if ('loading' === document.readyState) {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
