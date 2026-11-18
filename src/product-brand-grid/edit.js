import { __, sprintf } from '@wordpress/i18n';
import { InspectorControls, useBlockProps } from '@wordpress/block-editor';
import {
	Disabled,
	Notice,
	PanelBody,
	Placeholder,
	RangeControl,
	Spinner,
	ToggleControl,
} from '@wordpress/components';
import { useEntityRecords } from '@wordpress/core-data';
import { RawHTML } from '@wordpress/element';
import './editor.scss';

const brandQuery = {
	per_page: -1,
	hide_empty: false,
	orderby: 'name',
	order: 'asc',
	context: 'view',
	_fields: 'id,name,link,product_brand_grid',
};

/**
 * Preview brands using the native taxonomy entity store.
 *
 * @param {Object}                       props               Block props.
 * @param {Object}                       props.attributes    Saved block attributes.
 * @param {(attributes: Object) => void} props.setAttributes Update block attributes.
 * @return {Element} Block controls and preview.
 */
export default function Edit( { attributes, setAttributes } ) {
	const {
		columns,
		showTitle,
		showDescription,
		showThumbnail,
		linkToArchive,
	} = attributes;
	const blockProps = useBlockProps( {
		className: 'product-brand-grid-editor',
	} );
	const { records, isResolving, hasResolved, status } = useEntityRecords(
		'taxonomy',
		'product_brand',
		brandQuery
	);
	const hasDisplay = showTitle || showDescription || showThumbnail;
	const brands = ( records || [] ).filter(
		( brand ) =>
			showTitle ||
			( showDescription && brand.product_brand_grid?.description ) ||
			( showThumbnail && brand.product_brand_grid?.thumbnail )
	);

	let preview;
	if ( ! hasDisplay ) {
		preview = __(
			'Turn on a display option to show the brand grid.',
			'product-brand-grid'
		);
	} else if ( status === 'ERROR' ) {
		preview = (
			<Notice status="error" isDismissible={ false }>
				{ __(
					'Could not load brands. Check that WooCommerce Brands is enabled, then reload the editor.',
					'product-brand-grid'
				) }
			</Notice>
		);
	} else if ( isResolving || ! hasResolved ) {
		preview = <Spinner />;
	} else if ( ! records ) {
		preview = __(
			'Could not load brands. Check that WooCommerce Brands is enabled, then reload the editor.',
			'product-brand-grid'
		);
	} else if ( ! brands.length ) {
		preview = records.length
			? __(
					'No brands have content for the selected display options.',
					'product-brand-grid'
				)
			: __(
					'No brands yet. Add them under Products → Brands.',
					'product-brand-grid'
				);
	}

	return (
		<>
			<InspectorControls>
				<PanelBody title={ __( 'Layout', 'product-brand-grid' ) }>
					<RangeControl
						label={ __( 'Columns', 'product-brand-grid' ) }
						help={ __(
							'The grid uses fewer columns on smaller screens.',
							'product-brand-grid'
						) }
						value={ columns }
						onChange={ ( value ) =>
							setAttributes( { columns: value } )
						}
						min={ 1 }
						max={ 6 }
						__next40pxDefaultSize
						__nextHasNoMarginBottom
					/>
				</PanelBody>
				<PanelBody title={ __( 'Display', 'product-brand-grid' ) }>
					{ [
						[
							'showTitle',
							__( 'Show title', 'product-brand-grid' ),
						],
						[
							'showDescription',
							__( 'Show description', 'product-brand-grid' ),
						],
						[
							'showThumbnail',
							__( 'Show thumbnail', 'product-brand-grid' ),
						],
						[
							'linkToArchive',
							__( 'Link to brand archive', 'product-brand-grid' ),
						],
					].map( ( [ name, label ] ) => (
						<ToggleControl
							key={ name }
							label={ label }
							checked={ attributes[ name ] }
							onChange={ ( value ) =>
								setAttributes( { [ name ]: value } )
							}
							__nextHasNoMarginBottom
						/>
					) ) }
				</PanelBody>
			</InspectorControls>
			<div { ...blockProps }>
				{ preview ? (
					<Placeholder
						icon="grid-view"
						label={ __(
							'Product Brand Grid',
							'product-brand-grid'
						) }
					>
						{ preview }
					</Placeholder>
				) : (
					<Disabled>
						{ /* Explicit list role preserves list semantics in Safari with list-style: none. */ }
						{ /* eslint-disable-next-line jsx-a11y/no-redundant-roles */ }
						<ul
							className={ `product-brand-grid has-${ columns }-columns` }
							role="list"
						>
							{ brands.map( ( brand ) => {
								const { description, thumbnail } =
									brand.product_brand_grid || {};
								const hasIdentity =
									showTitle || ( showThumbnail && thumbnail );
								const Identity =
									linkToArchive && brand.link ? 'a' : 'div';
								return (
									<li
										key={ brand.id }
										className="product-brand-grid-item"
									>
										{ hasIdentity && (
											<Identity
												className="product-brand-grid-identity"
												href={
													Identity === 'a'
														? brand.link
														: undefined
												}
											>
												{ showThumbnail &&
													thumbnail && (
														<RawHTML
															className="product-brand-grid-thumbnail"
															aria-hidden={
																showTitle ||
																undefined
															}
														>
															{ thumbnail }
														</RawHTML>
													) }
												{ showTitle && (
													<p className="product-brand-grid-title">
														{ brand.name }
													</p>
												) }
											</Identity>
										) }
										{ showDescription && description && (
											<RawHTML className="product-brand-grid-description">
												{ description }
											</RawHTML>
										) }
										{ linkToArchive &&
											brand.link &&
											! hasIdentity && (
												<a
													className="product-brand-grid-link"
													href={ brand.link }
													aria-label={ sprintf(
														/* translators: %s: Brand name. */
														__(
															'View %s products',
															'product-brand-grid'
														),
														brand.name
													) }
												>
													{ __(
														'View brand',
														'product-brand-grid'
													) }
												</a>
											) }
									</li>
								);
							} ) }
						</ul>
					</Disabled>
				) }
			</div>
		</>
	);
}
