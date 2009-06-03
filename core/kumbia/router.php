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
	 */
	static private $vars = array('route' => '', //Ruta pasada en el GET
				     'module' => '', //Nombre del modulo actual
				     'controller' => '', //Nombre del controlador actual
				     'action' => 'index', //Nombre de la acción actual, por defecto index
				     'id' => '', //Nombre del primer parametro despues de action
				     'parameters' => array(), //Lista los parametros adicionales de la URL
				     'all_parameters' => array(), //Lista de Todos los parametros de la URL
				     'routed' => false //Indica si esta pendiente la ejecución de una ruta por parte del dispatcher
				     );
	/**
	 * Detector de enrutamiento ciclico
	 */
	static private $routed_cyclic;
	
	/**
	 * Toma $url y la descompone en aplicacion, (modulo), controlador, accion y argumentos
	 *
	 * @param string $url
	 */
	static function rewrite($url){
		/**
		 * Valor por defecto
		 */
		self::$vars['route'] = $url;
		 //Si esta vacio (es root)
		if (!$url) { $url = '/'; }
		
		//Miro si esta routed
		$url=self::if_routed($url);
		
		// Se limpian los parametros por seguridad
		$clean = array( '\\', '/../','//');
		$url_sanitize = str_replace($clean,  '', $url, $errors);
		// Si hay intento de hack TODO: añadir la ip y referer en el log
		if($errors) throw new KumbiaException("Posible intento de hack en URL: '$url'");
		
		/**
		 * Limpio la url en caso de que la hallan escrito con el 
		 * ultimo parametro sin valor es decir controller/action/
		 **/
		$url = trim($url,'/');

		/**
		 * Obtengo y asigno todos los parametros de la url
		 **/
		$url_items = explode ('/', $url);
		/**
		* Asigna todos los parametros
		**/
		self::$vars['all_parameters'] = $url_items;
		
		/**
		* El siguiente parametro de la url es un modulo?
		**/
		$item = current($url_items);
		if(is_dir(APP_PATH . "controllers/$item")) {
			self::$vars['module'] = current($url_items);
			
		    // Si no hay mas parametros sale y pone index como controlador
			if (next($url_items) === FALSE) {
				self::$vars['controller'] = 'index';
				return;
			}       
		}       
		       
		/**
		 * Controlador
		 */
		self::$vars['controller'] = current($url_items);
		// Si no hay mas parametros sale
		if (next($url_items) === FALSE) {
			return;
		}       
			
		/**
		* Accion
		*/
		self::$vars['action'] = current($url_items);
		// Si no hay mas parametros sale
		if (next($url_items) === FALSE) {
			return;
		}
		
		/**
		*  id
		*/
		self::$vars['id'] = current($url_items);
		
		/**
		*  Creo los parametros y los paso, depues elimino el $url_items
		*/
		$key = key($url_items);
		$rest = count($url_items) - $key;
		$parameters = array_slice($url_items, $key, $rest);
		
		self::$vars['parameters'] = $parameters;
		unset ($url_items);
	}
	
	/**
 	 * Busca en la tabla de entutamiento si hay una ruta en config/routes.ini
 	 * para el controlador, accion, id actual
 	 * 
	 */
	static function if_routed($url){
		$routes = Config::read('routes.ini');
		$routes = $routes['routes'];
		
		/**
		 * Si existe una ruta exacta la devuelve
		 **/
		if(isset($routes[$url])){
			return $routes[$url];
		}

		/**
		 * Si existe una ruta con el comodin * al final
		 **/
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
	public static function get_routed(){
		return self::$vars['routed'];
	}

	/**
	 * Devuelve el nombre del modulo actual
	 *
	 * @return string
	 */
	public static function get_module(){
		return self::$vars['module'];
	}

	/**
	 * Devuelve el nombre del controlador actual
	 *
	 * @return string
	 */
	public static function get_controller(){
		return self::$vars['controller'];
	}

	/**
	 * Devuelve el nombre del controlador actual
	 *
	 * @return string
	 */
	public static function get_action(){
		return self::$vars['action'];
	}

	/**
	 * Devuelve el primer parametro (id)
	 *
	 * @return mixed
	 */
	public static function get_id(){
		return self::$vars['id'];
	}

	/**
	 * Devuelve los parametros de la ruta
	 *
	 * @return array
	 */
	public static function get_parameters(){
		return self::$vars['parameters'];
	}

	/**
	 * Devuelve los parametros de la ruta
	 *
	 * @return array
	 */
	public static function get_all_parameters(){
		return self::$vars['all_parameters'];
	}

	/**
	 * Establece el estado del Router
	 *
	 */
	public static function set_routed($value){
		self::$vars['routed'] = $value;
	}

	/**
	 * Enruta el controlador actual a otro controlador, &oacute; a otra acción
	 * Ej:
	 * <code>
	 * kumbia::route_to(["module: modulo"], "controller: nombre", ["action: accion"], ["id: id"])
	 * </code>
	 *
	 * @return null
	 */
	static public function route_to(){
		self::$vars['routed'] = false;
		$cyclic_routing = false;
		$url = get_params(func_get_args());
		//print_r ($url);
		if(isset($url['module'])){
			if(self::$vars['module']==$url['module']){
				$cyclic_routing = true;
			}
			/**
			 * Verifico para asignar correctamente los parametros en all_parameters,
			 * efectuando los debidos corrimientos de ser necesario
			 **/
			if(self::$vars['module']) {
				self::$vars['all_parameters'][0] = $url['module'];
			} else {
				array_unshift(self::$vars['all_parameters'], $url['module']);
			}
			
			self::$vars['module'] = $url['module'];
			self::$vars['controller'] = 'index';
			self::$vars['action'] = "index";
			self::$vars['routed'] = true;
		}
		if(isset($url['controller'])){
			if(self::$vars['controller']==$url['controller']){
				$cyclic_routing = true;
			}
			self::$vars['controller'] = $url['controller'];
			
			/**
			 * Verifico para asignar correctamente los parametros en all_parameters,
			 * efectuando los debidos corrimientos de ser necesario
			 **/
			if(self::$vars['module']) {
				self::$vars['all_parameters'][1] = $url['controller'];
			} else {
				self::$vars['all_parameters'][0] = $url['controller'];
			}
			
			self::$vars['action'] = "index";
			self::$vars['routed'] = true;
			
			$app_controller = util::camelcase($url['controller'])."Controller";
		}
		if(isset($url['action'])){
			if(self::$vars['action']==$url['action']){
				$cyclic_routing = true;
			}
			self::$vars['action'] = $url['action'];
			/**
			 * Verifico para asignar correctamente los parametros en all_parameters,
			 * efectuando los debidos corrimientos de ser necesario
			 **/
			if(self::$vars['module']) {
				self::$vars['all_parameters'][2] = $url['action'];
			} else {
				self::$vars['all_parameters'][1] = $url['action'];
			}

			self::$vars['routed'] = true;
		}
		if(isset($url['id'])){
			if(self::$vars['id']==$url['id']){
				$cyclic_routing = true;
			}
			self::$vars['id'] = $url['id'];
			/**
			 * Verifico para asignar correctamente los parametros en all_parameters,
			 * efectuando los debidos corrimientos de ser necesario
			 **/
			if(self::$vars['module']) {
				self::$vars['all_parameters'][3] = $url['action'];
			} else {
				self::$vars['all_parameters'][2] = $url['action'];
			}

			self::$vars['parameters'][0] = $url['id'];
			self::$vars['routed'] = true;
		}
		
		if($cyclic_routing){
			self::$routed_cyclic++;
			if(self::$routed_cyclic>=1000){
				throw new KumbiaException("Se ha detectado un enrutamiento ciclico. Esto puede causar problemas de estabilidad", 1000);
			}
		} else {
			self::$routed_cyclic = 0;
		}
		return null;
	}

	/**
	 * Envia el array con todas las variables del router
	 *
	 * @return array
	 **/
	static public function get_vars($var=null) {
		if($var){
			return self::$vars[$var];
		} else {
			return self::$vars;
		}
	}
}
