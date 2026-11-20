<?php
/**
 * Render current WooCommerce brands, including brands without products.
 *
 * @var array $attributes Validated block attributes.
 * @package ProductBrandGrid
 * @since 1.0.0
 */

defined( 'ABSPATH' ) || exit;

$show_title      = ! empty( $attributes['showTitle'] );
$show_thumbnail  = ! empty( $attributes['showThumbnail'] );
$link_to_archive = ! empty( $attributes['linkToArchive'] );
$columns         = max( 1, min( 6, (int) ( $attributes['columns'] ?? 3 ) ) );

if ( ! taxonomy_exists( 'product_brand' ) || ! ( $show_title || $show_thumbnail ) ) {
	return;
}

$brands = get_terms(
	array(
		'taxonomy'               => 'product_brand',
		'hide_empty'             => false,
		'orderby'                => 'name',
		'order'                  => 'ASC',
		'update_term_meta_cache' => $show_thumbnail,
	)
);

if ( is_wp_error( $brands ) || ! $brands ) {
	return;
}

if ( $show_thumbnail ) {
	$thumbnail_ids = array();
	foreach ( $brands as $brand ) {
		$thumbnail_ids[] = absint( get_term_meta( $brand->term_id, 'thumbnail_id', true ) );
	}
	$thumbnail_ids = array_unique( array_filter( $thumbnail_ids ) );
	if ( ! empty( $thumbnail_ids ) ) {
		// Prime caches to reduce future queries.
		_prime_post_caches( $thumbnail_ids, false, true );
	}
}

$items = array();
foreach ( $brands as $brand ) {
	$item = product_brand_grid_get_content( $brand->term_id, false, $show_thumbnail );
	if ( ! $show_title && ! $item['thumbnail'] ) {
		continue;
	}

	$url     = $link_to_archive ? get_term_link( $brand ) : '';
	$items[] = array_merge(
		$item,
		array(
			'title' => $brand->name,
			'url'   => is_wp_error( $url ) ? '' : $url,
		)
	);
}

if ( ! $items ) {
	return;
}

$wrapper = array( 'class' => 'product-brand-grid has-' . $columns . '-columns' );
?>
<ul <?php echo get_block_wrapper_attributes( $wrapper ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped by WordPress. ?> role="list">
	<?php foreach ( $items as $item ) : ?>
		<li class="product-brand-grid-item">
			<?php if ( $item['url'] ) : ?>
				<a class="product-brand-grid-identity" href="<?php echo esc_url( $item['url'] ); ?>">
			<?php else : ?>
				<div class="product-brand-grid-identity">
			<?php endif; ?>
				<?php if ( $item['thumbnail'] ) : ?>
					<div class="product-brand-grid-thumbnail"<?php echo $show_title ? ' aria-hidden="true"' : ''; ?>>
						<?php echo wp_kses_post( $item['thumbnail'] ); ?>
					</div>
				<?php endif; ?>
				<?php if ( $show_title ) : ?>
					<p class="product-brand-grid-title"><?php echo esc_html( $item['title'] ); ?></p>
				<?php endif; ?>
			<?php if ( $item['url'] ) : ?>
				</a>
			<?php else : ?>
				</div>
			<?php endif; ?>
		</li>
	<?php endforeach; ?>
</ul>
