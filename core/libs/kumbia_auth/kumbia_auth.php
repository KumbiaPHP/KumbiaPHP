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
	use KumbiaFacade;
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
	 * Can get login with load class
	 * @return boolean
	 */
	public static function isLogin() {
		return (bool) Session::get('login', KumbiaAuthBase::$namespace);
	}

}
