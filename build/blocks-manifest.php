<?php
// This file is generated. Do not modify it manually.
return array(
	'product-brand-grid' => array(
		'$schema' => 'https://schemas.wp.org/trunk/block.json',
		'apiVersion' => 3,
		'name' => 'meh/product-brand-grid',
		'version' => '1.0.0',
		'title' => 'Product Brand Grid',
		'category' => 'widgets',
		'icon' => 'grid-view',
		'description' => 'Display WooCommerce product brands in a responsive grid.',
		'keywords' => array(
			'woocommerce',
			'brands',
			'logos'
		),
		'attributes' => array(
			'showTitle' => array(
				'type' => 'boolean',
				'default' => false
			),
			'showThumbnail' => array(
				'type' => 'boolean',
				'default' => true
			),
			'linkToArchive' => array(
				'type' => 'boolean',
				'default' => true
			),
			'columns' => array(
				'type' => 'integer',
				'default' => 3
			)
		),
		'supports' => array(
			'html' => false,
			'align' => array(
				'wide',
				'full'
			),
			'layout' => array(
				'default' => array(
					'type' => 'grid',
					'columnCount' => 3
				),
				'allowEditing' => false
			),
			'spacing' => array(
				'margin' => true,
				'padding' => true,
				'blockGap' => array(
					'horizontal',
					'vertical'
				),
				'__experimentalDefaultControls' => array(
					'blockGap' => true
				)
			),
			'__experimentalStyle' => array(
				'spacing' => array(
					'blockGap' => '1.5rem'
				)
			)
		),
		'textdomain' => 'product-brand-grid',
		'editorScript' => 'file:./index.js',
		'editorStyle' => 'file:./index.css',
		'style' => 'file:./style-index.css',
		'render' => 'file:./render.php'
	)
);
