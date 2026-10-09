(function ($) {
    'use strict';

    if (!$) {
        return;
    }

    var initialized = false;

    function registerHeroEditorType() {
        if (!window.elementor || !elementor.elementsManager || !elementor.elementsManager.getElementTypeClass) {
            return;
        }

        if (elementor.elementsManager.getElementTypeClass('gln-hero')) {
            return;
        }

        var containerType = elementor.elementsManager.getElementTypeClass('container');
        if (!containerType || !containerType.constructor || !elementor.elementsManager.registerElementType) {
            return;
        }

        var heroType = new containerType.constructor();
        heroType.getType = function () {
            return 'gln-hero';
        };
        elementor.elementsManager.registerElementType(heroType);
    }

    function initializeScalableCanvas() {
        if (initialized || !window.elementorModules || !window.elementorFrontend) {
            return;
        }

        initialized = true;

        var ScalableCanvasHandler = elementorModules.frontend.handlers.Base.extend({
        onInit: function () {
            elementorModules.frontend.handlers.Base.prototype.onInit.apply(this, arguments);

            this.viewport = this.$element[0];
            this.stage = this.$element.children('.e-con-inner')[0];
            this.resizeObserver = null;
            this.onResize = this.updateScale.bind(this);

            if (!this.viewport || !this.stage) {
                return;
            }

            if (window.ResizeObserver) {
                this.resizeObserver = new ResizeObserver(this.onResize);
                this.resizeObserver.observe(this.viewport);
            }
            window.addEventListener('resize', this.onResize, { passive: true });
            this.updateScale();
        },

        updateScale: function () {
            if (!this.viewport || !this.stage) {
                return;
            }

            var designWidth = parseFloat(this.$element.attr('data-gln-canvas-width')) || 1200;
            var designHeight = parseFloat(this.$element.attr('data-gln-canvas-height')) || 410;
            var viewportWidth = this.viewport.getBoundingClientRect().width;
            var styles = window.getComputedStyle(this.viewport);
            var maxContentWidth = parseFloat(styles.getPropertyValue('--container-max-width')) || 1310;
            var availableWidth = Math.min(viewportWidth, maxContentWidth);
            var scale = availableWidth > 0 ? availableWidth / designWidth : 1;
            var grow = this.$element.attr('data-gln-canvas-grow') === 'yes';

            if (!grow) {
                scale = Math.min(scale, 1);
            }

            this.$element[0].style.setProperty('--gln-scalable-canvas-design-width', designWidth + 'px');
            this.$element[0].style.setProperty('--gln-scalable-canvas-design-height', designHeight + 'px');
            this.$element[0].style.setProperty('--gln-scalable-canvas-scale', scale);
            var contentOffset = Math.max(0, (viewportWidth - availableWidth) / 2);
            var stageOffset = grow || scale < 1 ? 0 : (availableWidth - designWidth) / 2;
            this.stage.style.marginLeft = (contentOffset + stageOffset) + 'px';
        },

        onDestroy: function () {
            if (this.resizeObserver) {
                this.resizeObserver.disconnect();
                this.resizeObserver = null;
            }

            window.removeEventListener('resize', this.onResize);
            this.viewport = null;
            this.stage = null;

            elementorModules.frontend.handlers.Base.prototype.onDestroy.apply(this, arguments);
        }
        });

        elementorFrontend.hooks.addAction('frontend/element_ready/gln-hero', function ($element) {
            if (!$element.hasClass('gln-scalable-canvas') || !$element.children('.e-con-inner').length) {
                return;
            }

            elementorFrontend.elementsHandler.addHandler(ScalableCanvasHandler, {
                $element: $element,
                elementName: 'gln-hero'
            });
        });
    }

    $(window).on('elementor/frontend/init', function () {
        registerHeroEditorType();
        initializeScalableCanvas();
    });

    $(window).on('elementor/init', registerHeroEditorType);
    registerHeroEditorType();
    initializeScalableCanvas();
}(jQuery));