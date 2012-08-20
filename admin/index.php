<div class="wrap">
	<?php screen_icon( 'softwear' ); ?>

	<h2>
		<?php _e( 'Softwear', 'softwear' ); ?>
	</h2>

	<h3>
		<?php _e( 'Settings', 'softwear' ); ?>
	</h3>

	<form method="post" action="options.php">
		<?php settings_fields( 'softwear' ); ?>

		<table class="form-table">
			<tr valign="top">
				<th scope="row">
					<label for="softwear_uuid_field">
						<abbr title="Universally Unique IDentifier"><?php _e( 'UUID', 'softwear' ); ?></abbr>
					</label>
				</th>
				<td>
					<input id="softwear_uuid_field" name="softwear_uuid" value="<?php form_option( 'softwear_uuid' ); ?>" type="text" class="regular-text" />

					<span class="description">
						<br /><?php _e( 'For example: <code>1660-8920-F99A-11E0-BE50-0800-200C-9A66</code>', 'softwear' ); ?>
						<br /><?php _e( 'The API uses a token for identification. If an API has been setup for a Softwear instance, a token will be provided.', 'softwear' ); ?>
					</span>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">
					<label for="softwear_datafeed_url_field"><?php _e( 'Articles Datafeed URL', 'softwear' ); ?></label>
				</th>
				<td>
					<input id="softwear_datafeed_url_field" name="softwear_datafeed_url" value="<?php form_option( 'softwear_datafeed_url' ); ?>" type="url" class="regular-text" />

					<span class="description">
						<br /><?php _e( 'For example: <code>http://www.softwear.nl/ism/bebees/osws.csv</code>', 'softwear' ); ?>
						<br /><?php _e( 'Datafeeds are available for article information and client information. Datafeeds are refreshed automatically every 24 hrs. All datafeeds are in CSV format. The content of the datafeeds can vary per project, for more information contact Softwear at <a href="mailto:info@softwear.nl">info@softwear.nl</a> or <a href="tel:0900-SOFTWEAR">0900-SOFTWEAR</a>.', 'softwear' ); ?>
					</span>
				</td>
			</tr>
		</table>

		<?php submit_button(); ?>
	
		<?php

		$data = Pronamic_Softwear_Plugin::get_data();
	
		if ( $data !== false ): ?>
		
			<h3>
				<?php _e( 'Datafeed', 'softwear' ); ?>
			</h3>
		
			<table cellspacing="0" class="widefat fixed">
				<?php foreach ( array( 'thead', 'tfoot' ) as $tag ): ?>
					<<?php echo $tag; ?>>
						<tr>
							<th scope="col" class="manage-column"><?php _e( 'First Row', 'softwear' ) ?></th>
							<th scope="col" class="manage-column"><?php _e( 'CSV » WooCommerce', 'softwear' ) ?></th>
							<th scope="col" class="manage-column"><?php _e( 'Variation Attribute', 'softwear' ) ?></th>
							<th scope="col" class="manage-column"><?php _e( 'Second Row', 'softwear' ) ?></th>
						</tr>
					</<?php echo $tag; ?>>
				<?php endforeach; ?>
		
				<tbody>
					<?php 
					
					$data_map = get_option('softwear_datafeed_map');
					
					$map_fields = array(
						'Post' => array(
							'post_title' => __( 'Title', 'softwear' ) ,
						) , 
						'Meta' => array(
							'sku_parent' => __( 'SKU (parent)', 'softwear' ) ,
							'sku_variation' => __( 'SKU (variation)', 'softwear' ) ,
							'stock' => __( 'Stock', 'softwear' ) , 
							'price' => __( 'Price', 'softwear' ) 
						) , 
						'Terms' => array(
							
						)
					);
	
					$taxonomies = get_taxonomies(array(
						
					), 'objects');
					
					foreach ( $taxonomies as $key => $taxonomy ) {
						$map_fields['Terms'][$key] = $taxonomy->labels->singular_name;
					}
	
					$first_row = $data[0];
					$second_row = $data[1];
					
					?>
		
					<?php foreach ( $first_row as $i => $column ): ?>
						<tr>
							<td>
								<?php echo $first_row[$i]; ?>
							</td>
							<td>
								<select name="softwear_datafeed_map[<?php echo $i; ?>]">
									<option value=""></option>
									<?php foreach ( $map_fields as $name => $group ): ?>
										<optgroup label="<?php echo esc_attr( $name ); ?>">
											<?php foreach ( $group as $value => $label ): ?>
												<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $data_map[$i], $value ); ?>>
													<?php echo $label; ?>
												</option>
											<?php endforeach; ?>
										</optgroup>
									<?php endforeach; ?>
								</select>
							</td>
							<td>
								<input name="softwear_datafeed_map2[<?php echo $i; ?>]" type="checkbox" <?php checked( 'test', $i ); ?> />
							</td>
							<td>
								<?php echo $second_row[$i]; ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
	
			<?php submit_button(); ?>

		<?php endif; ?>
	</form>

	<h3>
		<?php _e( 'Documentation', 'softwear' ); ?>
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
</div>