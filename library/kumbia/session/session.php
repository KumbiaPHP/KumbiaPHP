<?php

/**
 * Kumbia PHP Framework
 *
 * LICENSE
 *
 * This source file is subject to the GNU/GPL that is bundled
 * with this package in the file docs/LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.kumbia.org/license.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kumbia@kumbia.org so we can send you a copy immediately.
 *
 * @category Kumbia
 * @package Session
 * @copyright  Copyright (c) 2005-2007 Andres Felipe Gutierrez (andresfelipe at vagoogle.net)
 * @license http://www.kumbia.org/license.txt GNU/GPL
 */

/**
 * Modelo orientado a objetos para el acceso a datos en Sesiones
 *
 * @category Kumbia
 * @package Session
 * @copyright Copyright (c) 2005-2007 Andres Felipe Gutierrez (andresfelipe at vagoogle.net)
 * @license http://www.kumbia.org/license.txt GNU/GPL
 */
class Session 
{
	/**
	 * Crear o especificar el valor para un indice de la sesión
	 * actual
	 *
	 * @param string $index
	 * @param string $namespace
	 */
	static function set($index, $value, $namespace='default')
	{
	  	$_SESSION['KUMBIA_SESSION'][APP_PATH][$namespace][$index] = $value;
	}
	/**
	 * Obtener el valor para un indice de la sesion
	 *
	 * @param string $index
	 * @param string $namespace
	 * @return mixed
	 */
	static function get($index, $namespace='default')
	{
		if(isset($_SESSION['KUMBIA_SESSION'][APP_PATH][$namespace][$index])) {
			return $_SESSION['KUMBIA_SESSION'][APP_PATH][$namespace][$index];
		} else {
			return null;
		}
	}
	/**
	 * Unset una variable de indice
	 *
	 * @param string $index
	 * @param string $namespace
	 */
	static function unset_data($index, $namespace='default')
	{
	  	unset($_SESSION['KUMBIA_SESSION'][APP_PATH][$namespace][$index]);
	}
	/**
	 * Evalua si esta definido un valor dentro de
	 * los valores de sesion
	 *
	 * @param string $index
	 * @param string $namespace
	 * @return boolean
	 */
	static function isset_data($index, $namespace='default'){
		return isset($_SESSION['KUMBIA_SESSION'][APP_PATH][$namespace][$index]);
	}
}