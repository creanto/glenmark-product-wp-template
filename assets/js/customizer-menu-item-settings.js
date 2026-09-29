(function ($, customize, settings) {
    'use strict';

    if (!customize || !settings) {
        return;
    }

    var heroMenuItemIds = settings.heroMenuItemIds || [];

    function isHeroMenuItem(menuItemId) {
        return heroMenuItemIds.indexOf(menuItemId) !== -1;
    }

    function updateHeroMenuItem(menuItemId, isHero) {
        $.post(settings.ajaxUrl, {
            action: 'webrev_update_menu_item_hero',
            nonce: settings.nonce,
            menu_item_id: menuItemId,
            is_hero: isHero ? '1' : '0'
        });
    }

    function addHeroMenuItemField(menuItemSettings) {
        var $menuItemSettings = $(menuItemSettings);

        if ($menuItemSettings.find('.field-webrev-hero').length) {
            return;
        }

        var menuItemId = parseInt($menuItemSettings.find('.menu-item-data-db-id').val(), 10);

        if (!menuItemId || menuItemId < 1) {
            return;
        }

        var fieldId = 'edit-menu-item-webrev-hero-' + menuItemId;
        var $field = $('<p>', {
            'class': 'field-webrev-hero description description-wide'
        });
        var $label = $('<label>', {
            'for': fieldId,
            text: 'Hero'
        });
        var $input = $('<input>', {
            id: fieldId,
            type: 'checkbox',
            checked: isHeroMenuItem(menuItemId)
        });

        $input.on('change', function () {
            var isHero = this.checked;

            updateHeroMenuItem(menuItemId, isHero);

            if (isHero) {
                heroMenuItemIds.push(menuItemId);
                return;
            }

            heroMenuItemIds = heroMenuItemIds.filter(function (heroMenuItemId) {
                return heroMenuItemId !== menuItemId;
            });
        });

        $label.prepend($input);
        $field.append($label);
        $menuItemSettings.append($field);
    }

    function addHeroMenuItemFields() {
        $('.menu-item-settings').each(function () {
            addHeroMenuItemField(this);
        });
    }

    customize.bind('ready', function () {
        addHeroMenuItemFields();

        new MutationObserver(addHeroMenuItemFields).observe(document.body, {
            childList: true,
            subtree: true
        });
    });
}(jQuery, window.wp && window.wp.customize, window.webrevCustomizerMenuItemSettings));