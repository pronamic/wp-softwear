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
	public static function getSkusMap() {
		global $wpdb;
		
		$results = $wpdb->get_results("
			SELECT 
				post_id , 
				meta_value AS sku 
			FROM 
				$wpdb->postmeta 
			WHERE 
				meta_key = '_sku';
		");
		
		$skusMap = array();
		
		if($results) {
			foreach($results as $result) {
				$skusMap[$result->sku] = $result->post_id;
			}
		}

		return $skusMap;
	}

	//////////////////////////////////////////////////

	/**
	 * Transform data array to products array
	 * 
	 * @param array $data
	 * @return array
	 */
	public static function transformDataToProducts($data, $map) {
		// Products
		$products = array();
		
		// SKU's
		$skus = self::getSkusMap();

		// Shift the table head from the data array
		$thead = array_shift($data);	
		
		// Data loop
		foreach($data as $row) {
			$dataRow = new Pronamic_Softwear_DataRow($row, $map);

			$sku = $dataRow->get('sku_parent');

			if(!empty($sku)) {
				if(!isset($products[$sku])) {
					$product = new Pronamic_Softwear_WooCommerce_Product();
					
					if(isset($skus[$sku])) {
						$product->id = $skus[$sku];
					}

					$product->sku = $sku;
					$product->title = $dataRow->get('post_title');
					
					$category = trim($dataRow->get('product_cat'));
					if(!empty($category)) {
						$product->addTerms('product_cat', $category);
					}
		
					$brand = trim($dataRow->get('pa_merk'));
					if(!empty($brand)) {
						$product->addTerms('pa_merk', $brand);
						$product->setAttribute('pa_merk', $brand);
					}
		
					$material = trim($dataRow->get('pa_materiaal'));
					if(!empty($material)) {
						$product->addTerms('pa_materiaal', $material);
						$product->setAttribute('pa_materiaal', $material);
					}
		
					$target = trim($dataRow->get('pa_doelgroep'));
					if(!empty($target)) {
						$product->addTerms('pa_doelgroep', $target);
						$product->setAttribute('pa_doelgroep', $target);
					}
		
					$products[$sku] = $product;
				}
				
				$product = $products[$sku];
				
				// Variation
				$sku = $dataRow->get('sku_variation');

				if(!empty($sku)) {
					$variation = new Pronamic_Softwear_WooCommerce_ProductVariation();
						
					if(isset($skus[$sku])) {
						$variation->id = $skus[$sku];
					}
	
					$variation->sku = $sku;
					$variation->price = (float) trim($dataRow->get('price'));
					$variation->stock = (int) trim($dataRow->get('stock'));
					$variation->setAttribute('pa_maat', $dataRow->get('pa_maat'));
					$variation->setAttribute('pa_kleur', $dataRow->get('pa_kleur'));
			
					$product->addVariation($variation);
				}
			}
		}

		return $products;
	}
}
