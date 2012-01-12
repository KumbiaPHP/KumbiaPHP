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
 * Clase que Actua como router del Front-Controller
 *
 * @category   Kumbia
 * @package    Router
 * @copyright  Copyright (c) 2005-2009 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */
final class Router
{
	/**
	 * Array estatico con las variables del router
     *
     * @var array
	 */
	private static $_vars = array(
					 'route' => NULL, //Ruta pasada en el GET
				     'module' => NULL, //Nombre del modulo actual
				     'controller' => 'index', //Nombre del controlador actual
				     'action' => 'index', //Nombre de la acción actual, por defecto index
				     'parameters' => array(), //Lista los parametros adicionales de la URL
				     'routed' => FALSE, //Indica si esta pendiente la ejecución de una ruta por parte del dispatcher
					 'controller_path' => 'index'
				     );

	/**
	 * Toma $url y la descompone en (modulo), controlador, accion y argumentos
	 *
	 * @param string $url
	 */
	public static function rewrite($url)
    {
		//Valor por defecto
		self::$_vars['route'] = $url;

		// Se miran los parametros por seguridad
		str_replace(array( '\\', '/../','//'),  '', $url, $errors);
		// Si hay intento de hack TODO: añadir la ip y referer en el log
		if($errors) throw new KumbiaException("Posible intento de hack en URL: '$url'");

		//Si config.ini tiene routes activados, mira si esta routed
		if(Config::get('config.application.routes')){
			$url = self::ifRouted($url);
		}
		if($url == '/'){
			return self::$_vars;
		}
		//Se limpia la url, en caso de que la hallan escrito con el último parámetro sin valor, es decir controller/action/
		// Obtiene y asigna todos los parámetros de la url
		$url_items = explode ('/', trim($url,'/'));

		// El primer parametro de la url es un módulo?
		if(is_dir(APP_PATH . "controllers/$url_items[0]")) {
			self::$_vars['module'] = $url_items[0];

		    // Si no hay mas parametros sale
			if (next($url_items) === FALSE) {
				self::$_vars['controller_path'] = "$url_items[0]/index";
				return self::$_vars;
			}
		}

		// Controlador
		self::$_vars['controller'] = current($url_items);
		self::$_vars['controller_path'] = (self::$_vars['module']) ? "$url_items[0]/$url_items[1]" : current($url_items);
		// Si no hay mas parametros sale
		if (next($url_items) === FALSE) {
			return self::$_vars;
		}

		// Acción
		self::$_vars['action'] = current($url_items);
		// Si no hay mas parametros sale
		if (next($url_items) === FALSE) {
			return self::$_vars;
		}

		// Crea los parámetros y los pasa
		self::$_vars['parameters'] = array_slice($url_items, key($url_items));
		return self::$_vars;
	}

	/**
 	 * Busca en la tabla de entutamiento si hay una ruta en config/routes.ini
 	 * para el controlador, accion, id actual
 	 *
	 */
	private static function ifRouted($url)
	{
		$routes = Config::read('routes');
		$routes = $routes['routes'];

		// Si existe una ruta exacta la devuelve
		if(isset($routes[$url])){
			return $routes[$url];
		}

		// Si existe una ruta con el comodin * crea la nueva ruta
		foreach ($routes as $key => $val) {
			if($key == '/*'){
				return rtrim($val,'*').$url;
			}

			if (strripos($key,'*',-1)){
				$key = rtrim($key, '*');
				if(strncmp($url, $key, strlen($key)) == 0) return str_replace($key, rtrim($val,'*'), $url);
			}
		}

		return $url;
	}

	/**
	 * Devuelve el estado del router
	 *
	 * @return boolean
	 */
	public static function getRouted()
	{
		return self::$_vars['routed'];
	}

	/**
	 * Establece el estado del Router
	 *
	 */
	public static function setRouted($value)
	{
		self::$_vars['routed'] = $value;
	}

	/**
	 * Enruta el controlador actual a otro módulo, controlador, o a otra acción
	 * Ej:
	 * <code>Router::route_to(["module: modulo"], "controller: nombre", ["action: accion"], ["parameters: xxx/xxx/..."])</code>
	 *
	 */
	public static function route_to()
	{

		static $cyclic = 0;
		self::$_vars['routed'] = TRUE;
		$url = Util::getParams(func_get_args());

		if(isset($url['module'])){
			self::$_vars['module'] = $url['module'];
			self::$_vars['controller'] = 'index';
			self::$_vars['action'] = 'index';
			self::$_vars['parameters'] = array();
			self::$_vars['controller_path'] = $url['module']. '/index';
		}

		if(isset($url['controller'])){
			self::$_vars['controller'] = $url['controller'];
			self::$_vars['action'] = 'index';
			self::$_vars['parameters'] = array();
			self::$_vars['controller_path'] = (isset($url['module'])) ? $url['module'].'/'.$url['controller'] : $url['controller'];
		}

		if(isset($url['action'])){
			self::$_vars['action'] = $url['action'];
			self::$_vars['parameters'] = array();
		}

		if(isset($url['parameters'])){
			self::$_vars['parameters'] = explode('/',$url['parameters']);
		}elseif (isset($url['id'])){
			// Deprecated
			self::$_vars['parameters'] = array($url['id']);
		} else {
			self::$_vars['parameters'] = array();
		}

		if(++$cyclic > 1000) throw new KumbiaException('Se ha detectado un enrutamiento cíclico. Esto puede causar problemas de estabilidad');
	}

	/**
	 * Envia el valor de un atributo o el array con todos los atributos y sus valores del router
	 * Mirar el atributo vars del router
	 * ej.
	 * <code>Router::get()</code>
	 *
	 * @param ninguno
	 * @return array con todas los atributos y sus valores
	 *
	 * ej.
	 * <code>Router::get('controller')</code>
	 *
	 * @param string  un atributo: route, module, controller, action, parameters o routed
	 * @return string con el valor del atributo
	 **/
	public static function get($var = NULL)
	{
		if($var){
			return self::$_vars[$var];
		} else {
			return self::$_vars;
		}
	}

	/**
	 * Redirecciona la ejecución a otro controlador en un
	 * tiempo de ejecución determinado
	 *
	 * @param string $route
	 * @param integer $seconds
	 */
	public static function redirect($route = NULL, $seconds = NULL)
	{
		if(!$route) $route = self::$_vars['controller_path'].'/';
		$route = PUBLIC_PATH . ltrim($route,'/');
		if($seconds){
			header("Refresh: $seconds; url=$route");
		} else {
			header("Location: $route");
			$_SESSION['KUMBIA.CONTENT'] = ob_get_clean();
			View::select(NULL, NULL);
		}
	}
	
	
	/**
	 * Redirecciona la ejecución a una accion del controlador actual en un
	 * tiempo de ejecución determinado
	 * 
	 * @param string $action
	 * @param integer $seconds
	 */
	public static function toAction($action, $seconds = NULL)
	{
		self::redirect(self::$_vars['controller_path'] . "/$action", $seconds);
	}
}
