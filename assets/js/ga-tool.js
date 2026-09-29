(() => {
    'use strict';

    /**
     * GA4 tracking for elements containing data-ga-event.
     *
     * Example:
     *
     * <a
     *   href="/test/"
     *   data-ga-event="cta_click"
     *   data-ga-name="magnesium_test"
     *   data-ga-location="homepage_hero"
     *   data-ga-destination="magnesium_test"
     * >
     *   Udělat test
     * </a>
     */

    document.addEventListener('click', (event) => {
        const element = event.target.closest('[data-ga-event]');

        if (!element) {
            return;
        }

        const eventName = element.dataset.gaEvent;

        if (!eventName) {
            return;
        }

        const parameters = {};

        if (element.dataset.gaName) {
            parameters.cta_name = element.dataset.gaName;
        }

        if (element.dataset.gaLocation) {
            parameters.cta_location = element.dataset.gaLocation;
        }

        if (element.dataset.gaProduct) {
            parameters.product_name = element.dataset.gaProduct;
        }

        if (element.dataset.gaDestination) {
            parameters.destination = element.dataset.gaDestination;
        }

        if (window.ga4TrackingDebug === true) {
            console.group('[GA4 Tracking]');
            console.log('Event:', eventName);
            console.log('Parameters:', parameters);
            console.log('Element:', element);
            console.groupEnd();
        }

        if (typeof window.gtag !== 'function') {
            if (window.ga4TrackingDebug === true) {
                console.warn('[GA4 Tracking] gtag() is not available.');
            }

            return;
        }

        window.gtag('event', eventName, parameters);
    });
})();