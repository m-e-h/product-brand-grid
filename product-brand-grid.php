<?php
/**
 * Plugin Name:       Product Brand Grid
 * Description:       Display WooCommerce product brands in a responsive grid.
 * Version:           1.0.0
 * Requires at least: 6.8
 * Requires PHP:      7.4
 * Author:            Marty Helmick
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       product-brand-grid
 * Requires Plugins: woocommerce
 * WC requires at least: 9.6
 * WC tested up to:   11.3
 *
 * @package ProductBrandGrid
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

require_once __DIR__ . '/includes/brands.php';

/**
 * Register the block and its assets from the build metadata.
 *
 * @since 1.0.0
 * @return void
 */
function product_brand_grid_block_init(): void {
	if ( ! class_exists( 'WooCommerce' ) ) {
		return;
	}

	wp_register_block_types_from_metadata_collection( __DIR__ . '/build', __DIR__ . '/build/blocks-manifest.php' );
	wp_set_script_translations( 'meh-product-brand-grid-editor-script', 'product-brand-grid', __DIR__ . '/languages' );
}
add_action( 'init', 'product_brand_grid_block_init' );

/**
 * Declare compatibility with WooCommerce order storage.
 *
 * This plugin only reads brand terms and never accesses order storage.
 *
 * @since 1.0.0
 * @return void
 */
function product_brand_grid_declare_compatibility(): void {
	if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
		\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
	}
}
add_action( 'before_woocommerce_init', 'product_brand_grid_declare_compatibility' );
