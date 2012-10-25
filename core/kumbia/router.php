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
 * @category   Kumbia
 * @package    Router
 * @copyright  Copyright (c) 2005-2012 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

/**
 * Clase que Actua como router del Front-Controller
 *
 * Manejo de redirecciones de peticiones
 * Contiene información referente a la url de
 * la petición ( modudo, controlador, acción, parametros, etc )
 *
 * @category   Kumbia
 * @package    Router
 */
final class Router
{
    /**
     * Array estatico con las variables del router
     *
     * @var array
     */
    private static $_vars = array(
        'route' => null, //Ruta pasada en el GET
        'module' => null, //Nombre del modulo actual
        'controller' => 'index', //Nombre del controlador actual
        'action' => 'index', //Nombre de la acción actual, por defecto index
        'parameters' => array(), //Lista los parametros adicionales de la URL
        'controller_path' => 'index'
    );

	/**
	 * Indica si esta pendiente la ejecución de una ruta por parte del dispatcher
	 * 
	 * @var boolean
	 */
	private static $_routed = false;

    /**
     * Busca en la tabla de entutamiento si hay una ruta en config/routes.ini
     * para el controlador, accion, id actual
     *
     */
    private static function _ifRouted($url)
    {
        $routes = Config::read('routes');
        $routes = $routes['routes'];

        // Si existe una ruta exacta la devuelve
        if (isset($routes[$url])) {
            return $routes[$url];
        }

        // Si existe una ruta con el comodin * crea la nueva ruta
        foreach ($routes as $key => $val) {
            if ($key == '/*') {
                return rtrim($val, '*') . $url;
            }

            if (strripos($key, '*', -1)) {
                $key = rtrim($key, '*');
                if (strncmp($url, $key, strlen($key)) == 0)
                    return str_replace($key, rtrim($val, '*'), $url);
            }
        }

        return $url;
    }
    
	/**
     * Toma $url y la descompone en (modulo), controlador, accion y argumentos
     *
     * @param string $url
     */
    private static function _rewrite($url)
    {
        //Valor por defecto
        self::$_vars['route'] = $url;

        // Se miran los parametros por seguridad
        str_replace(array('\\', '/../', '//'), '', $url, $errors);
        
        // Si hay intento de hack TODO: añadir la ip y referer en el log
        if ($errors) throw new KumbiaException("Posible intento de hack en URL: '$url'");

        //Si config.ini tiene routes activados, mira si esta routed
        if (Config::get('config.application.routes')) {
            $url = self::_ifRouted($url);
        }
        
        if ($url == '/') return;

        //Se limpia la url, en caso de que la hallan escrito con el último parámetro sin valor, es decir controller/action/
        // Obtiene y asigna todos los parámetros de la url
        $url_items = explode('/', trim($url, '/'));

        // El primer parametro de la url es un módulo?
        if (is_dir(APP_PATH . "controllers/$url_items[0]")) {
            self::$_vars['module'] = $url_items[0];

            // Si no hay mas parametros sale
            if (next($url_items) === false) {
                self::$_vars['controller_path'] = "$url_items[0]/index";
                return;
            }
        }

        // Controlador
        self::$_vars['controller'] = current($url_items);
        self::$_vars['controller_path'] = (self::$_vars['module']) ? "$url_items[0]/$url_items[1]" : current($url_items);
        
        // Si no hay mas parametros sale
        if (next($url_items) === false) return;

        // Acción
        self::$_vars['action'] = current($url_items);
        
        // Si no hay mas parametros sale
        if (next($url_items) === false) return;

        // Crea los parámetros y los pasa
        self::$_vars['parameters'] = array_slice($url_items, key($url_items));
    }
    
