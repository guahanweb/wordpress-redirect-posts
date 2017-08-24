(function ($) {
    $(document).ready(function () {
        var copyLink = document.getElementById('gw-redirectposts-social-copy');
        console.log(copyLink);
        if (!!copyLink) {
            console.log('here');
            var linkText = document.getElementById('gw-redirectposts-social-link');
            copyLink.addEventListener('click', function (e) {
                e.preventDefault();
                linkText.select();
                try {
                    document.execCommand('copy');
                } catch (err) {
                    console.warn('Browser does not support document.execCopy()');
                }
            });
        }
    });
})(jQuery);
