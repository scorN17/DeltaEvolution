<?php

class dRequest {

	private $settings = array();
	private static $_instance = null;
	private $POST = array();
	private static $GET = array();

	private function __construct() {
		$this->POST = $_POST;
		//$this->GET = $_GET;
		self::$GET = $_GET;
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
		return ($this->POST);
	}


	public static function GET() {
		return (self::$GET);
	}

	public static function fieldGET($feild = false) {
		if (! $feild ) return false;
		if (array_key_exists($feild , self::$GET) ) {
			return  self::$GET[$feild];
		}
		return false; 
	}


	public static function fieldPOST($feild = false) {
		if (! $feild ) return false;
		if (array_key_exists($feild , self::$POST) ) {
			return self::$POST[$feild];
		}
		return false; 
	}




}
