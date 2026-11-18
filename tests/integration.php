<?php
/**
 * Integration checks against a running local WordPress + WooCommerce site.
 *
 * Run: wp eval-file tests/integration.php
 * Creates isolated fixtures and removes them in finally, including on failure.
 *
 * @package ProductBrandGrid
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	exit;
}

if ( ! in_array( wp_get_environment_type(), array( 'local', 'development' ), true ) ) {
	WP_CLI::error( 'Run integration checks on a local or development site.' );
}

$check = static function ( bool $condition, string $message ): void {
	if ( ! $condition ) {
		throw new RuntimeException( $message );
	}
};
$render = static function ( array $attributes = array() ): string {
	return render_block( array( 'blockName' => 'meh/product-brand-grid', 'attrs' => $attributes, 'innerBlocks' => array(), 'innerHTML' => '', 'innerContent' => array() ) );
};

$term_ids     = array();
$attachment_id = 0;
$query_filter = null;
$unsafe_filter = null;
$original_user = get_current_user_id();
$contains = static function ( string $haystack, string $needle ): bool {
	return false !== strpos( $haystack, $needle );
};

try {
	$check( null !== WP_Block_Type_Registry::get_instance()->get_registered( 'meh/product-brand-grid' ), 'Block must be registered.' );
	foreach ( array( 'Alpha', 'Beta' ) as $name ) {
		$term = wp_insert_term( 'Grid test ' . $name . ' ' . wp_generate_uuid4(), 'product_brand' );
		$check( ! is_wp_error( $term ), 'Fixture brand should be created.' );
		$term_ids[] = (int) $term['term_id'];
	}
	wp_update_term( $term_ids[0], 'product_brand', array( 'description' => 'A brand description.' ) );
	$upload = wp_upload_bits( 'brand-grid-test-' . wp_generate_uuid4() . '.png', null, base64_decode( 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aS1sAAAAASUVORK5CYII=' ) );
	$check( empty( $upload['error'] ), 'Fixture image should be created.' );
	$attachment_id = wp_insert_attachment( array( 'post_mime_type' => 'image/png', 'post_title' => 'Grid test logo' ), $upload['file'] );
	update_post_meta( $attachment_id, '_wp_attachment_metadata', array( 'width' => 1, 'height' => 1, 'file' => _wp_relative_upload_path( $upload['file'] ) ) );
	update_term_meta( $term_ids[0], 'thumbnail_id', $attachment_id );
	$query_term_ids = $term_ids;
	$query_filter = static function ( array $args, array $taxonomies ) use ( &$query_term_ids ): array {
		if ( in_array( 'product_brand', $taxonomies, true ) ) {
			$args['include'] = $query_term_ids;
		}
		return $args;
	};
	add_filter( 'get_terms_args', $query_filter, 10, 2 );

	for ( $mask = 0; $mask < 16; ++$mask ) {
		$title = (bool) ( $mask & 1 );
		$description = (bool) ( $mask & 2 );
		$thumbnail = (bool) ( $mask & 4 );
		$link = (bool) ( $mask & 8 );
		$html = $render( array( 'showTitle' => $title, 'showDescription' => $description, 'showThumbnail' => $thumbnail, 'linkToArchive' => $link ) );
		$expected_items = $title ? 2 : ( ( $description || $thumbnail ) ? 1 : 0 );
		$check( substr_count( $html, '<li ' ) === $expected_items, "Visible brands for mask $mask." );
		$check( substr_count( $html, 'class="product-brand-grid-title"' ) === ( $title ? 2 : 0 ), "Title setting for mask $mask." );
		$check( substr_count( $html, 'class="product-brand-grid-description"' ) === ( $description ? 1 : 0 ), "Description setting for mask $mask." );
		$check( substr_count( $html, '<img ' ) === ( $thumbnail ? 1 : 0 ), "Thumbnail setting for mask $mask." );
		$check( substr_count( $html, '<a ' ) === ( $link ? $expected_items : 0 ), "One archive link per visible brand for mask $mask." );
		if ( 0 === $expected_items ) {
			$check( '' === $html, 'Hidden grids should have no empty markup.' );
		}
	}
	foreach ( array( -5 => 1, 1 => 1, 2 => 2, 6 => 6, 99 => 6 ) as $input => $expected ) {
		$check( $contains( $render( array( 'columns' => $input ) ), 'has-' . $expected . '-columns' ), 'Columns must be bounded to 1–6.' );
	}
	$check( $contains( $render(), 'has-3-columns' ), 'Default should be three columns.' );
	$check( $contains( $render( array( 'align' => 'wide', 'style' => array( 'spacing' => array( 'padding' => array( 'top' => '24px' ) ) ) ) ), 'padding-top:24px' ), 'Native block spacing should reach the wrapper.' );
	wp_update_term( $term_ids[0], 'product_brand', array( 'name' => 'Grid test updated & escaped' ) );
	$check( $contains( $render(), 'Grid test updated &amp; escaped' ), 'Current term data must be escaped and rendered without resaving a post.' );
	$unsafe_filter = static function ( string $description ): string {
		return $description . '<script>alert("unsafe")</script><img src="x" onerror="alert(1)">';
	};
	add_filter( 'term_description', $unsafe_filter );
	$html = $render();
	$check( ! $contains( $html, '<script' ) && ! $contains( $html, 'onerror=' ), 'Description HTML must be sanitized.' );
	remove_filter( 'term_description', $unsafe_filter );

	wp_set_current_user( 0 );
	$taxonomy = get_taxonomy( 'product_brand' );
	$base = $taxonomy->rest_base ?: $taxonomy->name;
	$response = rest_do_request( new WP_REST_Request( 'GET', '/wp/v2/' . $base . '/' . $term_ids[0] ) );
	$data = $response->get_data();
	$check( 200 === $response->get_status(), 'Public brand REST data should load.' );
	$check( $contains( $data['product_brand_grid']['thumbnail'], '<img' ), 'Editor REST response should contain the brand image.' );
	$check( $data['product_brand_grid'] === product_brand_grid_get_content( $term_ids[0] ), 'REST and storefront must share formatted content.' );
	$check( $GLOBALS['wp_rest_additional_fields']['product_brand']['product_brand_grid']['schema']['readonly'], 'Computed REST field must be read-only.' );
	$response = rest_do_request( new WP_REST_Request( 'POST', '/wp/v2/' . $base . '/' . $term_ids[0] ) );
	$check( in_array( $response->get_status(), array( 401, 403 ), true ), 'Anonymous users must not be able to write brand data.' );

	$query_term_ids = array( PHP_INT_MAX );
	$check( '' === $render(), 'No brands should produce no frontend markup.' );
	$query_term_ids = $term_ids;
	$check( array( 'description' => '', 'thumbnail' => '' ) === product_brand_grid_get_content( PHP_INT_MAX ), 'Deleted brands should return empty content.' );
	$taxonomy = $GLOBALS['wp_taxonomies']['product_brand'];
	unset( $GLOBALS['wp_taxonomies']['product_brand'] );
	try {
		$check( '' === $render(), 'A missing brand taxonomy must render safely.' );
	} finally {
		$GLOBALS['wp_taxonomies']['product_brand'] = $taxonomy;
	}
	WP_CLI::success( 'All display combinations, column bounds, dynamic data, escaping, empty states, REST parity and permissions passed.' );
} finally {
	if ( $query_filter ) {
		remove_filter( 'get_terms_args', $query_filter, 10 );
	}
	if ( $unsafe_filter ) {
		remove_filter( 'term_description', $unsafe_filter );
	}
	foreach ( $term_ids as $term_id ) {
		wp_delete_term( $term_id, 'product_brand' );
	}
	if ( $attachment_id ) {
		wp_delete_attachment( $attachment_id, true );
	}
	wp_set_current_user( $original_user );
}
