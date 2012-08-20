<?php

/**
 * Title: WooCommerce 
 * Description: 
 * Copyright: Copyright (c) 2005 - 2011
 * Company: Pronamic
 * @author Remco Tolsma
 * @version 1.0
 */
class Pronamic_Softwear_WooCommerce {
	/**
	 * Get WooCommerce SKU's with WordPress post ID's map
	 * 
	 * @return array
	 */
	public static function get_skus_map() {
		global $wpdb;
		
		$results = $wpdb->get_results( "
			SELECT 
				post_id , 
				meta_value AS sku 
			FROM 
				$wpdb->postmeta 
			WHERE 
				meta_key = '_sku';
		" );
		
		$skus_map = array();
		
		if ( $results ) {
			foreach ( $results as $result ) {
				$skus_map[$result->sku] = $result->post_id;
			}
		}

		return $skus_map;
	}

	//////////////////////////////////////////////////

	/**
	 * Transform data array to products array
	 * 
	 * @param array $data
	 * @return array
	 */
	public static function transform_data_to_products( $data, $map ) {
		// Products
		$products = array();
		
		// SKU's
		$skus = self::get_skus_map();

		// Shift the table head from the data array
		$thead = array_shift( $data );	
		
		// Data loop
		foreach ( $data as $row ) {
			$data_row = new Pronamic_Softwear_DataRow( $row, $map );

			$sku = $data_row->get( 'sku_parent' );

			if ( ! empty( $sku ) ) {
				if ( ! isset( $products[$sku] ) ) {
					$product = new Pronamic_Softwear_WooCommerce_Product();
					
					if ( isset( $skus[$sku] ) ) {
						$product->id = $skus[$sku];
					}

					$product->sku = $sku;
					$product->title = $data_row->get( 'post_title' );
					
					$category = trim( $data_row->get( 'product_cat' ) );
					if ( ! empty( $category ) ) {
						$product->add_terms( 'product_cat', $category );
					}
		
					$brand = trim( $data_row->get( 'pa_merk' ) );
					if ( ! empty( $brand ) ) {
						$product->add_terms( 'pa_merk', $brand );
						$product->set_attribute( 'pa_merk', $brand );
					}
		
					$material = trim( $data_row->get( 'pa_materiaal' ) );
					if ( ! empty( $material ) ) {
						$product->add_terms( 'pa_materiaal', $material );
						$product->set_attribute( 'pa_materiaal', $material );
					}
		
					$target = trim( $data_row->get( 'pa_doelgroep' ) );
					if ( ! empty( $target ) ) {
						$product->add_terms( 'pa_doelgroep', $target );
						$product->set_attribute( 'pa_doelgroep', $target );
					}
		
					$products[$sku] = $product;
				}
				
				$product = $products[$sku];
				
				// Variation
				$sku = $data_row->get( 'sku_variation' );

				if ( ! empty( $sku ) ) {
					$variation = new Pronamic_Softwear_WooCommerce_ProductVariation();
						
					if ( isset( $skus[$sku] ) ) {
						$variation->id = $skus[$sku];
					}
	
					$variation->sku = $sku;
					$variation->price = (float) trim( $data_row->get( 'price' ) );
					$variation->stock = (int) trim( $data_row->get( 'stock' ) );
					$variation->set_attribute( 'pa_maat', $data_row->get( 'pa_maat' ) );
					$variation->set_attribute( 'pa_kleur', $data_row->get( 'pa_kleur' ) );
			
					$product->add_variation( $variation );
				}
			}
		}

		return $products;
	}
}
