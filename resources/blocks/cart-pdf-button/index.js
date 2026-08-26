import { registerBlockType } from '@wordpress/blocks';

import Edit from './edit';
import save from './save';
import metadata from './block.json';

registerBlockType( metadata.name, {
	edit: Edit,
	save,
} );

/**
 * Register the block with the cart and checkout area.
 */
document.addEventListener( 'DOMContentLoaded', function () {
	const { registerCheckoutFilters } = window.wc.blocksCheckout;

	registerCheckoutFilters( 'wc-cart-pdf', {
		additionalCartCheckoutInnerBlockTypes: ( value ) => {
			value.push( 'wc-cart-button/cart-pdf-button' );

			return value;
		},
	} );
} );
