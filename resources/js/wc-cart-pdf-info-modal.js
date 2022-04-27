import MicroModal from 'micromodal';

(function($) {
	const $cartPDFButton = $('.cart-pdf-button, [href*="cart-pdf=1"');

	$cartPDFButton.on('click', function(e) {
		e.preventDefault();

		MicroModal.show('wc-cart-pdf-info-modal');
	});
})(jQuery);
