<?php 

global $woocommerce;

?>
<div class="wrap">
	<?php screen_icon( 'softwear' ); ?>

	<h2>
		<?php _e( 'Softwear Synchronization', 'softwear' ); ?>
	</h2>

	<?php 

	$map = Pronamic_Softwear_Plugin::getDataMap();
	$map = array_flip($map);

	$data = Pronamic_Softwear_Plugin::getData();

	if($data !== false): ?>

	<div>
		<?php 

		$products = Pronamic_Softwear_WooCommerce::transformDataToProducts($data, $map);
		
		?>
		<table cellspacing="0" class="widefat fixed">
			<thead>
				<tr>
					<th scope="col"><?php _e('SKU', 'softwear'); ?></th>
					<th scope="col"><?php _e('Title', 'softwear'); ?></th>
					<th scope="col"><?php _e('ID', 'softwear'); ?></th>
					<th scope="col"><?php _e('Price', 'softwear'); ?></th>
					<th scope="col"><?php _e('Stock', 'softwear'); ?></th>
					<th scope="col"><?php _e('Synchronized', 'softwear'); ?></th>
				</tr>
			</thead>

			<tbody>
				
				<?php foreach($products as $product): ?>
				
				<tr style="font-weight: bold;">
					<?php $synced = Pronamic_Softwear_Plugin::syncWooCommerceProduct($product); ?>
					<td>
						<?php echo $product->sku; ?>
					</td>
					<td>
						<?php echo $product->getTitle(); ?>
					</td>
					<td>
						<?php if(!empty($product->id)): ?>

						<a href="<?php echo get_permalink($product->id); ?>">
							<?php echo $product->id; ?>
						</a>

						<?php endif; ?>
					</td>
					<td>
						
					</td>
					<td>
					
					</td>
					<td>
					
					</td>
				</tr>
				
				<?php if(false): ?>
				
				<tr>
					<td colspan="5">
						<?php $taxonomies = $product->getTaxonomies(); ?>

						<textarea rows="5" cols="60"><?php var_dump($taxonomies); ?></textarea>
					</td>
				</tr>
				
				<?php endif; ?>

				<?php foreach($product->variations as $variation): ?>

				<tr>
					<?php 
					
					$synced = Pronamic_Softwear_Plugin::syncWooCommerceProduct($variation); 
					
					$woocommerce->clear_product_transients( $variation->id ); 
					
					?>
					<td>
						<?php echo $variation->sku; ?>
					</td>
					<td>
						<?php echo $variation->getTitle(); ?>
					</td>
					<td>
						<?php echo $variation->id; ?>
					</td>
					<td>
						&euro;&nbsp;<?php echo number_format($variation->price, 2, ',', '.'); ?>
						
						<?php 
						
						$result = update_post_meta($variation->id, '_price', $variation->price);
						
						echo $result !== false ? '&#9991;' : '';
						
						?>
					</td>
					<td>
						<?php echo $variation->stock; ?>
						
						<?php 
						
						$result = update_post_meta($variation->id, '_stock', $variation->stock);
						
						echo $result !== false ? '&#9991;' : '';
						
						?>
					</td>
					<td>
						<?php echo $synced ? __( 'Yes', 'softwear' ) : __( 'No', 'softwear' ); ?>
					</td>
				</tr>

				<?php endforeach; ?>

				<tr>
					<td colspan="6">
						<?php 
					
						$woocommerce->clear_product_transients( $product->id ); 

						$wcProduct = new WC_Product( $product->id );
						$wcProduct->variable_product_sync( );

						?>
					</td>
				</tr>

				<?php endforeach; ?>
			</tbody>
		</table>
	</div>

	<?php endif; ?>
</div>