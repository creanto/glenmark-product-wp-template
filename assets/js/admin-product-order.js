(function ($) {
    'use strict';

    $(function () {
        var $tbody = $('#the-list');

        if (!$tbody.length || typeof glnProductOrder === 'undefined') {
            return;
        }

        $tbody.sortable({
            items: 'tr',
            axis: 'y',
            cursor: 'move',
            helper: function (event, row) {
                row.children().each(function () {
                    $(this).width($(this).width());
                });
                return row;
            },
            update: function () {
                var postIds = $tbody.find('tr').map(function () {
                    return $(this).attr('id').replace('post-', '');
                }).get();

                $.post(glnProductOrder.ajaxUrl, {
                    action: 'gln_product_reorder',
                    nonce: glnProductOrder.nonce,
                    post_ids: postIds
                }).done(function () {
                    $tbody.find('.gln-product-order-handle').each(function (index) {
                        $(this).text(index);
                    });
                });
            }
        });

        $tbody.find('tr').css('cursor', 'move');
    });
})(jQuery);
