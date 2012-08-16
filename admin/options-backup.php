<div class="wrap">
	<?php screen_icon('softwear'); ?>

	<h2>
		<?php _e('Softwear', 'softwear'); ?>
	</h2>

	<form method="post" action="options.php">
		<?php settings_fields('softwear'); ?>

		<table class="form-table">
			<tr valign="top">
				<th scope="row">
					<label for="softwear_uuid_field">
						<abbr title="Universally Unique IDentifier"><?php _e('UUID', 'softwear') ?></abbr>
					</label>
				</th>
				<td>
					<input id="softwear_uuid_field" name="softwear_uuid" value="<?php form_option('softwear_uuid'); ?>" type="text" class="regular-text" />

					<span class="description">
						<br /><?php _e('For example: <code>1660-8920-F99A-11E0-BE50-0800-200C-9A66</code>', 'softwear'); ?>
						<br /><?php _e('The API uses a token for identification. If an API has been setup for a Softwear instance, a token will be provided.', 'softwear'); ?>
					</span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="softwear_datafeed_url_field"><?php _e('Datafeed URL', 'softwear') ?></label>
				</th>
				<td>
					<input id="softwear_datafeed_url_field" name="softwear_datafeed_url" value="<?php form_option('softwear_datafeed_url'); ?>" type="url" class="regular-text" />

					<span class="description">
						<br /><?php _e('For example: <code>http://www.softwear.nl/ism/bebees/osws.csv</code>', 'softwear'); ?>
						<br /><?php _e('Datafeeds are available for article information and client information. Datafeeds are refreshed automatically every 24 hrs. All datafeeds are in CSV format. The content of the datafeeds can vary per project, for more information contact Softwear at <a href="mailto:info@softwear.nl">info@softwear.nl</a> or <a href="tel:0900-SOFTWEAR">0900-SOFTWEAR</a>.', 'softwear'); ?>
					</span>
				</td>
			</tr>
		</table>

		<?php submit_button(); ?>
	
		<?php

		$url = get_option('softwear_datafeed_url');
		
		$data = Pronamic_Softwear::getDataFromUrl($url);
	
		if($data !== false): ?>
	
		<h3>
			<?php _e('Datafeed', 'softwear'); ?>
		</h3>
	
		<table cellspacing="0" class="widefat fixed">
	
			<?php foreach(array('thead', 'tfoot') as $tag): ?>
	
			<<?php echo $tag; ?>>
				<tr>
					<th scope="col" class="manage-column"><?php _e('First Row', 'softwear') ?></th>
					<th scope="col" class="manage-column"><?php _e('CSV » WooCommerce', 'softwear') ?></th>
					<th scope="col" class="manage-column"><?php _e('Second Row', 'softwear') ?></th>
				</tr>
			</<?php echo $tag; ?>>
	
			<?php endforeach; ?>
	
			<tbody>
				<?php 
				
				$dataMap = get_option('softwear_datafeed_map');
				
				$mapFields = array(
					'Post' => array(
						'post_title' => __('Title', 'softwear') ,
					) , 
					'Meta' => array(
						'sku_parent' => __('SKU (parent)', 'softwear') ,
						'sku_variation' => __('SKU (variation)', 'softwear') ,
						'stock' => __('Stock', 'softwear') , 
						'price' => __('Price', 'softwear') 
					) , 
					'Terms' => array(
					
					)
				);

				$taxonomies = get_taxonomies(array(
					
				), 'objects');
				
				foreach($taxonomies as $key => $taxonomy) {
					$mapFields['Terms'][$key] = $taxonomy->labels->singular_name;
				}

				$firstRow = $data[0];
				$secondRow = $data[1];
				
				?>
	
				<?php foreach($firstRow as $i => $column): ?>
	
				<tr>
					<td>
						<?php echo $firstRow[$i]; ?>
					</td>
					<td>
						<select name="softwear_datafeed_map[<?php echo $i; ?>]">
							<option value=""></option>
							<?php foreach($mapFields as $name => $group): ?>
							<optgroup label="<?php echo esc_attr($name); ?>">
								<?php foreach($group as $value => $label): ?>
								<option value="<?php echo esc_attr($value); ?>" <?php selected($dataMap[$i], $value); ?>>
									<?php echo $label; ?>
								</option>
								<?php endforeach; ?>
							</optgroup>
							<?php endforeach; ?>
						</select>
					</td>
					<td>
						<?php echo $secondRow[$i]; ?>
					</td>
				</tr>
				
				<?php endforeach; ?>
	
			</tbody>
		</table>
	
		<?php submit_button(); ?>
	</form>

	<?php endif; ?>
	
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
	
	$products = array(); 
	foreach($data as $row) {
		$sku = get_value_from_map($row, $dataMap, 'sku_parent');
		
		if(!empty($sku)) {
			if(!isset($products[$sku])) {
				$product = new Pronamic_Softwear_Product();
				$product->sku = $sku;
				$product->title = get_value_from_map($row, $dataMap, 'post_title');

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
			
			<?php if(false): ?>
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

	<h3>
		<?php _e('Synchronize', 'softwear'); ?>
	</h3>

	<table cellspacing="0" class="widefat fixed">
		<?php $firstRow = array_shift($data); ?>

		<?php foreach(array('thead', 'tfoot') as $tag): ?>

		<<?php echo $tag; ?>>
			<tr>
				<?php foreach($firstRow as $value): ?>
				<th scope="col" class="manage-column">
					<?php echo $value; ?>
				</th>
				<?php endforeach; ?>
				<th scope="col">
					<?php _e('Product', 'softwear'); ?>
				</th>
			</tr>
		</<?php echo $tag; ?>>

		<?php endforeach; ?>

		<tbody>
			<?php foreach($data as $row): $i++; ?>
			
			<tr>
				<?php 
				
				$import = new stdClass();
				$import->post = array();
				$import->meta = array();
				$import->terms = array();
				
				foreach($row as $column): ?>
				<td>
					<?php echo $column; ?>
				</td>
				<?php endforeach; ?>
				<td>
					<?php 
					
					$parent = Pronamic_Softwear::getWooCommerceProductBySku($skuParent, 'product');
					if(empty($parent)) {
						$parentImport = new stdClass();
						$parentImport->post = array(
							'post_title' => get_value_from_map($row, $dataMap, 'post_title') ,
							'post_type' => 'product' , 
							'post_status' => 'publish'
						);
						$parentImport->tax = array(
							'product_type' => array('variable') 
						);
						
						foreach($mapFields['Terms'] as $key => $name) {
							$terms = get_value_from_map($row, $dataMap, $key);

							if(!empty($terms)) {
								$parentImport->tax[$key] = $terms;
							}
						} 

						$parentImport->meta = array(
							'_sku' => $skuParent , 
							'_product_attributes' => array(
								'pa_maat' => array(
									'name' => 'pa_maat',
									'value' => '',
									'position' => '0',
									'is_visible' => '0',
									'is_variation' => '1',
									'is_taxonomy' => '1',
								) , 
								'pa_kleur' => array(
									'name' => 'pa_kleur',
									'value' => '',
									'position' => '0',
									'is_visible' => '0',
									'is_variation' => '1',
									'is_taxonomy' => '1',
								) , 
								'pa_doelgroep' => array(
									'name' => 'pa_doelgroep',
									'value' => '',
									'position' => '0',
									'is_visible' => '0',
									'is_variation' => '0',
									'is_taxonomy' => '1',
								)
							)
						);
						/*
						$result = Pronamic_Softwear::insertImport($parentImport);
						if(!is_wp_error($result)) {
							$parent = get_post($result);
						} else {
							var_dump($result);
						}
						*/
					}

					if(!empty($parent)) {
						$sku = get_value_from_map($row, $dataMap, 'sku_variation');

						$product = Pronamic_Softwear::getWooCommerceProductBySku($sku, 'product_variation');

						if(empty($product)) {
							$import->post = array(
								'post_parent' => $parent->ID , 
								'post_title' => get_value_from_map($row, $dataMap, 'post_title') ,
								'post_type' => 'product_variation' , 
								'post_status' => 'publish'
							);
							$import->meta = array(
								'_sku' => $sku , 
								'_stock' => absint(trim(get_value_from_map($row, $dataMap, 'stock'))) , 
								'_price' => (float) trim(get_value_from_map($row, $dataMap, 'price')) , 
								'attribute_' . 'pa_maat' => get_value_from_map($row, $dataMap, 'pa_maat') , 
								'attribute_' . 'pa_kleur' => get_value_from_map($row, $dataMap, 'pa_kleur') 
							);
							$import->tax = array(
								
							);
							/*
							$result = Pronamic_Softwear::insertImport($import);
							if(!is_wp_error($result)) {
								$product = get_post($result);
							} else {
								var_dump($result);
							}
							*/
						}
					}

					?>
				</td>
			</tr>
			<tr>
				<td colspan="<?php echo count($row); ?>">
					<pre><?php // var_dump($parentImport); ?></pre>
				</td>
			</tr>

			<?php endforeach; ?>
		</tbody>
	</table>
	
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

	 */	
	
	?>
</div>