<?php

/**
 * Title: Plugin 
 * Description: 
 * Copyright: Copyright (c) 2005 - 2011
 * Company: Pronamic
 * @author Remco Tolsma
 * @version 1.0
 */
class Pronamic_Softwear_Plugin {
	/**
	 * The plugin file
	 * 
	 * @var string
	 */
	public static $file;

	//////////////////////////////////////////////////

	/**
	 * Bootstrap
	 */
	public static function bootstrap( $file ) {
		self::$file = $file;

		add_action( 'init', array( __CLASS__, 'init' ) );

		add_action( 'admin_init', array( __CLASS__, 'adminInit' ) );
		add_action( 'admin_menu', array( __CLASS__, 'adminMenu' ) );
		
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueueAdminScripts' ) );
	}

	//////////////////////////////////////////////////

	/**
	 * Initialize
	 */
	public static function init() {
		// Translations
		$rel_path = dirname( plugin_basename( self::$file ) ) . '/languages/';
		load_plugin_textdomain( 'softwear', false, $rel_path );
	}

	//////////////////////////////////////////////////

	/**
	 * Admin initialize
	 */
	public static function adminInit() {
		register_setting( 'softwear', 'softwear_uuid' );
		register_setting( 'softwear', 'softwear_datafeed_url' );
		register_setting( 'softwear', 'softwear_datafeed_map' );
	}

	//////////////////////////////////////////////////

	/**
	 * Enqueue admin scripts
	 */
	public static function enqueueAdminScripts($hook) {
		$isSoftwear = strpos($hook, 'softwear') !== false;

		if($isSoftwear) {
			// Styles
			wp_enqueue_style(
				'softwear_admin' , 
				plugins_url('css/admin.css', self::$file)
			);
		}
	}

	//////////////////////////////////////////////////
	
	/**
	 * Adds an extra settings page to the WordPress admin's settings board.
	 */
	public static function adminMenu() {
		// Menu
		add_menu_page(
			$pageTitle = __( 'Softwear', 'softwear' ) , 
			$menuTitle = __( 'Softwear', 'softwear' ) , 
			$capability = 'administrator' , 
			$menuSlug = 'softwear' , 
			$function = array( __CLASS__, 'pageIndex' ) , 
			$iconUrl = plugins_url( 'images/icon-16x16.png', self::$file )
		);

		add_submenu_page(
			$parentSlug = 'softwear' , 
			$pageTitle = __( 'Softwear Datafeed', 'softwear' ) , 
			$menuTitle = __( 'Datafeed', 'softwear' ) , 
			$capability = 'administrator' , 
			$menuSlug = 'softwear_datafeed' , 
			$function = array( __CLASS__, 'pageDatafeed' )
		);

		add_submenu_page(
			$parentSlug = 'softwear' , 
			$pageTitle = __( 'Softwear Synchronization', 'softwear' ) , 
			$menuTitle = __( 'Synchronization', 'softwear' ) , 
			$capability = 'administrator' , 
			$menuSlug = 'softwear_synchronization' , 
			$function = array( __CLASS__, 'pageSynchronization' )
		);

		// Rename first Softwear submenu item
		global $submenu;

		if(isset($submenu['softwear'])) {
			$submenu['softwear'][0][0] = __('Settings', 'softwear');
		}

		// Options page
		add_options_page(
			$pageTitle = __('Softwear', 'softwear') ,
			$menuTitle = __('Softwear', 'softwear') ,
			$capability = 'manage_options' ,
			$menuSlug = 'softwear_options' ,
			$function = array(__CLASS__, 'pageIndex')
		);
	}

	//////////////////////////////////////////////////

	/**
	 * Options page
	 */
	public static function pageIndex() {
		include plugin_dir_path(self::$file) . '/admin/index.php';
	}

	/**
	 * Datafeed page
	 */
	public static function pageDatafeed() {
		include plugin_dir_path(self::$file) . '/admin/datafeed.php';
	}

	/**
	 * Synchronize page
	 */
	public static function pageSynchronization() {
		include plugin_dir_path(self::$file) . '/admin/synchronization.php';
	}

	/**
	 * Options page
	 */
	public static function optionsPage() {
		include plugin_dir_path(self::$file) . '/admin/options.php';
	}

