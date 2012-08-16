<?php
/*
Plugin Name: Softwear
Plugin URI: http://pronamic.eu/wp-plugins/softwear/
Description: The Softwear plugin allows you to easily connect to the Softwear system.
Version: 0.1
Requires at least: 3.0
Author: Pronamic
Author URI: http://pronamic.eu/
License: GPL
*/

if(function_exists('spl_autoload_register')):

function pronamic_softwear_autoload($name) {
	$name = str_replace('\\', DIRECTORY_SEPARATOR, $name);
	$name = str_replace('_', DIRECTORY_SEPARATOR, $name);

	$file = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . $name . '.php';

	if(is_file($file)) {
		require_once $file;
	}
}

spl_autoload_register('pronamic_softwear_autoload');

Pronamic_Softwear_Plugin::bootstrap(__FILE__);

endif;
