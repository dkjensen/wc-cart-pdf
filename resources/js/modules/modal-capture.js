import Cookies from 'js-cookie';

const modalCapture = () => {
	const modal = document.getElementById('wc-cart-pdf-modal');
	const modalForm = modal?.querySelector('form');
	const modalFormAction = modalForm?.getAttribute('action');
	const modalFormErrors = modal?.querySelector(
		'#wc-cart-pdf-capture-form-errors'
	);

	if (!modal || !modalForm || !modalFormAction || !modalFormErrors) {
		console.error('Cart PDF for WooCommerce: Modal capture elements not found.'); // eslint-disable-line no-console
		return;
	}

	const button = document.querySelector('.cart-pdf-button');
	let loading = false;

	function listenForModalClose(e) {
		if (
			e.target === modal ||
			e.target === modal.querySelector('.wc-cart-pdf-modal-close')
		) {
			modal.close();

			// Remove event listener after modal is closed.
			modal.removeEventListener('click', listenForModalClose);
		}
	}

	function listenForModalOpen(e) {
		e.preventDefault();

		modal.showModal();

		// Set focus on first form element.
		modalForm.querySelector('input').focus();

		// Close modal when clicking outside of it.
		modal.addEventListener('click', listenForModalClose);
	}

	function listenForButton() {
		const count = 0;
		const interval = setInterval(() => {
			if (document.querySelector('.cart-pdf-button')) {
				clearInterval(interval);
				document
					.querySelector('.cart-pdf-button')
					.addEventListener('click', listenForModalOpen);
			}

			// Abort after 10 seconds.
			if (count >= 40) {
				clearInterval(interval);
			}
		}, 250);
	}

	// Listen for button click.
	button?.addEventListener('click', listenForModalOpen) || listenForButton(); // eslint-disable-line no-unused-expressions

	modalForm.addEventListener('submit', function (e) {
		e.preventDefault();

		if (loading) {
			return;
		}

		loading = true;

		const formData = new FormData(modalForm);
		const data = {};

		for (const [key, value] of formData.entries()) {
			data[key] = value;
		}

		Cookies.set('wc-cart-pdf-customer', JSON.stringify(data));

		data.nonce = cartpdf.nonce;

		fetch(`${cartpdf.ajax_url}?action=wc_cart_pdf_modal_form_save`, {
			method: 'POST',
			body: new URLSearchParams(data).toString(),
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded',
			},
		})
			.then((response) => response.json())
			.then((response) => {
				if (!response.success) {
					modalFormErrors.textContent =
						response?.data || 'An error occurred.';
				} else {
					const pdf = new URL(modalFormAction);

					if (data.email_copy && data.email_copy === '1') {
						pdf.searchParams.set('email_copy', '1');
					}

					window.location.href = pdf.toString();
					modal.close();
				}
			})
			.catch((error) => {
				modalFormErrors.textContent =
					error?.responseJSON?.data || 'An error occurred.';
			})
			.finally(() => {
				loading = false;
			});
	});
};

export default modalCapture;
