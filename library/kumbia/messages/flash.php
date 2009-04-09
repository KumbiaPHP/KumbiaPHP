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
 * @category   Kumbia
 * @package Messages
 * @copyright  Copyright (c) 2005-2007 Andres Felipe Gutierrez (andresfelipe at vagoogle.net)
 * @license    http://www.kumbia.org/license.txt GNU/GPL
 */

/**
 * Flash Es la clase standard para enviar advertencias,
 * informacion y errores a la pantalla
 *
 * @category Kumbia
 * @package Messages
 * @abstract
 * @copyright Copyright (c) 2005-2007 Andres Felipe Gutierrez (andresfelipe at vagoogle.net)
 * @copyright Copyright (c) 2008-2008 Joan Miquel Abrines (joanhey2 at gmail.com)
 * @license http://www.kumbia.org/license.txt GNU/GPL
 * @access public
 */
abstract class Flash {

	/**
	 * Visualiza un mensaje flash
	 *
	 * @param string $name	Para tipo de mensaje y para CSS class='$name'.
	 * @param string $msg 	Mensaje a mostrar
	 */
	public static function show($name,$msg)
	{
		if(isset($_SERVER['SERVER_SOFTWARE'])){
    			echo '<div class="' , $name , '">' , $msg , '</div>' , "\n";
		} else {
			echo $name , '*** ' , strip_tags($msg) , "\n";
		}
	}
	
	/**
	 * Visualiza un mensaje de error
	 *
	 * @param string $err
	 */
	public static function error($err)
	{     
		return self::show('error_message',$err);
	}

	/**
	 * Visualiza una alerta de Error JavaScript
	 *
	 * @param string $err
	 */
	public static function jerror($err)
	{
        	formsPrint("\r\nalert(\"$err\")\r\n");
	}

	/**
	 * Visualiza informacion en pantalla
	 *
	 * @param string $msg
	 */
	public static function notice($msg)
	{
		return self::show('notice_message',$msg);
	}

	/**
	 * Visualiza informacion de Suceso en pantalla
	 *
	 * @param string $msg
	 */
	public static function success($msg)
	{
		return self::show('success_message',$msg);
	}

	/**
	 * Visualiza un mensaje de advertencia en pantalla
	 *
	 * @param string $msg
	 */
	public static function warning($msg)
	{
		return self::show('warning_message',$msg);
	}

	/**
	 * Visualiza un Mensaje del interactiveBuilder
	 *
	 * @param string $msg
	 */
	public static function interactive($msg)
	{
		return self::show('interactive_message',$msg);
	}

	/**
	 * Visualiza un Mensaje de Kumbia
	 *
	 * @param string $msg
	 * @return 
	 */
	public static function kumbia_error($msg)
	{
		return self::show('error_message','<em>KumbiaError:</em> '.$msg);
	}
	
}