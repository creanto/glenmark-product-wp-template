(function () {
    var elementorCarouselHooksBound = false;
    var productBuyModalEventsBound = false;

    function setupMobileMenu() {
        var header = document.querySelector('.site-header');

        if (!header) {
            return;
        }

        var toggle = header.querySelector('.header-menu-toggle');
        var nav = header.querySelector('.site-navigation');
        var closeToggle = header.querySelector('.site-navigation-close');

        if (!toggle || !nav) {
            return;
        }

        function openMenu() {
            header.classList.add('nav-open');
            toggle.setAttribute('aria-expanded', 'true');
        }

        function isClickInsideMenu(target) {
            return target && (nav.contains(target) || toggle.contains(target));
        }

        function handleDocumentClick(event) {
            if (!header.classList.contains('nav-open') || window.innerWidth > 1024) {
                return;
            }

            if (isClickInsideMenu(event.target)) {
                return;
            }

            closeMenu();
        }

        function closeMenu() {
            header.classList.remove('nav-open');
            toggle.setAttribute('aria-expanded', 'false');
        }

        window.glenmarkCloseMobileMenu = closeMenu;

        toggle.addEventListener('click', function () {
            if (header.classList.contains('nav-open')) {
                closeMenu();
                return;
            }

            openMenu();
        });

        if (closeToggle) {
            closeToggle.addEventListener('click', closeMenu);
        }

        document.addEventListener('click', handleDocumentClick);

        nav.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', closeMenu);
        });

        window.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeMenu();
            }
        });

        window.addEventListener('resize', function () {
            if (window.innerWidth > 1024) {
                closeMenu();
            }

            toggleMobileAdminBarHeaderOffset();
        });
    }

    function setupBackToTop() {
        var button = document.querySelector('.glenmark-back-to-top');

        if (!button) {
            return;
        }

        function updateVisibility() {
            button.classList.toggle('is-visible', window.scrollY > 320);
        }

        button.addEventListener('click', function () {
            window.scrollTo({
                top: 0,
                behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth'
            });
        });

        updateVisibility();
        window.addEventListener('scroll', updateVisibility, { passive: true });
    }

    function setupBottomNotice() {
        var notice = document.querySelector('.glenmark-bottom-notice');

        if (!notice) {
            return;
        }

        function updateContentOffset() {
            document.documentElement.style.setProperty('--gln-bottom-notice-height', notice.offsetHeight + 'px');
        }

        updateContentOffset();
        window.addEventListener('resize', updateContentOffset);

        if (typeof ResizeObserver !== 'undefined') {
            new ResizeObserver(updateContentOffset).observe(notice);
        }
    }

    function toggleHeaderState() {
        var headers = document.querySelectorAll('.site-header[data-scroll-watch="1"]');

        if (!headers.length) {
            return;
        }

        var scrollY = window.pageYOffset || document.documentElement.scrollTop || 0;

        headers.forEach(function (header) {
            var canShrink = header.getAttribute('data-shrink') === '1';
            var isTransparent = header.getAttribute('data-transparent') === '1';
            var showOnScroll = header.getAttribute('data-hide-on-scroll') === '1';

            if (!canShrink && !isTransparent && !showOnScroll) {
                return;
            }

            var isScrolled = header.classList.contains('is-scrolled');

            if (!isScrolled && scrollY >= 48) {
                header.classList.add('is-scrolled');
            } else if (isScrolled && scrollY <= 2) {
                header.classList.remove('is-scrolled');
            }

            if (showOnScroll) {
                if (scrollY > 36) {
                    header.classList.add('is-visible');
                } else {
                    header.classList.remove('is-visible');
                }
            } else {
                header.classList.remove('is-visible');
            }

        });
    }

    function toggleMobileAdminBarHeaderOffset() {
        var body = document.body;

        if (!body.classList.contains('admin-bar')) {
            return;
        }

        var header = document.querySelector('.site-header');

        if (!header) {
            return;
        }

        var isSmallViewport = window.innerWidth <= 782;
        var scrollY = window.pageYOffset || document.documentElement.scrollTop || 0;

        if (isSmallViewport && scrollY > 0) {
            header.classList.add('is-adminbar-scrolled');
            return;
        }

        header.classList.remove('is-adminbar-scrolled');
    }

    var lastScrollY = 0;
    var ticking = false;

    function handleScroll() {
        var currentScrollY = window.pageYOffset || document.documentElement.scrollTop || 0;
        toggleHeaderState();

        var headers = document.querySelectorAll('.site-header[data-hide-on-scroll="1"]');

        headers.forEach(function (header) {
            if (currentScrollY > 24) {
                header.classList.add('is-visible');
            } else {
                header.classList.remove('is-visible');
            }
        });

        lastScrollY = currentScrollY;
        ticking = false;
    }

    function requestHeaderScrollUpdate() {
        if (ticking) {
            return;
        }

        ticking = true;
        window.requestAnimationFrame(handleScroll);
    }

    function initHeaderScrollBehavior() {
        toggleHeaderState();
        requestHeaderScrollUpdate();

        window.addEventListener('scroll', requestHeaderScrollUpdate, { passive: true });
        window.addEventListener('resize', toggleHeaderState);
    }

    function revealLineElement(element) {
        if (!element) {
            return;
        }

        element.classList.add('is-visible');
    }

    function setupRevealLineAnimations() {
        var elements = document.querySelectorAll('.reveal-line');

        if (!elements.length) {
            return;
        }

        if (document.body.classList.contains('elementor-editor-active') || document.body.classList.contains('elementor-editor-preview')) {
            elements.forEach(function (element) {
                revealLineElement(element);
            });
            return;
        }

        if (typeof IntersectionObserver === 'undefined') {
            elements.forEach(function (element) {
                revealLineElement(element);
            });
            return;
        }

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) {
                    return;
                }

                revealLineElement(entry.target);
                observer.unobserve(entry.target);
            });
        }, {
            threshold: 0.2,
            rootMargin: '0px 0px -10% 0px'
        });

        elements.forEach(function (element) {
            observer.observe(element);
        });
    }

    function setupElementorRevealHooks() {
        if (!window.elementorFrontend || !window.elementorFrontend.hooks) {
            return;
        }

        window.elementorFrontend.hooks.addAction('frontend/element_ready/widget', function ($element) {
            if (!$element || !$element.length) {
                return;
            }

            var revealElements = $element.find('.reveal-line');

            if (!revealElements.length) {
                return;
            }

            revealElements.each(function () {
                revealLineElement(this);
            });
        });
    }

    function setupActiveMenuState() {
        var menuLinks = document.querySelectorAll('.nav-menu a[href*="#"]');

        if (!menuLinks.length) {
            return;
        }

        function normalizeHash(hash) {
            if (!hash) {
                return '';
            }

            var cleaned = hash.replace(/^#/, '').trim();
            return decodeURIComponent(cleaned);
        }

        function setActiveLink(link, isActive) {
            var anchor = link;
            var listItem = anchor.closest('li');

            anchor.classList.toggle('is-active', isActive);
            if (listItem) {
                listItem.classList.toggle('is-active', isActive);
            }
        }

        function updateActiveMenuState() {
            var activeId = '';
            var viewportMidpoint = window.innerHeight * 0.35;

            menuLinks.forEach(function (link) {
                var href = link.getAttribute('href') || '';
                var parsedUrl;

                try {
                    parsedUrl = new URL(href, window.location.href);
                } catch (error) {
                    return;
                }

                var hash = normalizeHash(parsedUrl.hash);
                if (!hash) {
                    return;
                }

                var target = document.getElementById(hash) || document.querySelector('[name="' + hash.replace(/"/g, '\\"') + '"]');

                if (!target) {
                    return;
                }

                var rect = target.getBoundingClientRect();
                var isInView = rect.top <= viewportMidpoint && rect.bottom >= viewportMidpoint;

                if (isInView) {
                    activeId = hash;
                }
            });

            if (!activeId) {
                activeId = normalizeHash(window.location.hash);
            }

            menuLinks.forEach(function (link) {
                var href = link.getAttribute('href') || '';
                var parsedUrl;

                try {
                    parsedUrl = new URL(href, window.location.href);
                } catch (error) {
                    return;
                }

                var hash = normalizeHash(parsedUrl.hash);
                var isLinkActive = hash && activeId && hash === activeId;

                setActiveLink(link, isLinkActive);

                if (hash && !activeId && window.location.hash && hash === normalizeHash(window.location.hash)) {
                    setActiveLink(link, true);
                }
            });
        }

        updateActiveMenuState();
        window.addEventListener('scroll', updateActiveMenuState, { passive: true });
        window.addEventListener('hashchange', updateActiveMenuState);
    }

    // href="#" on the CookieYes trigger would otherwise jump the page to the top.
    function setupCookieSettingsLinks() {
        document.addEventListener('click', function (event) {
            var target = event.target;

            if (target && typeof target.closest === 'function' && target.closest('.cky-banner-element')) {
                event.preventDefault();
            }
        });
    }

    function setupMenuHashScroll() {
        var menuLinks = document.querySelectorAll('.nav-menu a[href*="#"]');

        if (!menuLinks.length) {
            return;
        }

        var header = document.querySelector('.site-header');
        var prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        var animationFrameId = null;
        var restoreScrollBehaviorTimer = null;

        function cancelActiveAnimation() {
            if (animationFrameId !== null) {
                window.cancelAnimationFrame(animationFrameId);
                animationFrameId = null;
            }
        }

        function getHeaderOffset() {
            if (!header) {
                return 0;
            }

            var headerHeight = Math.max(0, header.getBoundingClientRect().height || 0);

            return headerHeight + 28;
        }

        function easeInOutQuart(progress) {
            return progress < 0.5
                ? 8 * Math.pow(progress, 4)
                : 1 - Math.pow(-2 * progress + 2, 4) / 2;
        }

        function getTargetFromHash(hash) {
            if (!hash || hash === '#') {
                return null;
            }

            var rawId = hash.slice(1);
            var decodedId;

            try {
                decodedId = decodeURIComponent(rawId);
            } catch (error) {
                decodedId = rawId;
            }

            var targetById = document.getElementById(decodedId);

            if (targetById) {
                return targetById;
            }

            return document.querySelector('[name="' + decodedId.replace(/"/g, '\\"') + '"]');
        }

        function animateScrollTo(target, instant) {
            cancelActiveAnimation();

            var startY = window.pageYOffset || document.documentElement.scrollTop || 0;
            var targetY = target.getBoundingClientRect().top + startY - getHeaderOffset();
            targetY = Math.max(0, targetY);

            if (Math.abs(targetY - startY) < 2) {
                window.scrollTo(0, targetY);
                return;
            }

            var docElement = document.documentElement;
            var bodyElement = document.body;
            var previousDocBehavior = docElement.style.scrollBehavior;
            var previousBodyBehavior = bodyElement.style.scrollBehavior;

            docElement.style.scrollBehavior = 'auto';
            bodyElement.style.scrollBehavior = 'auto';

            if (restoreScrollBehaviorTimer !== null) {
                window.clearTimeout(restoreScrollBehaviorTimer);
                restoreScrollBehaviorTimer = null;
            }

            if (instant || prefersReducedMotion) {
                window.scrollTo(0, targetY);
                docElement.style.scrollBehavior = previousDocBehavior;
                bodyElement.style.scrollBehavior = previousBodyBehavior;
                return;
            }

            var duration = 900;
            var distance = targetY - startY;
            var startTime = null;

            function step(timestamp) {
                if (startTime === null) {
                    startTime = timestamp;
                }

                var elapsed = timestamp - startTime;
                var progress = Math.min(elapsed / duration, 1);
                var easedProgress = easeInOutQuart(progress);

                window.scrollTo(0, startY + distance * easedProgress);

                if (progress < 1) {
                    animationFrameId = window.requestAnimationFrame(step);
                } else {
                    animationFrameId = null;
                    docElement.style.scrollBehavior = previousDocBehavior;
                    bodyElement.style.scrollBehavior = previousBodyBehavior;
                }
            }

            animationFrameId = window.requestAnimationFrame(step);

            restoreScrollBehaviorTimer = window.setTimeout(function () {
                docElement.style.scrollBehavior = previousDocBehavior;
                bodyElement.style.scrollBehavior = previousBodyBehavior;
                restoreScrollBehaviorTimer = null;
            }, duration + 120);
        }

        function bindSamePageHashLink(link) {
            link.addEventListener('click', function (event) {
                var href = link.getAttribute('href') || '';
                var parsedUrl;

                try {
                    parsedUrl = new URL(href, window.location.href);
                } catch (error) {
                    return;
                }

                if (
                    parsedUrl.origin !== window.location.origin ||
                    parsedUrl.pathname !== window.location.pathname ||
                    parsedUrl.search !== window.location.search ||
                    !parsedUrl.hash
                ) {
                    return;
                }

                var target = getTargetFromHash(parsedUrl.hash);

                if (!target) {
                    return;
                }

                event.preventDefault();
                event.stopPropagation();
                if (typeof event.stopImmediatePropagation === 'function') {
                    event.stopImmediatePropagation();
                }

                if (
                    window.innerWidth <= 1024 &&
                    header &&
                    header.classList.contains('nav-open') &&
                    typeof window.glenmarkCloseMobileMenu === 'function'
                ) {
                    window.glenmarkCloseMobileMenu({ restoreScroll: false });
                }

                history.pushState(null, '', parsedUrl.hash);
                window.setTimeout(function () {
                    animateScrollTo(target, false);
                }, 20);
            }, true);
        }

        menuLinks.forEach(bindSamePageHashLink);

        document.querySelectorAll('a[href*="#"]:not(.nav-menu a):not(.gln-product-buy-modal-trigger):not(.gln-product-info-modal-trigger)').forEach(function (contentLink) {
            bindSamePageHashLink(contentLink);
        });

        if (window.location.hash) {
            var initialTarget = getTargetFromHash(window.location.hash);

            if (initialTarget) {
                window.setTimeout(function () {
                    animateScrollTo(initialTarget, true);
                }, 0);
            }
        }
    }

    function setupProductCarouselAndPopups(scope) {
        if (!window.jQuery) {
            return;
        }

        var $ = window.jQuery;
        var $scope = scope && scope.length ? scope : $(document);
        var $tracks = $scope.find('.gln-product-carousel__track');

        if (!productBuyModalEventsBound) {
            $(document).on('click', '.gln-product-buy-modal-trigger, .gln-product-info-modal-trigger', function (event) {
                var modalSelector = $(this).attr('href');

                if (!modalSelector || !$(modalSelector).length || !$.magnificPopup) {
                    return;
                }

                event.preventDefault();
                $.magnificPopup.open({
                    items: {
                        src: modalSelector,
                        type: 'inline',
                    },
                    midClick: true,
                });
            });
            productBuyModalEventsBound = true;
        }

        if ($scope.hasClass && $scope.hasClass('gln-product-carousel__track')) {
            $tracks = $tracks.add($scope);
        }

        $tracks.each(function () {
            var $track = $(this);
            var $carousel = $track.closest('.gln-product-carousel');
            var autoplay = String($carousel.data('autoplay')) === 'true';
            var speed = parseInt($carousel.data('speed'), 10) || 5000;
            var slidesToShow = parseInt($carousel.data('slides-to-show'), 10) || 3;
            slidesToShow = Math.max(1, Math.min(6, slidesToShow));
            var slidesToShowTablet = Math.max(1, Math.min(3, slidesToShow - 1));
            var slidesToShowMobile = Math.max(1, Math.min(2, slidesToShowTablet));

            if ($track.hasClass('slick-initialized')) {
                return;
            }

            if (typeof $track.slick !== 'function') {
                return;
            }

            $track.slick({
                slidesToShow: slidesToShow,
                slidesToScroll: 1,
                arrows: true,
                dots: false,
                infinite: true,
                autoplay: autoplay,
                autoplaySpeed: speed,
                adaptiveHeight: true,
                responsive: [
                    {
                        breakpoint: 1200,
                        settings: {
                            slidesToShow: slidesToShowTablet,
                        },
                    },
                    {
                        breakpoint: 900,
                        settings: {
                            slidesToShow: slidesToShowMobile,
                        },
                    },
                    {
                        breakpoint: 640,
                        settings: {
                            slidesToShow: 1,
                        },
                    },
                ],
            });
        });

    }

    function bindElementorCarouselHooks() {
        if (!window.jQuery || !window.elementorFrontend || !window.elementorFrontend.hooks) {
            return;
        }

        if (elementorCarouselHooksBound) {
            return;
        }

        elementorCarouselHooksBound = true;

        var $ = window.jQuery;

        window.elementorFrontend.hooks.addAction('frontend/element_ready/gln-product-carousel.default', function ($scope) {
            setupProductCarouselAndPopups($scope);
        });

        // Fallback for environments where widget-specific hook naming differs.
        window.elementorFrontend.hooks.addAction('frontend/element_ready/widget', function ($scope) {
            if (!$scope || !$scope.find || !$scope.find('.gln-product-carousel__track').length) {
                return;
            }

            setupProductCarouselAndPopups($scope);
        });

        // Run once on already-rendered editor content.
        setupProductCarouselAndPopups($('.elementor-editor-active .elementor-widget, .elementor-widget-gln-product-carousel'));
    }

    function setupProductPurposeFilter() {
        var filterRoot = document.querySelector('.gln-product-filter');

        if (!filterRoot) {
            var legacyButtons = document.querySelectorAll('.purpose-filter');

            legacyButtons.forEach(function (button) {
                button.addEventListener('click', function (event) {
                    var filterLink = button.querySelector('[data-product-purpose]');
                    var purpose = filterLink ? filterLink.getAttribute('data-product-purpose') : '';

                    if (!purpose) {
                        return;
                    }

                    event.preventDefault();
                    var wasActive = button.classList.contains('is-active');
                    legacyButtons.forEach(function (legacyButton) {
                        legacyButton.classList.toggle('is-active', !wasActive && legacyButton === button);
                    });
                    document.querySelectorAll('.gln-grid--products .gln-card--product').forEach(function (product) {
                        var purposes = (product.getAttribute('data-product-purpose') || '').split('|');
                        product.classList.toggle('is-purpose-filtered-out', !wasActive && purposes.indexOf(purpose) === -1);
                    });
                });
            });
            return;
        }

        var filterButtons = filterRoot.querySelectorAll('[data-filter-field]');
        var toggle = filterRoot.querySelector('.gln-product-filter__toggle');
        var panel = filterRoot.querySelector('.gln-product-filter__panel');
        var count = filterRoot.querySelector('[data-filter-count]');
        var countBadge = filterRoot.querySelector('.gln-product-filter__count');
        var clearButton = filterRoot.querySelector('[data-filter-clear]');

        toggle.addEventListener('click', function () {
            var isExpanded = toggle.getAttribute('aria-expanded') === 'true';
            toggle.setAttribute('aria-expanded', isExpanded ? 'false' : 'true');
            panel.hidden = isExpanded;
        });

        function updateProductGrid(updateProducts) {
            var products = Array.prototype.slice.call(document.querySelectorAll('.gln-grid--products .gln-card--product'));
            var firstPositions = new Map();

            products.forEach(function (product) {
                if (!product.classList.contains('is-purpose-filtered-out')) {
                    firstPositions.set(product, product.getBoundingClientRect());
                }
            });

            updateProducts();

            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                return;
            }

            products.forEach(function (product) {
                var firstPosition = firstPositions.get(product);

                if (!firstPosition || product.classList.contains('is-purpose-filtered-out')) {
                    return;
                }

                var lastPosition = product.getBoundingClientRect();
                var translateX = firstPosition.left - lastPosition.left;
                var translateY = firstPosition.top - lastPosition.top;

                if (translateX || translateY) {
                    product.animate([
                        { transform: 'translate(' + translateX + 'px, ' + translateY + 'px)' },
                        { transform: 'translate(0, 0)' },
                    ], {
                        duration: 360,
                        easing: 'cubic-bezier(0.2, 0.8, 0.2, 1)',
                    });
                }
            });
        }

        function filterProducts() {
            updateProductGrid(function () {
                var selectedValues = {};
                filterRoot.querySelectorAll('[data-filter-field][aria-pressed="true"]').forEach(function (button) {
                    var field = button.getAttribute('data-filter-field');
                    selectedValues[field] = selectedValues[field] || [];
                    selectedValues[field].push(button.getAttribute('data-filter-value'));
                });
                var selectedCount = Object.keys(selectedValues).reduce(function (total, field) {
                    return total + selectedValues[field].length;
                }, 0);

                document.querySelectorAll('.gln-grid--products .gln-card--product').forEach(function (product) {
                    var matches = !selectedCount;
                    Object.keys(selectedValues).some(function (field) {
                        var productValues = (product.getAttribute('data-' + field.replace(/_/g, '-')) || '').split('|');
                        return matches = selectedValues[field].some(function (value) {
                            return productValues.indexOf(value) !== -1;
                        });
                    });
                    product.classList.toggle('is-purpose-filtered-out', !matches);
                });
                count.textContent = selectedCount;
                countBadge.textContent = selectedCount;
                countBadge.hidden = !selectedCount;
                clearButton.hidden = !selectedCount;
            });
        }

        filterButtons.forEach(function (button) {
            button.addEventListener('click', function (event) {
                event.preventDefault();
                var isActive = button.getAttribute('aria-pressed') === 'true';
                button.setAttribute('aria-pressed', isActive ? 'false' : 'true');
                button.classList.toggle('is-active', !isActive);
                filterProducts();
            });
        });

        clearButton.addEventListener('click', function () {
            filterButtons.forEach(function (button) {
                button.setAttribute('aria-pressed', 'false');
                button.classList.remove('is-active');
            });
            filterProducts();
        });
    }

    function setupProductVariantSwitch() {
        document.querySelectorAll('.gln-variant-switch').forEach(function (switcher) {
            var article = switcher.closest('.gln-product-template');

            if (!article) {
                return;
            }

            var image = article.querySelector('.gln-product-packshot');
            var modalLinks = article.querySelectorAll('.gln-product-buy-modal__logo-link[data-pharmacy-key]');
            var buttons = switcher.querySelectorAll('.gln-variant-switch__item');

            buttons.forEach(function (button) {
                button.addEventListener('click', function () {
                    buttons.forEach(function (otherButton) {
                        otherButton.classList.remove('is-active');
                        otherButton.setAttribute('aria-pressed', 'false');
                    });
                    button.classList.add('is-active');
                    button.setAttribute('aria-pressed', 'true');

                    var imageUrl = button.getAttribute('data-image');

                    if (image && imageUrl) {
                        image.setAttribute('src', imageUrl);
                    }

                    var pharmacies = {};

                    try {
                        pharmacies = JSON.parse(button.getAttribute('data-pharmacies') || '{}');
                    } catch (error) {
                        pharmacies = {};
                    }

                    modalLinks.forEach(function (link) {
                        var key = link.getAttribute('data-pharmacy-key');

                        if (key && pharmacies[key]) {
                            link.setAttribute('href', pharmacies[key]);
                        }
                    });
                });
            });
        });
    }

    function setupProductLegalToggle() {
        document.querySelectorAll('.gln-product-legal__toggle').forEach(function (button) {
            var content = button.nextElementSibling;

            if (!content) {
                return;
            }

            button.addEventListener('click', function () {
                var isExpanded = button.getAttribute('aria-expanded') === 'true';

                button.setAttribute('aria-expanded', isExpanded ? 'false' : 'true');
                content.hidden = isExpanded;
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        setupMobileMenu();
        setupBackToTop();
        setupBottomNotice();
        toggleHeaderState();
        toggleMobileAdminBarHeaderOffset();
        setupRevealLineAnimations();
        setupElementorRevealHooks();
        setupActiveMenuState();
        setupProductCarouselAndPopups();
        setupProductPurposeFilter();
        setupProductVariantSwitch();
        setupProductLegalToggle();
        setupCookieSettingsLinks();
        try {
            setupMenuHashScroll();
        } catch (error) {
            // Keep other frontend features running even if hash-scroll binding fails.
            // eslint-disable-next-line no-console
            console.warn('Menu hash scroll initialization failed.', error);
        }
        window.addEventListener('scroll', function () {
            toggleHeaderState();
            toggleMobileAdminBarHeaderOffset();
        }, { passive: true });
        window.addEventListener('resize', toggleMobileAdminBarHeaderOffset);

        bindElementorCarouselHooks();

        if (window.jQuery) {
            window.jQuery(window).on('elementor/frontend/init', function () {
                bindElementorCarouselHooks();
            });
        }
    });
})();