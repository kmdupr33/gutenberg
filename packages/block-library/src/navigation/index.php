<?php
/**
 * Server-side rendering of the `core/navigation` block.
 *
 * @package gutenberg
 */

/**
 * Build an array with CSS classes and inline styles defining the colors
 * which will be applied to the navigation markup in the front-end.
 *
 * @param  array $attributes Navigation block attributes.
 * @return array Colors CSS classes and inline styles.
 */
function build_css_colors( $attributes ) {
	$colors = array(
		'css_classes'   => array(),
		'inline_styles' => '',
	);

	// Text color.
	$has_named_text_color  = array_key_exists( 'textColor', $attributes );
	$has_custom_text_color = array_key_exists( 'customTextColor', $attributes );

	// If has text color.
	if ( $has_custom_text_color || $has_named_text_color ) {
		// Add has-text-color class.
		$colors['css_classes'][] = 'has-text-color';
	}

	if ( $has_named_text_color ) {
		// Add the color class.
		$colors['css_classes'][] = sprintf( 'has-%s-color', $attributes['textColor'] );
	} elseif ( $has_custom_text_color ) {
		// Add the custom color inline style.
		$colors['inline_styles'] = sprintf( 'color: %s;', $attributes['customTextColor'] );
	}
	
	// Background color.
	$has_named_background_color  = array_key_exists( 'backgroundColor', $attributes );
	$has_custom_background_color = array_key_exists( 'customBackgroundColor', $attributes );
	
	// If has background color.
	if ( $has_custom_background_color || $has_named_background_color ) {
		// Add has-background-color class.
		$colors['css_classes'][] = 'has-background-color';
	}
	
	if ( $has_named_background_color ) {
		// Add the background-color class.
		$colors['css_classes'][] = sprintf( 'has-%s-background-color', $attributes['backgroundColor'] );
	} elseif ( $has_custom_background_color ) {
		// Add the custom background-color inline style.
		$colors['inline_styles'] = sprintf( 'background-color: %s;', $attributes['customBackgroundColor'] );
	}

	return $colors;
}

/**
 * Renders the `core/navigation` block on server.
 *
 * @param array $attributes The block attributes.
 * @param array $content The saved content.
 * @param array $block The parsed block.
 *
 * @return string Returns the post content with the legacy widget added.
 */
function render_block_navigation( $attributes, $content, $block ) {
	$colors          = build_css_colors( $attributes );
	$classes         = array_merge(
		$colors['css_classes'],
		array( 'wp-block-navigation' ),
		isset( $attributes['className'] ) ? array( $attributes['className'] ) : array(),
		isset( $attributes['itemsJustification'] ) ? array( 'items-justified-' . $attributes['itemsJustification'] ) : array(),
		isset( $attributes['align'] ) ? array( 'align' . $attributes['align'] ) : array()
	);
	$class_attribute = sprintf( ' class="%s"', esc_attr( implode( ' ', $classes ) ) );
	$style_attribute = $colors['inline_styles'] ? sprintf( ' style="%s"', esc_attr( $colors['inline_styles'] ) ) : '';

	return sprintf(
		'<nav %1$s %2$s>%3$s</nav>',
		$class_attribute,
		$style_attribute,
		build_navigation_html( $block, $colors )
	);
}

/**
 * Walks the inner block structure and returns an HTML list for it.
 *
 * @param array $block  The block.
 * @param array $colors Contains inline styles and CSS classes to apply to navigation item.
 *
 * @return string Returns  an HTML list from innerBlocks.
 */
function build_navigation_html( $block, $colors ) {
	$html            = '';
	$css_classes     = implode( ' ', $colors['css_classes'] );
	$class_attribute = sprintf( ' class="wp-block-navigation-link__content %s"', esc_attr( trim( $css_classes ) ) );
	$style_attribute = $colors['inline_styles'] ? sprintf( ' style="%s"', esc_attr( $colors['inline_styles'] ) ) : '';

	foreach ( (array) $block['innerBlocks'] as $key => $block ) {

		$html .= '<li class="wp-block-navigation-link">' .
			'<a' . $class_attribute . $style_attribute;

		// Start appending HTML attributes to anchor tag.
		if ( isset( $block['attrs']['url'] ) ) {
			$html .= ' href="' . esc_url( $block['attrs']['url'] ) . '"';
		}
		if ( isset( $block['attrs']['title'] ) ) {
			$html .= ' title="' . esc_attr( $block['attrs']['title'] ) . '"';
		}

		if ( isset( $block['attrs']['opensInNewTab'] ) && true === $block['attrs']['opensInNewTab'] ) {
			$html .= ' target="_blank"  ';
		}
		// End appending HTML attributes to anchor tag.

		// Start anchor tag content.
		$html .= '>';
		if ( isset( $block['attrs']['label'] ) ) {
			$html .= esc_html( $block['attrs']['label'] );
		}
		$html .= '</a>';
		// End anchor tag content.

		if ( count( (array) $block['innerBlocks'] ) > 0 ) {
			$html .= build_navigation_html( $block, $colors );
		}

		$html .= '</li>';
	}
	return '<ul>' . $html . '</ul>';
}

/**
 * Register the navigation block.
 *
 * @uses render_block_navigation()
 * @throws WP_Error An WP_Error exception parsing the block definition.
 */
function register_block_core_navigation() {

	register_block_type(
		'core/navigation',
		array(
			'attributes'      => array(
				'className'          => array(
					'type' => 'string',
				),
				'textColor'          => array(
					'type' => 'string',
				),
				'customTextColor'    => array(
					'type' => 'string',
				),
				'backgroundColor'          => array(
					'type' => 'string',
				),
				'customBackgroundColor'    => array(
					'type' => 'string',
				),
				'itemsJustification' => array(
					'type' => 'string',
				),
			),

			'render_callback' => 'render_block_navigation',
		)
	);
}
add_action( 'init', 'register_block_core_navigation' );