	//////////////////////////////////////////////////

	/**
	 * Get data
	 * 
	 * @return array
	 */
	public static function getData() {
		$url = get_option('softwear_datafeed_url');

		return Pronamic_Softwear_Softwear::getDataFromUrl($url);
	}

	//////////////////////////////////////////////////

	/**
	 * Get data map
	 * 
	 * @return array
	 */
	public static function getDataMap() {
		$dataMap = get_option('softwear_datafeed_map', array());

		return $dataMap;
	}

	//////////////////////////////////////////////////

	/**
	 * Build an associative array from an array and map
	 * 
	 * @param array $array
	 * @param array $map
	 */
	public static function buildAssociativeArray($array, $map) {
		$result = array();

		foreach($map as $i => $key) {
			if(isset($array[$i])) {
				$result[$key] = $array[$i];
			}
		}
		
		return $result;
	}

	//////////////////////////////////////////////////

	/**
	 * Get product by SKU
	 * 
	 * @return 
	 */
	public static function getWooCommerceProductBySku($sku, $type) {
		$product = null;

		if(!empty($sku)) {
			$products = get_posts(array(
				'post_type' => $type , 
				'posts_per_page' => -1 , 
				'meta_query' => array(
					array(
						'key' => '_sku' ,  
						'value' => $sku 
					)
				)
			));

			$product = array_pop($products);
		}

		return $product;
	}

	//////////////////////////////////////////////////

	/**
	 * Insert
	 */
	public static function insertImport($import) {
		$result = wp_insert_post($import->post, true);

		if(!is_wp_error($result)) {
			$post_ID = $result;

			foreach($import->meta as $key => $value) {
				$r = update_post_meta($post_ID, $key, $value);
			}

			foreach($import->tax as $taxonomy => $terms) {
				if(is_taxonomy_hierarchical($taxonomy)) {
					$ids = array();

					if(!is_array($terms)) {
						$terms = explode(',', $terms);
					}

					foreach($terms as $term) {
						$data = term_exists($term, $taxonomy);
						if(empty($data)) {
							$data = wp_insert_term($term, $taxonomy);
						}

						if(!empty($data) && !is_wp_error($data)) {
							$ids[] = $data['term_id'];
						}
					}

					$terms = $ids;
				}

				$r = wp_set_post_terms($post_ID, $terms, $taxonomy, false);
			}
		}

		return $result;
	}

	//////////////////////////////////////////////////

	public static function syncWooCommerceProduct(Pronamic_Softwear_WooCommerce_Product $product) {
		$result = true;

		if(empty($product->id)) {
			$result = self::insertWooCommerceProduct($product);
		} 

		return $result;
	}

	public static function insertWooCommerceProduct(Pronamic_Softwear_WooCommerce_Product $product) {
		$result = wp_insert_post($product->getPost(), true);

		if(!is_wp_error($result)) {
			$product->id = $result;
	
			foreach($product->getMeta() as $key => $value) {
				$r = update_post_meta($product->id, $key, $value);
			}
	
			foreach($product->getTaxonomies() as $taxonomy => $terms) {
				if(is_taxonomy_hierarchical($taxonomy)) {
					$ids = array();
	
					if(!is_array($terms)) {
						$terms = explode(',', $terms);
					}
	
					foreach($terms as $term) {
						$data = term_exists($term, $taxonomy);
						if(empty($data)) {
							$data = wp_insert_term($term, $taxonomy);
						}
	
						if(!empty($data) && !is_wp_error($data)) {
							$ids[] = $data['term_id'];
						}
					}
	
					$terms = $ids;
				}
	
				$r = wp_set_post_terms($product->id, $terms, $taxonomy, false);
			}
		}

		return $result;
	}

	//////////////////////////////////////////////////

	/**
	 * Insert
	 */
	public static function insertWooCommerceVariantProduct($import) {
		$result = wp_insert_post($import->post, true);

		if(!is_wp_error($result)) {
			$post_ID = $result;

			foreach($import->meta as $key => $value) {
				update_post_meta($post_ID, $key, $value);
			}

			foreach($import->tax as $tax => $terms) {
				wp_set_post_terms($post_ID, $terms, $tax, false);
			}
		}
	}
}
