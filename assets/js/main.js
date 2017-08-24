(function ($) {
    function newTab(el) {
        $el = $(el);
        $el.attr('href', $el.attr('href').replace(/#new_tab/, ''))
            .attr('target', '_blank');
    }

    if (typeof $.fn.on == 'function') {
        $(document).on('click', 'a[href$="#new_tab"]', function (e) {
            newTab(this);
        });
    }

    $(function () {
        $('a[href$="#new_tab"]').each(function (i, el) {
            newTab(el);
        });
    });
})(window.jQueryWP || window.jQuery);
