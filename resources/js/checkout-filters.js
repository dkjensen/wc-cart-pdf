/**
 * External dependencies
 */
import { registerCheckoutFilters } from '@woocommerce/blocks-checkout';

registerCheckoutFilters('wc-cart-pdf', {
	additionalCartCheckoutInnerBlockTypes: (value) => {
		value.push('wc-cart-button/cart-pdf-button');

		return value;
	},
});
