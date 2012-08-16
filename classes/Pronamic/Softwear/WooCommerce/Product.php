<?php

/**
 * Title: Product 
 * Description: 
 * Copyright: Copyright (c) 2005 - 2011
 * Company: Pronamic
 * @author Remco Tolsma
 * @version 1.0
 */
class Pronamic_Softwear_WooCommerce_Product {
	/**
	 * WordPress post ID
	 * 
	 * @var string
	 */
	public $id;

	/**
	 * WordPress post title
	 * 
	 * @var string
	 */
	public $title;

	/**
	 * WooCommerce product SKU
	 * 
	 * @var string
	 */
	public $sku;

	/**
	 * WooCommerce product price
	 * 
	 * @var int
	 */
	public $price;

	/**
	 * WooCommerce product stock
	 * 
	 * @var int
	 */
	public $stock;

	//////////////////////////////////////////////////

	/**
	 * Taxonomies
	 * 
	 * @var array
	 */
	public $taxonomies;

	/**
	 * Attributes
	 * 
	 * @var array
	 */
	public $attributes;

	/**
	 * Variations
	 * 
	 * @var array
	 */
	public $variations;

	//////////////////////////////////////////////////

	/**
	 * Constructs and initialize an product
	 */
	public function __construct() {
		$this->taxonomies = array();
		$this->attributes = array();
		$this->variations = array();
	}

	//////////////////////////////////////////////////

	public function getTitle() {
		return $this->title;
	}

	//////////////////////////////////////////////////
	// Taxonomies
	//////////////////////////////////////////////////

	public function addTerms($taxonmy, $terms) {
		if(!isset($this->taxonomies[$taxonmy])) {
			$this->taxonomies[$taxonmy] = array(); 
		}

		if(!is_array($terms)) {
			$terms = array_map('trim', explode(',', $terms));
		}

		$this->taxonomies[$taxonmy] += $terms;
	}

	public function getTaxonomies() {
		$taxonomies = $this->taxonomies;

		// Type
		$taxonomies['product_type'] = array('variable'); 

		// Variations taxonomies
		foreach($this->variations as $variation) {
			foreach($variation->attributes as $key => $value) {
				if(!isset($taxonomies[$key])) {
					$taxonomies[$key] = array();
				}

				$taxonomies[$key][] = $value; 
			}
		}
		
		return $taxonomies;
	}

	//////////////////////////////////////////////////
	// Attributes
	//////////////////////////////////////////////////

	public function setAttribute($key, $value) {
		$this->attributes[$key] = $value;
	}
	
	//////////////////////////////////////////////////
	// Variations
	//////////////////////////////////////////////////

	public function addVariation(Pronamic_Softwear_WooCommerce_ProductVariation $variation) {
		$variation->parent = $this;

		$this->variations[] = $variation;
	}
	
	//////////////////////////////////////////////////

	public function getPost() {
		$post = array();

		$post['post_type'] = 'product';
		$post['post_status'] = 'publish';
		$post['post_title'] = $this->title;

		return $post;
	}

	public function getMeta() {
		$meta = array();

		$meta['_sku'] = $this->sku;
		$meta['_backorders'] = 'yes';
		$meta['_manage_stock'] = 'yes';
		$meta['_visibility'] = 'visible';

		$meta['_product_attributes'] = array();
		
		// Normal attributes
		$position = 0;

		foreach($this->attributes as $key => $value) {
			$meta['_product_attributes'][$key] = array(
				'name' => $key ,
				'value' => '' , 
				'position' => $position ,
				'is_visible' => true , 
				'is_variation' => false , 
				'is_taxonomy' => true 
			);
					
			$position++;
		}
		
		// Variations attributes
		$position = 0;

		foreach($this->variations as $variation) {
			foreach($variation->attributes as $key => $value) {
				if(!isset($meta['_product_attributes'][$key])) {
					$meta['_product_attributes'][$key] = array(
						'name' => $key ,
						'value' => '',
						'position' => $position ,
						'is_visible' => false , 
						'is_variation' => true , 
						'is_taxonomy' => true 
					);
					
					$position++;
				}
			}
		}

		return $meta;
	}
}
