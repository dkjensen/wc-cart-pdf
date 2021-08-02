import Cookies from 'js-cookie';

(function ($) {
    const $form = $('form[name="checkout"]');

    const captureFields = cartpdf.capture_fields || [];

    $form.on('change', ':input', function (e) {
        e.preventDefault();

        let formData = new FormData(document.querySelectorAll('form[name="checkout"]')[0]);

        var object = {};
        formData.forEach(function(value, key) {
            if (captureFields.includes(key)) {
                object[ key ] = value;
            }
        });

        var json = JSON.stringify(object);

        Cookies.set('wc-cart-pdf-customer', json);
    });
})(jQuery);
