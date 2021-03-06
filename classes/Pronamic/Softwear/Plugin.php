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

		add_action( 'admin_init', array( __CLASS__, 'admin_init' ) );
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );
		
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_enqueue_scripts' ) );
		
		register_activation_hook( $file, array( __CLASS__, 'activation' ) );
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
	public static function admin_init() {
		register_setting( 'softwear', 'softwear_uuid' );
		register_setting( 'softwear', 'softwear_datafeed_url' );
		register_setting( 'softwear', 'softwear_datafeed_map' );
	}

	//////////////////////////////////////////////////

	/**
	 * Enqueue admin scripts
	 */
	public static function admin_enqueue_scripts( $hook ) {
		$is_softwear = strpos( $hook, 'softwear' ) !== false;

		if ( $is_softwear ) {
			// Styles
			wp_enqueue_style(
				'softwear_admin' , 
				plugins_url( 'css/admin.css', self::$file )
			);
		}
	}

	//////////////////////////////////////////////////
	
	/**
	 * Adds an extra settings page to the WordPress admin's settings board.
	 */
	public static function admin_menu() {
		// Menu
		add_menu_page(
			$pageTitle = __( 'Softwear', 'softwear' ) , 
			$menuTitle = __( 'Softwear', 'softwear' ) , 
			$capability = 'softwear_manage_settings' , 
			$menuSlug = 'softwear' , 
			$function = array( __CLASS__, 'page_index' ) , 
			$iconUrl = plugins_url( 'images/icon-16x16.png', self::$file )
		);

		add_submenu_page(
			$parentSlug = 'softwear' , 
			$pageTitle = __( 'Softwear Datafeed', 'softwear' ) , 
			$menuTitle = __( 'Datafeed', 'softwear' ) , 
			$capability = 'softwear_view_datafeed' , 
			$menuSlug = 'softwear_datafeed' , 
			$function = array( __CLASS__, 'page_datafeed' )
		);

		add_submenu_page(
			$parentSlug = 'softwear' , 
			$pageTitle = __( 'Softwear Synchronization', 'softwear' ) , 
			$menuTitle = __( 'Synchronization', 'softwear' ) , 
			$capability = 'softwear_view_synchronization' , 
			$menuSlug = 'softwear_synchronization' , 
			$function = array( __CLASS__, 'page_synchronization' )
		);

		// Rename first Softwear submenu item
		global $submenu;

		if ( isset( $submenu['softwear'] ) ) {
			$submenu['softwear'][0][0] = __( 'Settings', 'softwear' );
		}

		// Options page
		add_options_page(
			$pageTitle = __( 'Softwear', 'softwear' ) ,
			$menuTitle = __( 'Softwear', 'softwear' ) ,
			$capability = 'manage_options' ,
			$menuSlug = 'softwear_options' ,
			$function = array( __CLASS__, 'page_index' )
		);
	}

	//////////////////////////////////////////////////

	/**
	 * Options page
	 */
	public static function page_index() {
		include plugin_dir_path( self::$file ) . '/admin/index.php';
	}

	/**
	 * Datafeed page
	 */
	public static function page_datafeed() {
		$url = get_option( 'softwear_datafeed_url' );
		
		include plugin_dir_path( self::$file ) . '/admin/datafeed.php';
	}

	/**
	 * Synchronize page
	 */
	public static function page_synchronization() {
		include plugin_dir_path( self::$file ) . '/admin/synchronization.php';
	}

	//////////////////////////////////////////////////
	
	/**
	 * Add role capabilities on activation
	 */
	public static function activation() {
		$role = get_role( 'administrator' );
		
		$role->add_cap( 'softwear_view_datafeed' );
		$role->add_cap( 'softwear_view_synchronization' );
		$role->add_cap( 'softwear_manage_settings' );
	}
	
	//////////////////////////////////////////////////

	/**
	 * Get data
	 * 
	 * @return array
	 */
	public static function get_data() {
		$url = get_option( 'softwear_datafeed_url' );

		return Pronamic_Softwear_Softwear::get_data_from_url( $url );
	}

	//////////////////////////////////////////////////

	/**
	 * Get data map
	 * 
	 * @return array
	 */
	public static function get_data_map() {
		$data_map = get_option( 'softwear_datafeed_map', array() );

		return $data_map;
	}

	//////////////////////////////////////////////////

	/**
	 * Synchronize WooCommerce product
	 * 
	 * @param Pronamic_Softwear_WooCommerce_Product $product
	 */
	public static function sync_product( Pronamic_Softwear_WooCommerce_Product $product ) {
		$result = true;

		if ( empty( $product->id ) ) {
			$result = self::insert_product( $product );
		} 

		return $result;
	}

	public static function insert_product( Pronamic_Softwear_WooCommerce_Product $product ) {
		$result = wp_insert_post( $product->get_post(), true );

		if ( ! is_wp_error( $result ) ) {
			$product->id = $result;
	
			foreach ( $product->get_meta() as $key => $value ) {
				$r = update_post_meta( $product->id, $key, $value );
			}
	
			foreach ( $product->get_taxonomies() as $taxonomy => $terms ) {
				if ( is_taxonomy_hierarchical( $taxonomy ) ) {
					$ids = array();
	
					if ( ! is_array( $terms ) ) {
						$terms = explode( ',', $terms );
					}
	
					foreach ( $terms as $term ) {
						$data = term_exists( $term, $taxonomy );
						if ( empty( $data ) ) {
							$data = wp_insert_term( $term, $taxonomy );
						}
	
						if ( ! empty( $data ) && ! is_wp_error( $data ) ) {
							$ids[] = $data['term_id'];
						}
					}
	
					$terms = $ids;
				}
	
				$r = wp_set_post_terms( $product->id, $terms, $taxonomy, false );
			}
		}

		return $result;
	}
}
