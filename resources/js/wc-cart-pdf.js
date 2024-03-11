import subscribeToCustomerData from './modules/capture-customer';
import modalCapture from './modules/modal-capture';

(function () {
	document.addEventListener('DOMContentLoaded', function () {
		if (cartpdf?.modules?.capture_customer) {
			subscribeToCustomerData();
		}

		if (cartpdf?.modules?.modal_capture) {
			modalCapture();
		}
	});
})();
