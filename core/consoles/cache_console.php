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
 * Consola para manejar la cache
 *
 * @category   Kumbia
 * @package    consoles
 * @copyright  Copyright (c) 2005-2009 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */
class CacheConsole
{
    /**
     * Librerias que utiliza la consola
     *
     * @var array
     **/
    public $libs = array('cache');
    
    /**
     * Inicializa la consola
     *
     **/
    public function initialize()
    {
        // asigna el driver para la cache
        if($driver = Config::get('config.application.cache_driver')) {
            Cache::set_driver($driver);
        }
    }
    
    /**
     * Comando de consola para limpiar la cache
     *
     * @param array $params parametros nombrados de la consola
     * @param string $group nombre de grupo
     **/
    public function clean($params, $group=false)
    {
        // asigna el driver para la cache
        if(isset($params['driver'])) {
            Cache::set_driver($params['driver']);
        }
        
        // limpia la cache
        if(Cache::clean($group)) {
            echo 'Operación Exitosa'.PHP_EOL;
        } else {
            echo 'No se ha logrado eliminar el contenido'.PHP_EOL;
        }
    }
    
    /**
     * Comando de consola para eliminar un elemento cacheado
     *
     * @param array $params parametros nombrados de la consola
     * @param string $id id del elemento
     * @param string $group nombre de grupo
     **/
    public function remove($params, $id, $group='default')
    {
        // asigna el driver para la cache
        if(isset($params['driver'])) {
            Cache::set_driver($params['driver']);
        }
        
        // elimina el elemento
        if(Cache::remove($id, $group)) {
            echo 'Operación Exitosa'.PHP_EOL;
        } else {
            echo 'No se ha logrado eliminar el elemento'.PHP_EOL;
        }
    }
}