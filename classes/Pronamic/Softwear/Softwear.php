<?php

/**
 * Title: Softwear 
 * Description: 
 * Copyright: Copyright (c) 2005 - 2011
 * Company: Pronamic
 * @author Remco Tolsma
 * @version 1.0
 */
class Pronamic_Softwear_Softwear {
	/**
	 * Get data from URL
	 * 
	 * @param string $url
	 * @return array
	 */
	public static function get_data_from_url( $url ) {
		$data = false;
		
		// Get URL
		$response = wp_remote_get($url);

		if(!is_wp_error($response)) {
			$data = array();

			$body = $response['body'];
			$body = utf8_encode($body);

			$lines = explode("\n", $body);
			foreach($lines as $line) {
				$line = trim($line);

				$data[] = explode(';', $line);
			}
		}
		
		return $data;
	}
}
