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
 * @category   extensions
 * @package    Auth
 * @copyright  Copyright (c) 2005-2015 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

include 'kumbia_auth_base.php';
include 'kumbia_auth_interface.php';

class KumbiaAuth {
	/**
	 * Espacio de nombre para las variables de login
	 * @var String
	 */
	protected $_ns = 'KumbiaAuthNameSpace';

	/**
	 * Auth instance
	 * @var KumbiaAuthInterface
	 */
	protected $auth = NULL;

	/**
	 * Objeto de autenticacion
	 * @var KumbiaAuth
	 */
	static protected $_obj = null;

	/**
	 * Inyecta el objeto de autenticación
	 * @param KumbiaAuthInterface $auth
	 */
	public static function init(KumbiaAuthInterface $auth) {
		if (self::$_obj instanceof KumbiaAuthBase) {
			throw new Exception('Object was initialized');
		}
		self::$_obj = new KumbiaAuthBase($auth);
	}

	/**
	 * Can get login with load class
	 * @return boolean
	 */
	public static function isLogin() {
		return (bool) Session::get('login', KumbiaAuthBase::$namespace);
	}

	/**
	 * Make the facade
	 * @param  string $method
	 * @param  array $args
	 * @return mixed
	 */
	public static function __callStatic($method, $args) {
		if (!self::$_obj instanceof KumbiaAuthBase) {
			throw new Exception('Objeto de autenticación nulo');
		}
		$instance = self::$_obj;
		return call_user_func_array(array($instance, $method), $args);
	}

}
