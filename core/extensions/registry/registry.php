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
 * @package Registry
 * @copyright Copyright (c) 2005-2007 Andres Felipe Gutierrez (andresfelipe at vagoogle.net)
 * @license http://www.kumbia.org/license.txt GNU/GPL
 */

/**
 * Permite almacenar valores durante la ejecución de la aplicacion. Implementa el
 * patrón de diseño Registry
 *
 * @category Kumbia
 * @package Registry
 * @abstract
 * @copyright Copyright (c) 2005-2007 Andres Felipe Gutierrez (andresfelipe at vagoogle.net)
 * @license http://www.kumbia.org/license.txt GNU/GPL
 *
 */
class Registry
{
	/**
	 * Variable donde se guarda el registro
	 *
	 * @var array
	 */
	private static $registry = array();

	/**
	 * Establece un valor del registro
	 *
	 * @param string $index
	 * @param string $value
	 */
	public static function set($index, $value)
	{
		self::$registry[$index] = $value;
	}

	/**
	 * Agrega un valor al registro a uno ya establecido
	 *
	 * @param string $index
	 * @param string $value
	 */
	public static function append($index, $value)
	{
		if(!isset(self::$registry[$index])){
			self::$registry[$index] = array();
		}
		self::$registry[$index][] = $value;
	}


	/**
	 * Agrega un valor al registro al inicio de uno ya establecido
	 *
	 * @param string $index
	 * @param string $value
	 */

	public static function prepend($index, $value)
	{
		if(!isset(self::$registry[$index])){
			self::$registry[$index] = array();
		}
		array_unshift(self::$registry[$index], $value);
	}

	/**
	 * Obtiene un valor del registro
	 *
	 * @param string $index
	 * @return mixed
	 */
	public static function get($index)
	{
		if(isset(self::$registry[$index])){
			return self::$registry[$index];
		} else {
			return null;
		}
	}

}