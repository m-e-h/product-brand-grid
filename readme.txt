=== Product Brand Grid ===
Contributors: martyhelmick
Tags: woocommerce, brands, grid, block
Requires at least: 6.8
Tested up to: 7.1
Requires PHP: 7.4
Requires Plugins: woocommerce
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display WooCommerce product brands in a responsive grid with optional titles, descriptions, images, and archive links.

== Description ==

Add Product Brand Grid in the block editor or Site Editor. Choose which brand fields to display, enable archive links, and select one to six columns. The grid adapts to smaller screens and inherits your theme's typography and colors.

Brand data updates automatically when edited in Products > Brands. Includes brands without products, sorted by name. No frontend JavaScript is required.

Requires WooCommerce 9.6 or later with Brands enabled.

== Installation ==

1. Upload the product-brand-grid folder to /wp-content/plugins/.
2. Activate WooCommerce and Product Brand Grid.
3. Add brands under Products > Brands.
4. Insert Product Brand Grid and adjust its sidebar settings.

The included build files are ready to use. Developers should run npm ci and npm run build after editing source files.

== Frequently Asked Questions ==

= Where do I change the logos and descriptions? =

Edit the brand under Products > Brands. Changes appear without resaving the page containing the grid.

= Does this replace the old prototype block? =

No. This block is registered as meh/product-brand-grid. The old prototype remains separate.

= What happens when all display options are disabled? =

The editor explains how to enable content. The storefront renders nothing.

== Changelog ==

= 1.0.0 =
* Add independent title, description, thumbnail, and archive link controls.
* Add one to six responsive columns and native alignment/spacing support.
* Add live editor data, dynamic rendering, accessible links, and integration checks.
