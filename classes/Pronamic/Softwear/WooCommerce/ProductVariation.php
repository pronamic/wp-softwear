<?php

/**
 * Title: Product variation
 * Description: 
 * Copyright: Copyright (c) 2005 - 2011
 * Company: Pronamic
 * @author Remco Tolsma
 * @version 1.0
 */
class Pronamic_Softwear_WooCommerce_ProductVariation extends Pronamic_Softwear_WooCommerce_Product {
	/**
	 * Parent product
	 * 
	 * @var Pronamic_Softwear_WooCommerce_Product
	 */
	public $parent;

	//////////////////////////////////////////////////

	/**
	 * Construct and intialize an product variation
	 */
	public function __construct() {
		parent::__construct();
	}

	//////////////////////////////////////////////////

	/**
	 * Get the title of this product variation
	 * 
	 * @see Pronamic_Softwear_WooCommerce_Product::get_title()
	 */
	public function get_title() {
		$title = parent::get_title();

		if ( empty( $title ) ) {
			$title = sprintf( __( 'Variation #%s of %s', 'softwear' ), $this->id, $this->parent->title );
		}

		return $title;
	}

	//////////////////////////////////////////////////

	/**
	 * Get post
	 * 
	 * @see Pronamic_Softwear_WooCommerce_Product::get_post()
	 */
	public function get_post() {
		$post = array();

		$post['post_type'] = 'product_variation';
		$post['post_status'] = 'publish';
		$post['post_title'] = $this->get_title();

		if ( $this->parent != null ) {
			$post['post_parent'] = $this->parent->id;
		}

		return $post;
	}

	/**
	 * Get meta
	 * 
	 * @see Pronamic_Softwear_WooCommerce_Product::get_meta()
	 */
	public function get_meta() {
		$meta = array();

		$meta['_sku'] = $this->sku;
		$meta['_price'] = $this->price;
		$meta['_stock'] = $this->stock;
		$meta['_backorders'] = 'yes';
		$meta['_manage_stock'] = 'yes';
		$meta['_visibility'] = 'visible';

		foreach ( $this->attributes as $key => $value ) {
			// Maybe nicer to use the inserted or found term slug
			$value = sanitize_title( $value );

			$meta['attribute_' . $key] = $value;
		}

		return $meta;
	}
}
