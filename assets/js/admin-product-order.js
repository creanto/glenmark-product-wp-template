(function ($) {
    'use strict';

    $(function () {
        var $tbody = $('#the-list');

        if (!$tbody.length || typeof wrProductOrder === 'undefined') {
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

                $.post(wrProductOrder.ajaxUrl, {
                    action: 'wr_product_reorder',
                    nonce: wrProductOrder.nonce,
                    post_ids: postIds
                }).done(function () {
                    $tbody.find('.wr-product-order-handle').each(function (index) {
                        $(this).text(index);
                    });
                });
            }
        });

        $tbody.find('tr').css('cursor', 'move');
    });
})(jQuery);
