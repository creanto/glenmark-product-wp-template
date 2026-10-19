(function () {
    'use strict';

    var CONSENT_COOKIE = 'wpconsent_preferences';
    var PENDING_SELECTOR = '.gln-video-consent:not(.is-loaded)';
    var pollTimer = null;

    function getConsentPreferences() {
        var match = document.cookie.match(new RegExp('(?:^|;\\s*)' + CONSENT_COOKIE + '=([^;]*)'));

        if (!match) {
            return {};
        }

        try {
            return JSON.parse(match[1]);
        } catch (error) {
            return {};
        }
    }

    function hasConsent(category) {
        return true === getConsentPreferences()[category];
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

        if (window.WPConsent && 'function' === typeof window.WPConsent.showPreferences) {
            window.WPConsent.showPreferences();

            return;
        }

        var revisit = document.querySelector('.wpconsent-open-preferences');

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

        // Poll the stored preference as a fallback for banner configurations without a save event.
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
        window.addEventListener('wpconsent_consent_saved', resolvePending);
        window.addEventListener('wpconsent_consent_updated', resolvePending);
    }

    if ('loading' === document.readyState) {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
