<div class="wrap">
	<?php screen_icon('softwear'); ?>

	<h2>
		<?php _e('Softwear', 'softwear'); ?>
	</h2>
	
	<?php 

	function get_value_from_map($data, $map, $name) {
		$result = false;

		$index = array_search($name, $map);

		if($index !== false && isset($data[$index])) {
			$result = $data[$index];
		}
		
		return $result;
	}

	function get_sku_parent($data, $map) {
		$result = false;

		$index = array_search('sku_parent', $map);

		if($index !== false && isset($data[$index])) {
			$result = $data[$index];
		}
		
		return $result;
	}

	function get_sku_variation($data, $map) {
		$result = false;

		$index = array_search('sku_variation', $map);

		if($index !== false && isset($data[$index])) {
			$result = $data[$index];
		}
		
		return $result;
	}

	$url = get_option('softwear_datafeed_url');
	
	$data = Pronamic_Softwear::getDataFromUrl($url);
	$dataMap = get_option('softwear_datafeed_map');

	if($data !== false): ?>

	<h3>
		<?php _e('Synchronize', 'softwear'); ?>
	</h3>

	<?php 
	
	$thead = array_shift($data);
	
	$products = array(); 
	foreach($data as $row) {
		$sku = get_value_from_map($row, $dataMap, 'sku_parent');
		
		if(!empty($sku)) {
			if(!isset($products[$sku])) {
				$product = new Pronamic_Softwear_Product();
				$product->sku = $sku;
				$product->title = get_value_from_map($row, $dataMap, 'post_title');
				
				$category = trim(get_value_from_map($row, $dataMap, 'product_cat'));
				if(!empty($category)) {
					$product->addTerms('product_cat', $category);
				}

				$brand = trim(get_value_from_map($row, $dataMap, 'pa_merk'));
				if(!empty($brand)) {
					$product->addTerms('pa_merk', $brand);
				}

				$material = trim(get_value_from_map($row, $dataMap, 'pa_materiaal'));
				if(!empty($material)) {
					$product->addTerms('pa_materiaal', $material);
				}

				$target = trim(get_value_from_map($row, $dataMap, 'pa_doelgroep'));
				if(!empty($target)) {
					$product->addTerms('pa_doelgroep', $target);
				}

				$products[$sku] = $product;
			}
			
			$product = $products[$sku];

			$variation = new Pronamic_Softwear_Product_Variation();
			$variation->sku = get_value_from_map($row, $dataMap, 'sku_variation');
			$variation->title = get_value_from_map($row, $dataMap, 'post_title');
			$variation->price = (float) trim(get_value_from_map($row, $dataMap, 'price'));
			$variation->stock = (int) trim(get_value_from_map($row, $dataMap, 'stock'));
			$variation->setAttribute('pa_maat', get_value_from_map($row, $dataMap, 'pa_maat'));
			$variation->setAttribute('pa_kleur', get_value_from_map($row, $dataMap, 'pa_kleur'));

			$product->addVariation($variation);
		}
	}

	?>
	<div>
		<?php if(true): ?>
	
		<h3>
			<?php _e('Synchronize', 'softwear'); ?>
		</h3>
	
		<ul>
			<?php foreach($products as $product): ?>
			<li>
				<?php echo $product->sku; ?> - <?php echo $product->title; ?>
				<ul>
					<?php foreach($product->variations as $variation): ?>
					<li>
						- <?php echo $variation->sku; ?> 
						- <?php echo $variation->title; ?>
						- <?php echo $variation->stock; ?>
					</li>
					<?php endforeach; ?>
				</ul>
				
				<?php $taxonomies = $product->getTaxonomies(); ?>
				
				<?php if(true): ?>
				<textarea rows="5" cols="60"><?php var_dump($taxonomies); ?></textarea>
				<?php endif; ?>
			</li>
			<?php endforeach; ?>
		</ul>
	
		<?php 
		
		foreach($products as $product) {
			$post = Pronamic_Softwear::getWooCommerceProductBySku($product->sku, 'product');
			
			if(empty($post)) {
				$import = new stdClass();
				$import->post = $product->getPost();
				$import->meta = $product->getMeta();
				$import->tax = $product->getTaxonomies();
				
				$result = Pronamic_Softwear::insertImport($import);
				if(!is_wp_error($result)) {
					$product->id = $result;
				} else {
					var_dump($result);
				}
			} else {
				$product->id = $post->ID;
			}
			
			foreach($product->variations as $variation) {
				$import = new stdClass();
				$import->post = $variation->getPost();
				$import->meta = $variation->getMeta();
				$import->tax = array();
				
				$result = Pronamic_Softwear::insertImport($import);
				if(!is_wp_error($result)) {
					$variation->id = $result;
				} else {
					var_dump($result);
				}
			}
	
			$wcProduct = new WC_Product($product->id);
			$wcProduct->variable_product_sync();
		}
		
		?>
	
		<?php endif; ?>
	</div>
	
	<?php endif; ?>

	<h3>
		<?php _e('Documentation', 'softwear'); ?>
	</h3>

	<ul>
		<li>
			<a href="http://pronamic.nl/wp-content/uploads/2012/03/Softwear-Web-API-1.7.pdf">
				Softwear Web API <small>versie 1.7</small>
			</a>
		</li>
		<li>
			<a href="http://pronamic.nl/wp-content/uploads/2012/03/Softwear-Web-API-1.4.pdf">
				Softwear Web API <small>versie 1.4</small>
			</a>
		</li>
	</ul>

	<?php 
	
	/*

DELETE FROM wp_postmeta WHERE post_id IN (SELECT ID FROM wp_posts WHERE post_type IN ('product', 'product_variation'));
DELETE FROM wp_term_relationships WHERE object_id IN (SELECT ID FROM wp_posts WHERE post_type IN ('product', 'product_variation'));
DELETE FROM wp_posts WHERE post_type IN ('product', 'product_variation');

SELECT post_id, meta_value AS sku FROM wp_postmeta WHERE meta_key = '_sku';

	 */	
	
	?>
</div>