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
 * @see SessionNamespace
 */
require_once CORE_PATH.'library/kumbia/session/namespace/namespace.php';

/**
 * @see SessionRecord
 */
require_once CORE_PATH.'library/kumbia/session/session_record/session_record.php';

/**
 * Modelo orientado a objetos para el acceso a datos en Sesiones
 *
 * @category Kumbia
 * @package Session
 * @copyright Copyright (c) 2005-2007 Andres Felipe Gutierrez (andresfelipe at vagoogle.net)
 * @license http://www.kumbia.org/license.txt GNU/GPL
 * @access public
 */
class Session {

	/**
	 * Crear ó especificar el valor para un indice de la sesión
	 * actual
	 *
	 * @param string $index
	 * @param mixed $value
	 */
	static function set_data($index, $value){
	  	$_SESSION['session_data'][$index] = $value;
	}

	/**
	 * Obtener el valor para un indice de la sesión
	 *
	 * @param string $index
	 * @return mixed
	 */
	static function get_data($index){
		if(isset($_SESSION['session_data'][$index])){
	  		return $_SESSION['session_data'][$index];
		} else {
			return null;
		}
	}

	/**
	 * Crear ó especificar el valor para un indice de la sesión
	 * actual
	 *
	 * @param string $index
	 * @param mixed $value
	 */
	static function set($index, $value){
	  	$_SESSION['session_data'][$index] = $value;
	}

	/**
	 * Obtener el valor para un indice de la sesión
	 *
	 * @param string $index
	 * @return mixed
	 */
	static function get($index){
	  	if(isset($_SESSION['session_data'][$index])){
	  		return $_SESSION['session_data'][$index];
		} else {
			return null;
		}
	}

	/**
	 * Unset una variable de indice
	 *
	 */
	static function unset_data(){
	  	$lista_args = func_get_args();
	  	if($lista_args){
  	  		foreach($lista_args as $arg){
			  	unset($_SESSION['session_data'][$arg]);
			}
		}
	}

	/**
	 * Evalua si esta definido un valor dentro de
	 * los valores de sesion
	 *
	 * @param string $index
	 * @return mixed
	 */
	static function isset_data($index){
		return isset($_SESSION['session_data'][$index]);
	}
}
