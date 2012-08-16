<?php

/**
 * Title: WooCommerce 
 * Description: 
 * Copyright: Copyright (c) 2005 - 2011
 * Company: Pronamic
 * @author Remco Tolsma
 * @version 1.0
 */
class Pronamic_Softwear_DataRow {
	private $data;
	
	private $map;

	public function __construct($data, $map) {
		$this->data = $data;
		$this->map = $map;
	}

	public function get($key) {
		$result = null;

		if(isset($this->map[$key])) {
			$index = $this->map[$key];

			if(isset($this->data[$index])) {
				$result = $this->data[$index];
			}
		}

		return $result;
	}
}
