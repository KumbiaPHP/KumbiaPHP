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
 * @package    Dispatcher 
 * @copyright  Copyright (c) 2005-2012 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

/**
 * Clase para manejar las peticiones de KumbiaPHP Framework
 *
 * @category   Kumbia
 * @package    Dispatcher
 */
final class Dispatcher
{

    /**
     * Objeto del controlador en ejecución
     *
     * @var mixed
     */
    private static $_controller;

    /**
     * Realiza el dispatch de una ruta
     *
     * @return Object
     */
    public static function execute($route)
    {
        extract($route, EXTR_OVERWRITE);

        if (!include_once APP_PATH . "controllers/$controller_path" . '_controller.php')
            throw new KumbiaException(NULL, 'no_controller');

        //Asigna el controlador activo
        $app_controller = Util::camelcase($controller) . 'Controller';
        $cont = self::$_controller = new $app_controller($module, $controller, $action, $parameters);
        View::select($action);
        View::setPath($controller_path);

        // Se ejecutan los filtros initialize y before
        if ($cont->k_callback(TRUE) === FALSE) {
            return $cont;
        }

        //Se ejecuta el metodo con el nombre de la accion
        //en la clase de acuerdo al convenio
        if (!method_exists($cont, $cont->action_name)) {
            throw new KumbiaException(NULL, 'no_action');
        }

        //Obteniendo el metodo
        $reflectionMethod = new ReflectionMethod($cont, $cont->action_name);

        //k_callback y __constructor metodo reservado
        if ($reflectionMethod->name == 'k_callback' || $reflectionMethod->isConstructor()) {
            throw new KumbiaException('Esta intentando ejecutar un método reservado de KumbiaPHP');
        }

        //se verifica que el metodo sea public
        if (!$reflectionMethod->isPublic()) {
            throw new KumbiaException(NULL, 'no_action');
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
        if (Router::getRouted()) {
            Router::setRouted(FALSE);
            return Dispatcher::execute(Router::get()); // Vuelve a ejecutar el dispatcher
        }

        return $cont;
    }

    /**
     * Obtener el controlador en ejecucion
     *
     * @return mixed
     */
    public static function get_controller()
    {
        return self::$_controller;
    }

}
