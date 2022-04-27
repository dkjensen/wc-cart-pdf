import Cookies from 'js-cookie';

(function($) {
	const $form = $('form[name="checkout"]');

	const captureFields = cartpdf.capture_fields || [];

	$form.on('change', ':input', function(e) {
		e.preventDefault();

		const formData = new FormData(
			document.querySelectorAll('form[name="checkout"]')[0]
		);

		const object = {};
		formData.forEach(function(value, key) {
			if (captureFields.includes(key)) {
				object[key] = value;
			}
		});

		const json = JSON.stringify(object);

		Cookies.set('wc-cart-pdf-customer', json);
	});
})(jQuery);
