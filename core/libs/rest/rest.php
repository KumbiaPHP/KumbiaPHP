<?php
/**
 * Warning! This IS A ALPHA VERSION NOT USE IN PRODUCTION APP!
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
 * Rest. Clase estática para el manejo de API basada en REST
 * 
 * @category   Kumbia
 * @package    Controller 
 * @copyright  Copyright (c) 2005-2009 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */
class Rest{
	
	/**
	 * Array con los tipos de datos soportados
	 */
	private static $fSupport = array('json', 'text', 'html', 'xml', 'cvs', 'php');
	
	/**
	 * Metodo de petición (GET, POST, PUT, DELETE)
	 */
	private static $method = null;
	
	/**
	 * Establece los tipos de respuesta aceptados
	 */
	static public function accept($accept){
		 $fSupport =  is_array($accept) ? $accept : explode(',', $accept);
	}
	
	/**
	 * Define el inicio de un servicio REST
	 */
	static public function init(){
		/*Compruebo el método de petición*/
		self::$method = strtolower($_SERVER['REQUEST_METHOD']);
		$format = explode(',', $_SERVER['HTTP_ACCEPT']);
		while($f = array_shift($format)){
			$f = str_replace(array('text/', 'application/'), '', $f);
			if(in_array($f, self::$fSupport))
				break;
		}
		if($f== null){
			return 'error';
		}else{
			View::response($f);
			return self::$method;
		}
    }
    
    /**
     * Retorna los parametros de la petición el función del método
     * de la petición
     */
    static function param(){
		$vars = array('post'=>$_POST,
			'get'=> $_GET,
			'put' => putVar()
			);
		return $vars[self::$method];
	}
    
    /**
     * Permite leer las variables pasadas por el método PUT
     * (PHP por defecto no lo soporta 
     */
    static function putVar(){
		$input = file_get_contents('php://input');
		if ( function_exists('mb_parse_str') ) {
			mb_parse_str($input, $output);
		} else {
			parse_str($input, $output);
		}
		return $output;
	}
}
