<?php
/**
 * KumbiaPHP web & app Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://wiki.kumbiaphp.com/Licencia
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@kumbiaphp.com so we can send you a copy immediately.
 *
 * @category   Kumbia
 * @package    KumbiaRouter
 * @copyright  Copyright (c) 2005-2015 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

class KumbiaFacade {
	static $store = [];
	/**
	 * Make the facade
	 * @param  string $method
	 * @param  array $args
	 * @return mixed
	 */
	public static function __callStatic($method, $args) {
		$class = get_called_class();

		if (!isset(self::$store[$class]) || !(self::$store[$class] instanceof $class)) {
			throw new Exception('Objeto de autenticación nulo');
		}
		$instance = self::$store[$class];
		return call_user_func_array(array($instance, $method), $args);
	}

	/**
	 * Inyecta el objeto de autenticación
	 * @param KumbiaAuthInterface $auth
	 */
	public static function init($obj) {
		$class = get_called_class();
		if (isset(self::$store[$class]))) {
			throw new Exception('Object was initialized');
		}
		self::$store[$class] = $obj;
	}


}