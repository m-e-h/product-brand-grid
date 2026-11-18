<?php
/**
 * Brand content shared by the editor and server renderer.
 *
 * @package ProductBrandGrid
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Prepare safe, formatted brand content.
 *
 * @since 1.0.0
 * @param int  $term_id          Brand term ID.
 * @param bool $with_description Whether to format the description.
 * @param bool $with_thumbnail   Whether to render the image.
 * @return array Description and WordPress attachment markup.
 */
function product_brand_grid_get_content( int $term_id, bool $with_description = true, bool $with_thumbnail = true ): array {
	$brand = get_term( $term_id, 'product_brand' );
	if ( ! $brand instanceof WP_Term ) {
		return array(
			'description' => '',
			'thumbnail'   => '',
		);
	}

	return array(
		'description' => $with_description ? wp_kses_post( term_description( $term_id ) ) : '',
		'thumbnail'   => $with_thumbnail ? wp_kses_post(
			wp_get_attachment_image(
				absint( get_term_meta( $term_id, 'thumbnail_id', true ) ),
				'medium',
				false,
				array( 'alt' => $brand->name )
			)
		) : '',
	);
}

/**
 * Expose formatted public content through the existing brand REST controller.
 *
 * No update callback is provided: this computed field is read-only.
 *
 * @since 1.0.0
 * @return void
 */
function product_brand_grid_register_rest_field(): void {
	if ( ! class_exists( 'WooCommerce' ) || ! taxonomy_exists( 'product_brand' ) ) {
		return;
	}

	register_rest_field(
		'product_brand',
		'product_brand_grid',
		array(
			'get_callback' => 'product_brand_grid_get_rest_content',
			'schema'       => array(
				'description' => __( 'Formatted brand content for the product brand grid.', 'product-brand-grid' ),
				'type'        => 'object',
				'context'     => array( 'view', 'edit' ),
				'readonly'    => true,
				'properties'  => array(
					'description' => array( 'type' => 'string' ),
					'thumbnail'   => array( 'type' => 'string' ),
				),
			),
		)
	);
}
add_action( 'rest_api_init', 'product_brand_grid_register_rest_field' );

/**
 * Read computed content for a REST term response.
 *
 * @since 1.0.0
 * @param array $term Prepared REST term data.
 * @return array Public description and thumbnail markup.
 */
function product_brand_grid_get_rest_content( array $term ): array {
	return product_brand_grid_get_content( (int) $term['id'] );
}
