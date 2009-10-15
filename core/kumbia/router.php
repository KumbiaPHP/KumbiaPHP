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
					 'route' => '', //Ruta pasada en el GET
				     'module' => '', //Nombre del modulo actual
				     'controller' => '', //Nombre del controlador actual
				     'action' => 'index', //Nombre de la acción actual, por defecto index
				     'parameters' => array(), //Lista los parametros adicionales de la URL
				     'routed' => false //Indica si esta pendiente la ejecución de una ruta por parte del dispatcher
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
		
		//Miro si esta routed
		$url = self::ifRouted($url);
		
		// Se limpian los parametros por seguridad
		$clean = array( '\\', '/../','//');
		$url_sanitize = str_replace($clean,  '', $url, $errors);
        
		// Si hay intento de hack TODO: añadir la ip y referer en el log
		if($errors) throw new KumbiaException("Posible intento de hack en URL: '$url'");
		
		//Limpio la url en caso de que la hallan escrito con el ultimo parametro sin valor es decir controller/action/
		$url = trim($url,'/');

		// Obtengo y asigno todos los parametros de la url
		$url_items = explode ('/', $url);
		
		// El primer parametro de la url es un modulo?
		$item = current($url_items);
		if(is_dir(APP_PATH . "controllers/$item")) {
			self::$_vars['module'] = current($url_items);
			
		    // Si no hay mas parametros sale y pone index como controlador
			if (next($url_items) === FALSE) {
				self::$_vars['controller'] = 'index';
				return;
			}       
		}       
		       
		// Controlador
		self::$_vars['controller'] = current($url_items);
		// Si no hay mas parametros sale
		if (next($url_items) === FALSE) {
			return;
		}       
			
		// Accion
		self::$_vars['action'] = current($url_items);

		// Si no hay mas parametros sale
		if (next($url_items) === FALSE) {
			return;
		}
		
		// id
		//self::$_vars['id'] = current($url_items);
		
		// Crea los parametros y los pasa, depues elimina el $url_items
		$key = key($url_items);
		$rest = count($url_items) - $key;
		$parameters = array_slice($url_items, $key, $rest);
		
		self::$_vars['parameters'] = $parameters;
		unset ($url_items);
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
			if (strripos($key,'*',-1)){
				$key = substr($key, 0, -1);
				$key = str_replace('/','\/',$key);
				$pattern = '/^'.$key.'(.*)/';
                
				if (preg_match($pattern, $url, $match)){
                    $url = str_replace('*', $match[1], $val);
                    return $url ;	
				}
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
	public static function setRouted($value){
		self::$_vars['routed'] = $value;
	}

	/**
	 * Enruta el controlador actual a otro módulo, controlador, o a otra acción
	 * Ej:
	 * <code>kumbia::route_to(["module: modulo"], "controller: nombre", ["action: accion"], ["parameters: xxx/xxx/..."])</code>
	 *
	 */
	public static function route_to()
    {
		
		static $cyclic = 0;
		self::$_vars['routed'] = false;

		$cyclic_routing = false;
		$url = Util::getParams(func_get_args());
		
		//print_r ($url);
		if(isset($url['module'])){
			self::$_vars['module'] = $url['module'];
			self::$_vars['controller'] = 'index';
			self::$_vars['action'] = 'index';
			self::$_vars['routed'] = true;
		}
		
		if(isset($url['controller'])){
			self::$_vars['controller'] = $url['controller'];
			self::$_vars['action'] = "index";
			self::$_vars['routed'] = true;
			
			//$app_controller = util::camelcase($url['controller'])."Controller";
		}
		
		if(isset($url['action'])){
			self::$_vars['action'] = $url['action'];
			self::$_vars['routed'] = true;
		}
		
		if(isset($url['parameters'])){
			self::$_vars['parameters'] = explode('/',$url['parameters']);
			self::$_vars['routed'] = true;
		}elseif (isset($url['id'])){
			// Deprecated
			self::$_vars['parameters'] = array($url['id']);
			self::$_vars['routed'] = true;
		} else {
			self::$_vars['parameters'] = array();
			self::$_vars['routed'] = true;
		}
		
		$cyclic++;
		if($cyclic>=1000){
			throw new KumbiaException("Se ha detectado un enrutamiento cíclico. Esto puede causar problemas de estabilidad");
		}
		
		//return null;
	}

	/**
	 * Envia el valor de un atributo o el array con todos los atributos y sus valores del router
	 * Mirar el atributo vars del router
	 * ej.
	 * <code>kumbia::get()</code>
	 * 
	 * @param ninguno
	 * @return array con todas los atributos y sus valores
	 *
	 * ej.
	 * <code>kumbia::get('controller')</code>
	 * 
	 * @param string  un atributo: route, module, controller, action, parameters o routed
	 * @return string con el valor del atributo
	 **/
	public static function get($var=null) 
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
	public static function redirect($route, $seconds=null)
    {
		$route = PUBLIC_PATH . ltrim($route,'/');
		if(headers_sent() || ($seconds)){
			echo "
				<script type='text/javascript'>
					window.setTimeout(\"window.location=\"$route\", $seconds*1000);
				</script>\n";
		} else {
			header("Location: $route");
			echo 'Redirect to ', $route;
		}
	}
}