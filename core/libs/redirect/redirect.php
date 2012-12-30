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
 * Clase para redireccionar peticiones
 *
 * @category   Kumbia
 * @package    Redirect
 */
class Redirect
{
    
    /**
     * Redirecciona la ejecución a otro controlador en un
     * tiempo de ejecución determinado
     *
     * @param string $route ruta a la que será redirigida la petición.
     * @param integer $seconds segundos que se esperarán antes de redirigir
     * @param integer $statusCode código http de la respuesta, por defecto 302
     */
    public static function to($route = null, $seconds = null, $statusCode = 302)
    {
        $route OR $route = Router::get('controller_path') . '/';
        
        $route = PUBLIC_PATH . ltrim($route, '/');
        
        if ($seconds) {
            header("Refresh: $seconds; url=$route");
        } else {
            header('HTTP/1.1 ' . $statusCode);
            header("Location: $route");
            $_SESSION['KUMBIA.CONTENT'] = ob_get_clean();
            View::select(null, null);
        }
    }
    
    /**
     * Redirecciona la ejecución a una accion del controlador actual en un
     * tiempo de ejecución determinado
     * 
     * @param string $action acción del controlador actual a la que se redirige
     * @param integer $seconds segundos que se esperarán antes de redirigir
     * @param integer $statusCode código http de la respuesta, por defecto 302
     */
    public static function toAction($action, $seconds = null, $statusCode = 302)
    {
        self::to(Router::get('controller_path') . "/$action", $seconds, $statusCode);
    }
    
    /**
     * Enruta el controlador actual a otro módulo, controlador, o a otra acción
     * @deprecated Se mantiene por legacy temporalmente
     * @example
     * Redirect::route_to("module: modulo", "controller: nombre", "action: accion", "parameters: 1/2")
     */
    public static function route_to()
    {
        static $cyclic = 0;
        $url = Util::getParams(func_get_args());

        if (isset($url['module'])) {
            $vars['module'] = $url['module'];
            $vars['controller'] = 'index';
            $vars['action'] = 'index';
            $vars['parameters'] = array();
            $vars['controller_path'] = $url['module'] . '/index';
        }

        if (isset($url['controller'])) {
            $vars['controller'] = $url['controller'];
            $vars['action'] = 'index';
            $vars['parameters'] = array();
            $vars['controller_path'] = (isset($url['module'])) ? $url['module'] . '/' . $url['controller'] : $url['controller'];
        }

        if (isset($url['action'])) {
            $vars['action'] = $url['action'];
            $vars['parameters'] = array();
        }

        if (isset($url['parameters'])) {
            $vars['parameters'] = explode('/', $url['parameters']);
        } elseif (isset($url['id'])) {
            // Deprecated
            $vars['parameters'] = array($url['id']);
        } else {
            $vars['parameters'] = array();
        }

        if (++$cyclic > 1000)
            throw new KumbiaException('Se ha detectado un enrutamiento cíclico. Esto puede causar problemas de estabilidad');
        
        Router::to($vars, TRUE);
    }
}
