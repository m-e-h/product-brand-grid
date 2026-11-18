# Product Brand Grid

A dynamic WordPress block for WooCommerce product brands. Insert **Product Brand Grid** in the block editor or Site Editor, then use its sidebar controls to choose:

- Brand title, description, and thumbnail independently.
- Links to brand archives (off by default).
- One to six columns (three by default).

Brands are sorted alphabetically and include brands without products. Manage names, descriptions, and images under **Products → Brands**. Changes appear on the storefront without resaving pages. If the title and thumbnail are hidden, linked descriptions get a **View brand** link. Brands with no content for the selected fields are omitted. A completely empty grid produces no storefront markup.

The grid uses at most two columns at viewport widths up to 781px and one column up to 480px. A one-column setting stays one column. Wide/full alignment and native margin/padding controls are supported where the theme supports them. Styling inherits the theme's type and colors.

## Requirements

- WordPress 6.8 or later.
- PHP 7.4 or later.
- WooCommerce 9.6 or later with Brands enabled (`product_brand`).

Verified locally with WordPress 7.1.5 and WooCommerce 11.3.0. The `product-brand-grid-OLD` plugin is independent; this plugin does not modify it or migrate existing old blocks.

## Development

```sh
npm ci
npm run build
npm run start
```

The `build/` files are included and required at runtime. Rebuild after changing block source or `render.php`. Do not edit generated build files directly.

```sh
npm run lint:js
npm run lint:css
phpcs
wp eval-file tests/integration.php
npm run plugin-zip
```

PHPCS requires the WordPress coding standards. The integration test requires a **local/development** WordPress site with this plugin and WooCommerce active. It creates temporary brand/image fixtures and removes them in `finally`. On Local, run WP-CLI and PHP tools through the site's direnv environment, for example:

```sh
direnv exec /absolute/path/to/site/app wp --path=/absolute/path/to/site/app/public eval-file /absolute/path/to/product-brand-grid/tests/integration.php
```

## Implementation

The block keeps the scaffold's `meh/product-brand-grid` name and API version 3. Registration uses the WordPress 6.8 metadata collection API; `block.json` defines attributes, styles, editor script, and the PHP renderer. `save()` returns `null`, so no stale term data is stored in post content. No frontend JavaScript is loaded.

The editor uses `useEntityRecords` from WordPress core data, including automatic pagination for all brands. A read-only `product_brand_grid` field on the existing brand REST endpoint supplies sanitized description and attachment markup. Both the editor and PHP renderer use the same content helper. No new write endpoints, options, tables, scheduled jobs, or order data are introduced. The plugin declares HPOS compatibility because it does not access order storage.

Brand thumbnails use WooCommerce's `thumbnail_id` term metadata and WordPress responsive attachment markup. The renderer batches attachment cache priming, escapes titles/URLs, sanitizes descriptions, and uses `get_block_wrapper_attributes()` for native block supports. Archive links combine the title and thumbnail into one keyboard stop. Image-only links retain an accessible brand name.

References: [WordPress block metadata](https://developer.wordpress.org/block-editor/reference-guides/block-api/block-metadata/), [dynamic blocks](https://developer.wordpress.org/block-editor/how-to-guides/block-tutorial/creating-dynamic-blocks/), [core data](https://developer.wordpress.org/block-editor/reference-guides/packages/packages-core-data/).
