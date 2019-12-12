/**
 * External dependencies
 */
import { isEmpty, pick } from 'lodash';

/**
 * WordPress dependencies
 */
import { useState } from '@wordpress/element';
import { BaseControl, Button, ButtonGroup, ColorIndicator, ColorPalette, __experimentalGradientPicker as GradientPicker } from '@wordpress/components';
import { sprintf, __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { getColorObjectByColorValue } from '../colors';
import { __experimentalGetGradientObjectByGradientValue } from '../gradients';

// translators: first %s: the color name or value (e.g. red or #ff0000)
const colorIndicatorAriaLabel = __( '(Color: %s' );

// translators: first %s: the gradient name or value (e.g. red to green or linear-gradient(135deg,rgba(6,147,227,1) 0%,rgb(155,81,224) 100%)
const gradientIndicatorAriaLabel = __( '(Gradient: %s' );

const relevantSettings = [ 'colors', 'disableCustomColors', 'gradients', 'disableCustomGradients' ];

function VisualLabel( { colors, gradients, label, currentTab, colorValue, gradientValue } ) {
	let value, ariaLabel;
	if ( currentTab === 'color' ) {
		if ( colorValue ) {
			value = colorValue;
			const colorObject = getColorObjectByColorValue( colors, value );
			const colorName = colorObject && colorObject.name;
			ariaLabel = sprintf( colorIndicatorAriaLabel, colorName || value );
		}
	} else if ( currentTab === 'gradient' && gradientValue ) {
		value = gradientValue;
		const gradientObject = __experimentalGetGradientObjectByGradientValue( gradients, value );
		const gradientName = gradientObject && gradientObject.name;
		ariaLabel = sprintf( gradientIndicatorAriaLabel, gradientName || value );
	}

	return (
		<>
			{ label }
			{ !! value && (
				<ColorIndicator
					colorValue={ value }
					aria-label={ ariaLabel }
				/>
			) }
		</>
	);
}

export default function ColorGradientControl( {
	label,
	onColorChange,
	onGradientChange,
	colorValue,
	gradientValue,
	...otherProps
} ) {
	const {
		colors,
		gradients,
		disableCustomColors,
		disableCustomGradients,
	} = useSelect(
		( select ) => {
			const settings = select( 'core/block-editor' ).getSettings();
			return {
				...pick( settings, relevantSettings ),
				...pick( otherProps, relevantSettings ),
			};
		},
		[
			otherProps.colors,
			otherProps.disableCustomColors,
			otherProps.gradients,
			otherProps.disableCustomGradients,
		]
	);

	const canChooseAColor = onColorChange && ( ! isEmpty( colors ) || ! disableCustomColors );
	const canChooseAGradient = onGradientChange && ( ! isEmpty( gradients ) || ! disableCustomGradients );
	const [ currentTab, setCurrentTab ] = useState( canChooseAColor ? 'color' : ( !! canChooseAGradient && 'gradient' ) );

	if ( ! canChooseAColor && ! canChooseAGradient ) {
		return null;
	}
	return (
		<BaseControl
			className="block-editor-color-palette-control"
		>
			<BaseControl.VisualLabel>
				<VisualLabel
					currentTab={ currentTab }
					label={ label }
					colorValue={ colorValue }
					gradientValue={ gradientValue }
				/>
			</BaseControl.VisualLabel>
			{ canChooseAColor && canChooseAGradient && (
				<ButtonGroup>
					<Button
						isLarge
						isPrimary={ currentTab === 'color' }
						onClick={ () => ( setCurrentTab( 'color' ) ) }
					>
						{ __( 'Solid Color' ) }
					</Button>
					<Button
						isLarge
						isPrimary={ currentTab === 'gradient' }
						onClick={ () => ( setCurrentTab( 'gradient' ) ) }
					>
						{ __( 'Gradient' ) }
					</Button>
				</ButtonGroup>
			) }
			{ currentTab === 'color' && (
				<ColorPalette
					className="block-editor-color-palette-control__color-palette"
					value={ colorValue }
					onChange={ onColorChange }
					{ ... { colors, disableCustomColors } }
				/>
			) }
			{ currentTab === 'gradient' && (
				<GradientPicker
					value={ gradientValue }
					onChange={ onGradientChange }
					{ ... { gradients, disableCustomGradients } }
				/>
			) }
		</BaseControl>
	);
}
