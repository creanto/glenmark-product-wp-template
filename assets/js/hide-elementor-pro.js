/*
 * Hide Elementor Pro upsell elements in admin and editor UI.
 */
(function () {
    function hideContainer(node) {
        if (!node) {
            return;
        }

        var container = node.closest('li, section, article, div, span');

        if (container) {
            container.style.display = 'none';
            return;
        }

        node.style.display = 'none';
    }

    function hideBySelectors(selectors) {
        document.querySelectorAll(selectors).forEach(function (el) {
            hideContainer(el);
        });
    }

    function hideElementorProElements() {
        hideBySelectors([
            '#elementor-panel-get-pro-elements',
            '.elementor-nerd-box',
            '.elementor-upsell',
            '.elementor-promotion',
            '.e-upgrade-notice',
            '.elementor-go-pro',
            '.e-get-pro',
            '.e-upgrade',
            '.elementor-panel-footer-sub-menu-item[href*="go.elementor.com"]',
        ].join(', '));

        document.querySelectorAll('.elementor-panel-heading-promotion').forEach(function (el) {
            if (el.parentElement) {
                el.parentElement.remove();
            }
        });

        document.querySelectorAll('.elementor-panel-heading').forEach(function (button) {
            var title = button.querySelector('.elementor-panel-heading-title');
            if (title && title.textContent.trim() === 'Pro') {
                button.style.display = 'none';
            }
        });

        document.querySelectorAll('a[href*="go.elementor.com"], a[href*="go-pro"], a[href*="elementor-one-upgrade"]').forEach(function (link) {
            hideContainer(link);
        });

        hideBySelectors('.eicon-upgrade-crown-full, .eicon-pro-badge, [class*="pro-badge"], [class*="upgrade"] .eicon');
    }

    function initObserver() {
        if (!document.body || typeof MutationObserver === 'undefined') {
            return;
        }

        var observer = new MutationObserver(function () {
            hideElementorProElements();
        });

        observer.observe(document.body, {
            childList: true,
            subtree: true,
            attributes: false,
        });
    }

    hideElementorProElements();

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            hideElementorProElements();
            initObserver();
        });
    } else {
        initObserver();
    }

    setTimeout(hideElementorProElements, 600);
    setTimeout(hideElementorProElements, 1500);
})();