	/**
     * Realiza el dispatch de la ruta actual
     *
     * @return Controller
     */
    private static function _dispatch()
    {
		// Extrae las variables para manipularlas facilmente
        extract(self::$_vars, EXTR_OVERWRITE);

        if (!include_once APP_PATH . "controllers/$controller_path" . '_controller.php')
            throw new KumbiaException(null, 'no_controller');

        //Asigna el controlador activo
        $app_controller = Util::camelcase($controller) . 'Controller';
        $cont = new $app_controller($module, $controller, $action, $parameters);
        View::select($action);
        View::setPath($controller_path);

        // Se ejecutan los filtros initialize y before
        if ($cont->k_callback(true) === false) {
            return $cont;
        }

        //Se ejecuta el metodo con el nombre de la accion
        //en la clase de acuerdo al convenio
        if (!method_exists($cont, $cont->action_name)) {
            throw new KumbiaException(null, 'no_action');
        }

        //Obteniendo el metodo
        $reflectionMethod = new ReflectionMethod($cont, $cont->action_name);

        //k_callback y __constructor metodo reservado
        if ($reflectionMethod->name == 'k_callback' || $reflectionMethod->isConstructor()) {
            throw new KumbiaException('Esta intentando ejecutar un método reservado de KumbiaPHP');
        }

        //se verifica que el metodo sea public
        if (!$reflectionMethod->isPublic()) {
            throw new KumbiaException(null, 'no_action');
        }

        //se verifica que los parametros que recibe
        //la action sea la cantidad correcta
        $num_params = count($cont->parameters);
        if ($cont->limit_params && ($num_params < $reflectionMethod->getNumberOfRequiredParameters() ||
                $num_params > $reflectionMethod->getNumberOfParameters())) {
            throw new KumbiaException("Número de parámetros erróneo para ejecutar la acción \"{$cont->action_name}\" en el controlador \"$controller\"");
        }
        $reflectionMethod->invokeArgs($cont, $cont->parameters);

        //Corre los filtros after y finalize
        $cont->k_callback();

        //Si esta routed volver a ejecutar
        if (self::$_routed) {
            self::$_routed = false;
            return self::_dispatch(); // Vuelve a ejecutar el dispatcher
        }

        return $cont;
    }

	/**
	 * Ejecuta una url
	 * 
	 * @param string $url
	 * @return Controller
	 */
	public static function execute($url)
	{
		// Descompone la url
		self::_rewrite($url);
		// Despacha la ruta actual
		return self::_dispatch();
	}

    /**
     * Enruta el controlador actual a otro módulo, controlador, o a otra acción
     * 
     * @example
     * Router::route_to("module: modulo", "controller: nombre", "action: accion", "parameters: 1/2")
     */
    public static function route_to()
    {
        static $cyclic = 0;
        self::$_routed = true;
        $url = Util::getParams(func_get_args());

        if (isset($url['module'])) {
            self::$_vars['module'] = $url['module'];
            self::$_vars['controller'] = 'index';
            self::$_vars['action'] = 'index';
            self::$_vars['parameters'] = array();
            self::$_vars['controller_path'] = $url['module'] . '/index';
        }

        if (isset($url['controller'])) {
            self::$_vars['controller'] = $url['controller'];
            self::$_vars['action'] = 'index';
            self::$_vars['parameters'] = array();
            self::$_vars['controller_path'] = (isset($url['module'])) ? $url['module'] . '/' . $url['controller'] : $url['controller'];
        }

        if (isset($url['action'])) {
            self::$_vars['action'] = $url['action'];
            self::$_vars['parameters'] = array();
        }

        if (isset($url['parameters'])) {
            self::$_vars['parameters'] = explode('/', $url['parameters']);
        } elseif (isset($url['id'])) {
            // Deprecated
            self::$_vars['parameters'] = array($url['id']);
        } else {
            self::$_vars['parameters'] = array();
        }

        if (++$cyclic > 1000)
            throw new KumbiaException('Se ha detectado un enrutamiento cíclico. Esto puede causar problemas de estabilidad');
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
     */
    public static function get($var = null)
    {
        if ($var) {
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
    public static function redirect($route = null, $seconds = null)
    {
        if (!$route)
            $route = self::$_vars['controller_path'] . '/';
			$route = PUBLIC_PATH . ltrim($route, '/');
        if ($seconds) {
            header("Refresh: $seconds; url=$route");
        } else {
            header("Location: $route");
            $_SESSION['KUMBIA.CONTENT'] = ob_get_clean();
            View::select(null, null);
        }
    }

    /**
     * Redirecciona la ejecución a una accion del controlador actual en un
     * tiempo de ejecución determinado
     * 
     * @param string $action
     * @param integer $seconds
     */
    public static function toAction($action, $seconds = null)
    {
        self::redirect(self::$_vars['controller_path'] . "/$action", $seconds);
    }
}
