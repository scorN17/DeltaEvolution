<?php

dAutoloader::Register();
Rst::getInstance();

/**
 *
 *
 *
 * 
 */
class dAutoloader {

	const DELTA_ROOT = __DIR__;

	public static function Register() {
		if (function_exists('__autoload')) {
			spl_autoload_register('__autoload');
		}

		return spl_autoload_register(array('dAutoloader', 'Load'));
	}

	public static function Load($class) {
		if ( ! class_exists($class, false)) {
			$classPath = self::DELTA_ROOT .'/'. str_replace('_',DIRECTORY_SEPARATOR, $class) .'.php';

			if ((file_exists($classPath) === false) || (is_readable($classPath) === false)) {
				return false;
			}
			require($classPath);
		}
	}
}
