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

class KumbiaAuthBase {

	/**
	 * Object of auth
	 * @var KumbiaAuthInterface
	 */
	protected $auth;

	/**
	 * Namespace of session
	 * @var string
	 */
	static public $namespace = 'Addssfsweds';

	/**
	 * Create auth object
	 * @param KumbiaAuthInterface $auth interfaz
	 */
	public function __construct(KumbiaAuthInterface $auth) {
		$this->auth = $auth;
	}

	/**
	 * Verifica si el usuario está logueado
	 * @return bool
	 */
	public function isLogin() {
		return (bool) Session::get('login', self::$namespace);
	}

	/**
	 * Desloguea a un usuario
	 */
	public function logout() {
		Session::set('login', FALSE, self::$namespace);
	}

	/**
	 * Hace el login
	 * @param Array $args Agumentos para autentica
	 * @return bool
	 */
	public function login(Array $args = array()) {
		$login = $this->auth->login($args);
		Session::set('login', $login, self::$namespace);
		return $login;
	}

	/**
	 * Get information of auth
	 * @param  string $name name
	 * @return mixed
	 */
	public function get($name) {
		return $this->auth->get($name);
	}
}