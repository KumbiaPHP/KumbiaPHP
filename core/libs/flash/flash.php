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
 * Flash Es la clase standard para enviar advertencias,
 * informacion y errores a la pantalla
 * 
 * @category   Kumbia
 * @package    Flash 
 * @copyright  Copyright (c) 2005-2009 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */
class Flash {

	/**
	 * Visualiza un mensaje flash
	 *
	 * @param string $name	Para tipo de mensaje y para CSS class='$name'.
	 * @param string $msg 	Mensaje a mostrar
	 */
	public static function show($name,$msg)
	{
		if(isset($_SERVER['SERVER_SOFTWARE'])){
    			echo '<div class="' , $name , ' flash">' , $msg , '</div>', PHP_EOL;
		} else {
			echo $name , ': ' , strip_tags($msg) , PHP_EOL;
		}
	}
	
	/**
	 * Visualiza un mensaje de error
	 *
	 * @param string $err
	 */
	public static function error($err)
	{     
		return self::show('error',$err);
	}

	/**
	 * Visualiza un mensaje de advertencia en pantalla
	 *
	 * @param string $msg
	 */
	public static function warning($msg)
	{
		return self::show('warning',$msg);
	}
	
	/**
	 * Visualiza informacion en pantalla
	 *
	 * @param string $msg
	 */
	public static function info($msg)
	{
		return self::show('info',$msg);
	}
	/**
	 * Visualiza informacion de suceso correcto en pantalla
	 *
	 * @param string $msg
	 */
	public static function valid($msg)
	{
		return self::show('valid',$msg);
	}
	
	/**
	 * Visualiza informacion en pantalla
	 *
	 * @param string $msg
	 *
	 * @deprecated  ahora Flah::info()
	 */
	public static function notice($msg)
	{
		return self::show('info',$msg);
	}

	/**
	 * Visualiza informacion de Suceso en pantalla
	 *
	 * @param string $msg
	 *
	 * @deprecated  ahora Flash::valid()
	 */
	public static function success($msg)
	{
		return self::show('valid',$msg);
	}
}
