import Cookies from 'js-cookie';
import debounce from 'lodash/debounce';

const saveCustomerData = (object) => {
	const captureFields = cartpdf.capture_fields || [];

	try {
		if (!object || typeof object !== 'object') {
			throw new Error('Invalid object');
		}

		// Filter out object keys that are not in the captureFields array.
		for (const key in object) {
			if (!captureFields.includes(key)) {
				delete object[key];
			}
		}

		const json = JSON.stringify(object);

		Cookies.set('wc-cart-pdf-customer', json);
	} catch (error) {
		console.error(error); // eslint-disable-line no-console
	}
};

const subscribeToCustomerData = () => {
	/**
	 * WooCommerce Blocks Checkout
	 */
	if (typeof wp !== 'undefined' && wp && wp.data) {
		const debouncedSubscription = debounce(() => {
			const select = wp.data.select('wc/store/cart');
			const customerData = select.getCustomerData();

			const object = {};

			for (const key in customerData.shippingAddress) {
				object[`shipping_${key}`] = customerData.shippingAddress[key];
			}

			for (const key in customerData.billingAddress) {
				object[`billing_${key}`] = customerData.billingAddress[key];
			}

			saveCustomerData(object);
		}, 250);

		wp.data.subscribe(debouncedSubscription);
	}

	/**
	 * Legacy Checkout
	 */
	const form = document.querySelector('form[name="checkout"]');
	const formElements = form?.querySelectorAll('input, textarea, select');

	formElements?.forEach((element) => {
		element.addEventListener('change', function (e) {
			e.preventDefault();

			const formData = new FormData(form);

			const object = {};
			formData.forEach(function (value, key) {
				object[key] = value;
			});

			saveCustomerData(object);
		});
	});
};

export default subscribeToCustomerData;
