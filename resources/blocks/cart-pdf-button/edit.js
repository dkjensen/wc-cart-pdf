import classnames from 'classnames';
import React from 'react';

import { __ } from '@wordpress/i18n';
import {
	useBlockProps,
	RichText,
	__experimentalUseBorderProps as useBorderProps,
	__experimentalUseColorProps as useColorProps,
	__experimentalGetSpacingClassesAndStyles as useSpacingProps,
} from '@wordpress/blockEditor';

export default function Edit( { attributes, setAttributes } ) {
	const { text } = attributes;

	const borderProps = useBorderProps( attributes );
	const colorProps = useColorProps( attributes );
	const spacingProps = useSpacingProps( attributes );

	const classes = classnames(
		'cart-pdf-button',
		'button',
		'wp-element-button',
		colorProps.className,
		borderProps.className
	);

	return (
		<div { ...useBlockProps() }>
			<RichText
				tagName="a"
				aria-label={ __( 'Button text', 'wc-cart-pdf' ) }
				placeholder={ __( 'Add text…', 'wc-cart-pdf' ) }
				value={ text }
				onChange={ ( value ) => setAttributes( { text: value } ) }
				withoutInteractiveFormatting
				className={ classes }
				style={ {
					...borderProps.style,
					...colorProps.style,
					...spacingProps.style,
				} }
				identifier="text"
				rel="nofollow noopener"
			/>
		</div>
	);
}
