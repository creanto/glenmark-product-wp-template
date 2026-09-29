(function () {
    'use strict';

    var selector = '.wr-animate';
    var activeClass = 'wr-animation-active';
    var previewClass = 'wr-animation-preview-pending';
    var states = new WeakMap();

    function isEditMode() {
        return Boolean(window.elementorFrontend && window.elementorFrontend.isEditMode && window.elementorFrontend.isEditMode());
    }

    function parseBoolean(value) {
        return value === 'yes' || value === 'true' || value === '1';
    }

    function parseThreshold(value) {
        var threshold = parseFloat(value);

        if (Number.isNaN(threshold)) {
            return 0.15;
        }

        return Math.max(0, Math.min(1, threshold));
    }

    function parseTime(value) {
        var time = String(value || '').trim();

        if (time.endsWith('ms')) {
            return parseFloat(time) || 0;
        }

        if (time.endsWith('s')) {
            return (parseFloat(time) || 0) * 1000;
        }

        return parseFloat(time) || 0;
    }

    function disconnect(element) {
        var state = states.get(element);

        if (!state) {
            return;
        }

        if (state.observer) {
            state.observer.disconnect();
        }

        if (state.previewTimer) {
            window.clearTimeout(state.previewTimer);
        }

        if (state.cleanupTimer) {
            window.clearTimeout(state.cleanupTimer);
        }

        states.delete(element);
    }

    function activate(element) {
        element.classList.add(activeClass);
    }

    function activateAfterPaint(element) {
        window.requestAnimationFrame(function () {
            window.requestAnimationFrame(function () {
                activate(element);
            });
        });
    }

    function deactivate(element) {
        element.classList.remove(activeClass);
    }

    function previewInEditor(element) {
        disconnect(element);

        element.classList.add(previewClass);
        deactivate(element);

        element.getBoundingClientRect();

        var previewTimer = window.setTimeout(function () {
            activate(element);
        }, 80);

        var styles = window.getComputedStyle(element);
        var totalTime = parseTime(styles.getPropertyValue('--wr-animation-duration')) +
            parseTime(styles.getPropertyValue('--wr-animation-delay')) +
            parseTime(styles.getPropertyValue('--wr-animation-order-delay')) +
            200;

        var cleanupTimer = window.setTimeout(function () {
            element.classList.remove(previewClass);
            activate(element);
        }, totalTime);

        states.set(element, {
            previewTimer: previewTimer,
            cleanupTimer: cleanupTimer
        });
    }

    function initElement(element) {
        disconnect(element);
        deactivate(element);
        element.getBoundingClientRect();

        if (isEditMode()) {
            previewInEditor(element);
            return;
        }

        if (element.dataset.wrAnimationTrigger === 'load') {
            activateAfterPaint(element);
            return;
        }

        // IntersectionObserver toggles the active class for viewport-triggered entrance animations.
        if (!('IntersectionObserver' in window)) {
            activate(element);
            return;
        }

        var triggerOnce = parseBoolean(element.dataset.wrAnimationOnce);
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    activateAfterPaint(entry.target);

                    if (triggerOnce) {
                        observer.unobserve(entry.target);
                    }
                } else if (!triggerOnce) {
                    deactivate(entry.target);
                }
            });
        }, {
            threshold: parseThreshold(element.dataset.wrAnimationThreshold)
        });

        observer.observe(element);
        states.set(element, {
            observer: observer
        });
    }

    function initAnimations(root) {
        var scope = root && root.nodeType === 1 ? root : document;
        var elements = [];

        if (scope.matches && scope.matches(selector)) {
            elements.push(scope);
        }

        scope.querySelectorAll(selector).forEach(function (element) {
            elements.push(element);
        });

        elements.forEach(initElement);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            initAnimations(document);
        });
    } else {
        initAnimations(document);
    }

    if (window.elementorFrontend && window.elementorFrontend.hooks) {
        window.elementorFrontend.hooks.addAction('frontend/element_ready/global', function (scope) {
            initAnimations(scope && scope[0] ? scope[0] : scope);
        });
    }
}());