<?php

/**
 *
 *
 *
 * 
 */
class Rst {

	private $settings = array();
	private static $_instance = null;
	private $P = array();
	private static $G = array();

	private function __construct() {
		$this->P = $_POST;
		//$this->G = $_GET;
		self::$G = $_GET;
	}


	protected function __clone() {

	}	


	protected function __wakeup() {

	}


	static public function getInstance() {
		if (is_null(self::$_instance)){
			self::$_instance = new self();
		}
		return self::$_instance;
	}


	public static function POST() {
		return ($this->P);
	}


	public static function GET() {
		return (self::$G);
	}

	public static function fieldGET($feild = false) {
		if (! $feild ) return false;
		if (array_key_exists($feild , self::$G) ) {
			return  self::$G[$feild];
		}
		return false; 
	}


	public static function fieldPOST($feild = false) {
		if (! $feild ) return false;
		if (array_key_exists($feild , self::$P) ) {
			return self::$P[$feild];
		}
		return false; 
	}




}
