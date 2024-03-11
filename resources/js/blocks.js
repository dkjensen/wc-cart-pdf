import classnames from 'classnames';

import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { createBlock } from '@wordpress/blocks';
import {
	useBlockProps,
	RichText,
	__experimentalUseBorderProps as useBorderProps,
	__experimentalUseColorProps as useColorProps,
	__experimentalGetSpacingClassesAndStyles as useSpacingProps,
	__experimentalGetBorderClassesAndStyles as getBorderClassesAndStyles,
	__experimentalGetColorClassesAndStyles as getColorClassesAndStyles,
	__experimentalGetSpacingClassesAndStyles as getSpacingClassesAndStyles,
} from '@wordpress/blockEditor';
import metadata from './block.json';

const { name } = metadata;

const Edit = ({ attributes, setAttributes }) => {
	const { text } = attributes;

	const borderProps = useBorderProps(attributes);
	const colorProps = useColorProps(attributes);
	const spacingProps = useSpacingProps(attributes);

	const classes = classnames(
		'cart-pdf-button',
		'button',
		'wp-element-button',
		colorProps.className,
		borderProps.className
	);

	return (
		<div {...useBlockProps()}>
			<RichText
				tagName="a"
				aria-label={__('Button text', 'wc-cart-pdf')}
				placeholder={__('Add text…', 'wc-cart-pdf')}
				value={text}
				onChange={(value) => setAttributes({ text: value })}
				withoutInteractiveFormatting
				className={classes}
				style={{
					...borderProps.style,
					...colorProps.style,
					...spacingProps.style,
				}}
				onSplit={() => createBlock('core/paragraph')}
				identifier="text"
				rel="nofollow noopener"
			/>
		</div>
	);
};

const Save = ({ attributes }) => {
	const { text } = attributes;

	const borderProps = getBorderClassesAndStyles(attributes);
	const colorProps = getColorClassesAndStyles(attributes);
	const spacingProps = getSpacingClassesAndStyles(attributes);

	const classes = classnames(
		'cart-pdf-button',
		'button',
		'wp-element-button',
		colorProps.className,
		borderProps.className
	);

	return (
		<div {...useBlockProps.save()}>
			<RichText.Content
				tagName="a"
				value={text}
				className={classes}
				style={{
					...borderProps.style,
					...colorProps.style,
					...spacingProps.style,
				}}
				target="_blank"
				rel="nofollow noopener"
			/>
		</div>
	);
};

registerBlockType('wc-cart-pdf/cart-pdf', {
	name,
	...metadata,
	example: {
		attributes: {
			text: 'Download Cart as PDF',
		},
	},
	edit: Edit,
	save: Save,
});

/**
 * Register the block with the cart and checkout area.
 */
document.addEventListener('DOMContentLoaded', function () {
	const { registerCheckoutFilters } = window.wc.blocksCheckout;

	registerCheckoutFilters('wc-cart-pdf', {
		additionalCartCheckoutInnerBlockTypes: (value) => {
			value.push('wc-cart-button/cart-pdf-button');

			return value;
		},
	});
});
