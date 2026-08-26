import classnames from 'classnames';
import React from 'react';

import {
	useBlockProps,
	RichText,
	__experimentalGetBorderClassesAndStyles as getBorderClassesAndStyles,
	__experimentalGetColorClassesAndStyles as getColorClassesAndStyles,
	__experimentalGetSpacingClassesAndStyles as getSpacingClassesAndStyles,
} from '@wordpress/blockEditor';

export default function Save( { attributes } ) {
	const { text } = attributes;

	const borderProps = getBorderClassesAndStyles( attributes );
	const colorProps = getColorClassesAndStyles( attributes );
	const spacingProps = getSpacingClassesAndStyles( attributes );

	const classes = classnames(
		'cart-pdf-button',
		'button',
		'wp-element-button',
		colorProps.className,
		borderProps.className
	);

	return (
		<div { ...useBlockProps.save() }>
			<RichText.Content
				tagName="a"
				value={ text }
				className={ classes }
				style={ {
					...borderProps.style,
					...colorProps.style,
					...spacingProps.style,
				} }
				target="_blank"
				rel="nofollow noopener"
			/>
		</div>
	);
}
