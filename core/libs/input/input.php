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
 * @package    Input
 * @copyright  Copyright (c) 2005-2012 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */
class Input 
{
	/**
	 * Verifica o obtiene el metodo de la peticion
	 *
	 * @param string $method
	 * @return mixed
	 */
	public static function is($method = NULL)
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
	 * Obtiene un valor del arreglo $_POST
	 *
	 * @param string $var
	 * @return mixed
	 */
	public static function post($var)
	{
		return filter_has_var(INPUT_POST, $var) ? $_POST[$var] : NULL;
	}

	/**
	 * Obtiene un valor del arreglo $_GET, aplica el filtro FILTER_SANITIZE_STRING
	 * por defecto
	 *
	 * @param string $var
	 * @return mixed
	 */
	public static function get($var = NULL)
	{
		if($var){
			$value = filter_has_var(INPUT_GET, $var) ? filter_input(INPUT_GET, $var, FILTER_SANITIZE_STRING) : NULL;
		} else {
			$value = filter_input_array (INPUT_GET, FILTER_SANITIZE_STRING);
		}
			
		return $value;
	}

	/**
	 * Obtiene un valor del arreglo $_REQUEST
 	 *
	 * @param string $var
	 * @return mixed
	 */
	public static function request($var)
	{
		return isset($_REQUEST[$var]) ? $_REQUEST[$var] : NULL;
	}

	/**
	 * Verifica si existe el elemento indicado en $_POST
	 *
	 * @param string $var elemento a verificar
	 * @return boolean
	 */
	public static function hasPost($var) 
	{
		return filter_has_var(INPUT_POST, $var);
	}

	/**
	 * Verifica si existe el elemento indicado en $_GET
	 *
	 * @param string $var elemento a verificar
	 * @return boolean
	 */
	public static function hasGet($var)
	{
		return filter_has_var(INPUT_GET, $var);
	}

	/**
	 * Verifica si existe el elemento indicado en $_REQUEST
	 *
	 * @param string $var elemento a verificar
	 * @return boolean
	 */
	public static function hasRequest($var) 
	{
		return isset($_REQUEST[$var]);
	}
        
	/**
	 * Elimina elemento indicado en $_POST
	 *
	 * @param string $var elemento a verificar
	 * @return boolean
	 */
	public static function delete($var = NULL) 
	{
		if($var){
			unset($_POST[$var]);
		} else {
			unset($_POST);
		}
	}
	
    /**
    * Permite Obtener el Agente de Usuario (User Agent)
    * @return String
    */
    public static function userAgent(){
        return $_SERVER['HTTP_USER_AGENT'];
    }

    /**
    * Permite obtene la IP del cliente, aún cuando usa proxy
    * @return String
    */
    public static function ip(){
        if (!empty($_SERVER['HTTP_CLIENT_IP'])){
            return $_SERVER['HTTP_CLIENT_IP'];
        }elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }else{
            return $_SERVER['REMOTE_ADDR'];    
        }
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
		//TODO
    }

    
}
