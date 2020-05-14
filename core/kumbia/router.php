<?php
/**
 * KumbiaPHP web & app Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @category   Kumbia
 * @package    Router
 *
 * @copyright  Copyright (c) 2005 - 2020 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
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
class Router
{

    /**
     * Array estático con las variables del router
     *
     * @var array
     */
    protected static $vars = [
        // 'method'          => '', //Método usado GET, POST, ...
        // 'route'           => '', //Ruta pasada URL
        // 'module'          => '', //Nombre del módulo actual
        // 'controller'      => 'index', //Nombre del controlador actual
        // 'action'          => 'index', //Nombre de la acción actual, por defecto index
        // 'parameters'      => [], //Lista los parámetros adicionales de la URL
        // 'controller_path' => 'index'
    ];

    /**
     * Array estático con las variables del router por defecto
     * TODO: Convertir a constante
     * 
     * @var array
     */
    protected static $default = [
        'module'          => '', //Nombre del módulo actual
        'controller'      => 'index', //Nombre del controlador actual, por defecto index
        'action'          => 'index', //Nombre de la acción actual, por defecto index
        'parameters'      => [], //Lista los parámetros adicionales de la URL
        'controller_path' => 'index'
    ];

    /**
     * This is the name of router class
     * @var string
     */
    protected static $router = 'KumbiaRouter';
    //Es el router por defecto

    /**
     * Indica si esta pendiente la ejecución de una ruta por parte del dispatcher
     *
     * @var boolean
     */
    protected static $routed = false;

    /**
     * Procesamiento basico del router
     * @param string $url
     * 
     * @throws KumbiaException
     * @return void
     */
    public static function init($url)
    {
        // Se miran los parámetros por seguridad
        if (stripos($url, '/../') !== false) {
            throw new KumbiaException("Posible intento de hack en URL: '$url'");
        }
        // Si hay intento de hack TODO: añadir la ip y referer en el log
        self::$default['route'] = $url;
        //Método usado
        self::$default['method'] = $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Ejecuta una url
     *
     * @param string $url
     * 
     * @throws KumbiaException
     * @return Controller
     */
    public static function execute($url)
    {
        self::init($url);
        //alias
        $router = self::$router;
        $conf   = Config::get('config.application.routes');
        //Si config.ini tiene routes activados, mira si esta routed
        if ($conf) {
            /*Esta activado el router*/
            /* This if for back compatibility*/
            if ($conf === '1') {
                $url = $router::ifRouted($url);
            } else {
                /*Es otra clase de router*/
                $router = self::$router = $conf;
            }
        }

        // Descompone la url
        self::$vars = $router::rewrite($url) + self::$default;

        // Despacha la ruta actual
        return self::dispatch($router::getController(self::$vars));
    }

    /**
     * Realiza el dispatch de la ruta actual
     * 
     * @param Controller $cont  Controlador a usar
     *
     * @throws KumbiaException
     * @return Controller
     */
    private static function dispatch($cont)
    {
        // Se ejecutan los filtros initialize y before
        if ($cont->k_callback(true) === false) {
            return $cont;
        }

        if (method_exists($cont, $cont->action_name)) {
            if (strcasecmp($cont->action_name, 'k_callback') === 0 ) {
                throw new KumbiaException('Esta intentando ejecutar un método reservado de KumbiaPHP');
            }

            if ($cont->limit_params) { // with variadic php5.6 delete it
                $reflectionMethod = new ReflectionMethod($cont, $cont->action_name);
                $num_params = count($cont->parameters);
                
                if ($num_params < $reflectionMethod->getNumberOfRequiredParameters() ||
                    $num_params > $reflectionMethod->getNumberOfParameters()) {
                        
                    throw new KumbiaException('', 'num_params');   
                }
                        
            }
        }
        
        call_user_func_array([$cont, $cont->action_name], $cont->parameters);

        //Corre los filtros after y finalize
        $cont->k_callback();

        //Si esta routed internamente volver a ejecutar
        self::isRouted();

        return $cont;
    }

    /**
     * Redirecciona la ejecución internamente
     * 
     * @throws KumbiaException
     * @return void
     */
    protected static function isRouted()
    {
        if (self::$routed) {
            self::$routed = false;
            $router = self::$router;
            // Despacha la ruta actual
            self::dispatch($router::getController(self::$vars));
        }
    }

    /**
     * Envia el valor de un atributo o el array con todos los atributos y sus valores del router
     * Mirar el atributo vars del router
     * ej.
     * <code>Router::get()</code>
     *
     * ej.
     * <code>Router::get('controller')</code>
     *
     * @param string $var (opcional) un atributo: route, module, controller, action, parameters o routed
     * 
     * @return array|string con el valor del atributo
     */
    public static function get($var = '')
    {
        return ($var) ? self::$vars[$var] : self::$vars;
    }

    /**
     * Redirecciona la ejecución internamente o externamente con un routes propio
     *
     * @param array $params array de $vars (móddulo, controller, action, params, ...)
     * @param boolean $intern si la redirección es interna
     * 
     * @return void
     */
    public static function to(array $params, $intern = false)
    {
        if ($intern) {
            self::$routed = true;
        }
        self::$vars = $params + self::$default;
    }
}
