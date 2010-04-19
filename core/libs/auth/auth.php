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
 * Clase Principal para la gestion de autenticación
 * 
 * @category   Kumbia
 * @package    auth
 * @copyright  Copyright (c) 2005-2010 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */
class Auth
{
	/**
	 * Adaptador por defecto
	 *
	 * @var string
	 */
	protected static $_defaultAdapter = 'model';

	/**
	 * Obtiene el adaptador para Auth
	 *
	 * @param string $adapter (model, openid, oauth)
	 */
	public static function factory($adapter = NULL)
	{
		if(!$adapter) {
			$adapter = self::$_defaultAdapter;
		}

		require_once CORE_PATH . "libs/auth/adapters/{$adapter}_auth.php";
		$class = $adapter.'auth';
		
		return new $class;
	}

	/**
	 * Cambia el adaptador por defecto
	 *
	 * @param string $adapter nombre del adaptador por defecto
	 */
	public static function setDefault ($adapter)
	{
		self::$_defaultAdapter = $adapter;
	}
}
