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
 * @copyright  Copyright (c) 2005 - 2021 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Clase que actua sobre el router en apps persistentes
 *
 * Capa de cache sobre la clase router
 *
 * @category   Kumbia
 * @package    Router
 */
class StaticRouter extends Router
{
    protected static $routes = [];

    /**
     * Ejecuta el router de la url
     *
     * @param string $url
     * @return Controller
     */
    public static function execute($url)
    {
        if(isset(self::$routes[$url])) {
            $cont = self::$routes[$url];
            $cont['vars']['method'] = $_SERVER['REQUEST_METHOD'];
            return parent::dispatch(new $cont['name'](self::$vars = $cont['vars']));
        }
        
        return parent::execute($url);
    }

    /**
     * Undocumented function
     *
     * @param Controller $cont
     * @return Controller
     */
    protected static function dispatch($cont)
    {
        self::$routes[self::$vars['route']] = 
                            [ 'name' => get_class($cont), // in php 5.5 try ::class
                            'vars' => self::$vars ];
        if (\count(self::$routes) > 256) {
            unset(self::$routes[key(self::$routes)]);
        }

        return parent::dispatch($cont);
    }
}