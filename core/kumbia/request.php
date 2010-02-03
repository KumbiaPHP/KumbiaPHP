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
 * Clase para manejar los datos del request
 * 
 * @category   Kumbia
 * @package    Request
 * @copyright  Copyright (c) 2005-2009 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */
final class Request 
{

        public static function method($method = NULL)
	{
		if($method){			
			return $method == $_SERVER['REQUEST_METHOD'];
		}
		return $_SERVER['REQUEST_METHOD'];
	}
        
        /**
	 * Indica si el request es AJAX
	 *
	 * @return Bolean
	 */
	public static function isAjax()
	{
		return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');
	}
        
        /**
	 * Indica si el request es POST
	 *
	 * @return Bolean
	 */
	public static function isPost()
	{
		return $_SERVER['REQUEST_METHOD'] == 'POST';
	}
        
        /**
	 * Indica si el request es GET
	 *
	 * @return Bolean
	 */
	public static function isGet()
	{
		return $_SERVER['REQUEST_METHOD'] == 'GET';
	}
        
        /**
	 * Indica si el request es PUT
	 *
	 * @return Bolean
	 */
	public static function isPut()
	{
		return $_SERVER['REQUEST_METHOD'] == 'PUT';
	}
        
        /**
	 * Indica si el request es DELETE
	 *
	 * @return Bolean
	 */
	public static function isDelete()
	{
		return $_SERVER['REQUEST_METHOD'] == 'DELETE';
	}
        
        /**
	 * Indica si el request es HEAD
	 *
	 * @return Bolean
	 */
	public static function isHead()
	{
		return $_SERVER['REQUEST_METHOD'] == 'HEAD';
	}
        
	/**
	 * Obtiene un valor del arreglo $_POST
	 *
	 * @param string $var
	 * @return mixed
	 */
	public static function post($var)
	{
		//Verifica si posee el formato form.field, en ese caso accede al array $_POST['form']['field']
		if(stripos($var,'.')) {
                        $var = explode('.', $var);
			return isset($_POST[$var[0]][$var[1]]) ? $_POST[$var[0]][$var[1]] : NULL;
		}
		return isset($_POST[$var]) ? $_POST[$var] : NULL;
	}

	/**
	 * Obtiene un valor del arreglo $_GET
	 *
	 * @param string $var
	 * @return mixed
	 */
	public static function get($var = NULL)
	{	//FILTER_SANITIZE_STRING
		if($var){
			
		} else {
			$value = filter_input_array (INPUT_GET, FILTER_SANITIZE_STRING);
		}
		//$value = filter_has_var(INPUT_GET, $variable) ? $_GET[$variable] : NULL;
			
		return $value;
	}

	/**
	 * Obtiene un valor del arreglo $_REQUEST
 	 *
	 * @param string $var
	 * @return mixed
	 */
	public static function req($var)
        {
		 // Verifica si posee el formato form.field, en ese caso accede al array $_REQUEST['form']['field']
		if(stripos($var,'.')) {
                        $var = explode('.', $var); 
			return isset($_REQUEST[$var[0]][$var[1]]) ? $_REQUEST[$var[0]][$var[1]] : NULL;
		}
		return isset($_REQUEST[$var]) ? $_REQUEST[$var] : NULL;
	}

	/**
	 * Verifica si existe el elemento indicado en $_POST
	 *
	 * @param string $var elemento a verificar
	 * @return boolean
	 **/
	public static function hasPost($var) 
	{
		if(stripos($var,'.')) {
                        $var = explode('.', $var);
			return filter_has_var(INPUT_POST, $var[0][$var[1]]);
		}
		return filter_has_var(INPUT_POST, $var);
	}

	/**
	 * Verifica si existe el elemento indicado en $_GET
	 *
	 * @param string $var elemento a verificar
	 * @return boolean
	 **/
	public static function hasGet($var)
	{
		if(stripos($var,'.')) {
                        $var = explode('.', $var);
			return filter_has_var(INPUT_GET, $var[0][$var[1]]);
		}
		return filter_has_var(INPUT_GET, $var);
	}

	/**
	 * Verifica si existe el elemento indicado en $_REQUEST
	 *
	 * @param string $var elemento a verificar (soporta varios elementos simultaneos)
	 * @return boolean
	 **/

	public static function hasRequest($var) 
	{
		$success = TRUE;
		$args = func_get_args();
		foreach($args as $f) {
			/**
			 * Verifica si posee el formato form.field
			 **/
			$f = explode('.', $f);
			if(count($f)>1 && !isset($_REQUEST[$f[0]][$f[1]]) ) {
				$success = FALSE;
				break;
			} elseif(!isset($_REQUEST[$f[0]])) {
				$success = FALSE;
				break;
			}
		}
		return $success;
	}
        
        /**
	 * Obtiene y filtra un valor del arreglo $_REQUEST
	 * Por defecto, usa SANITIZE
 	 *
	 * @param string $var
	 * @return mixed
	 */
	public static function filter($var)
        {
            if(func_num_args()>1){
			$args = func_get_args();

                if(is_string($args[0])) {
                    return call_user_func_array(array('Filter', 'get'), $args);
                } 
                return call_user_func_array(array('Filter', 'get_array'), $args);
            }
	    return $value;
        }
}