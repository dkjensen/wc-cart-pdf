import * as pdfjs from 'pdfjs-dist';

( function () {
	const settings = window.wc_cart_pdf_settings || {};

	const getPDFPreview = ( formData = null ) => {
		// Perform POST request to get the PDF preview.
		const xhr = new XMLHttpRequest();
		xhr.open( 'POST', settings.ajax_url );
		xhr.setRequestHeader(
			'Content-Type',
			'application/x-www-form-urlencoded'
		);

		// Add nonce
		const data = new URLSearchParams();
		data.append( 'action', 'wc_cart_pdf_preview' );
		data.append( 'security', settings.security );

		// Add settings
		data.append(
			'settings',
			JSON.stringify(
				Array.from(formData.entries()).reduce((json, [key, value]) => {
					json[key] = value;
					return json;
				}, {} )
			)
		);

		// Send request
		xhr.send( data );

		// Handle response
		xhr.onload = async () => {
			if ( xhr.status === 200 ) {
				const response = JSON.parse( xhr.responseText );

				if ( response.success ) {
					pdfjs.GlobalWorkerOptions.workerSrc = settings.worker;

					// Base64 decode response.
					const decoded = atob( response.data );

					// Create a blob from the decoded data.
					const pdf = pdfjs.getDocument( { data: decoded } );
					pdf.promise
						.then( ( pdf ) => {
							return pdf.getPage( 1 );
						} )
						.then( ( page ) => {
							const scale = 1.5;
							const viewport = page.getViewport( { scale } );

							// Prepare canvas using PDF page dimensions.
							const canvas = document.getElementById(
								'wc-cart-pdf-preview'
							);
							const context = canvas.getContext( '2d' );
							canvas.height = viewport.height;
							canvas.width = viewport.width;

							// Render PDF page into canvas context.
							const renderContext = {
								canvasContext: context,
								viewport,
							};
							page.render( renderContext );
						} );

					// Update the preview.
					// document.getElementById( 'wc-cart-pdf-preview' ).src =
					// 	response.data;
				}
			}
		};

		xhr.onerror = () => {
			console.error( 'Error fetching PDF preview' );
		};

		xhr.onabort = () => {
			console.error( 'Request aborted' );
		};

		xhr.ontimeout = () => {
			console.error( 'Request timed out' );
		};

		xhr.onprogress = () => {
			console.log( 'Request in progress' );
		};
	};

	// Get the PDF preview when the settings change.
	document.addEventListener( 'DOMContentLoaded', () => {
		getPDFPreview();
	} );

	const form = document.getElementById('mainform');

	// When form field changes, get the PDF preview.
	form.addEventListener('change', () => {
		const formData = new FormData(form);

		getPDFPreview( formData );
	});
} )();
