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
 * Clase que implementa un componente de cacheo
 * 
 * @category   Kumbia
 * @package    Cache 
 * @copyright  Copyright (c) 2005-2009 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */
 
/**
 * @see CacheInterface
 */
include CORE_PATH . 'libs/cache/cache_interface.php';

/**
 * Clase que implementa un componente de cacheo
 */
class Cache
{
    /**
     * Pool de drivers para cache
     *
     * @var array
     **/
    protected static $_drivers = array();
    /**
     * Id de ultimo elemento solicitado
     *
     * @var string
     */
    protected static $_id = null;
    /**
     * Grupo de ultimo elemento solicitado
     *
     * @var string
     */
    protected static $_group = 'default';
    /**
     * Tiempo de vida
     *
     * @var string
     */
    protected static $_lifetime = null;
    /**
     * Driver para cache
     *
     * @var string
     **/
    protected static $_driver = null;
    /**
     * Carga un elemento cacheado
     *
     * @param string $id
     * @param string $group
     * @return string
     */
    public static function get ($id, $group = 'default')
    {
        self::$_id = $id;
        self::$_group = $group;
        return self::$_driver->get($id, $group);
    }
    /**
     * Guarda un elemento en la cache con nombre $id y valor $value
     *
     * @param string $value
     * @param string $lifetime tiempo de vida con formato strtotime, utilizado para cache de tiempo constante
     * @param string $id
     * @param string $group
     * @return boolean
     */
    public static function save ($value, $lifetime = null, $id = false, $group = 'default')
    {
        /**
         * Verifica si se ha pasado un id
         **/
        if (! $id) {
            $id = self::$_id;
            $group = self::$_group;
        }
        if ($lifetime) {
            $lifetime = strtotime($lifetime);
        }
        
        return self::$_driver->save($id, $group, $value, $lifetime);
    }
    /**
     * Inicia el cacheo del buffer de salida hasta que se llame a end
     *
     * @param string $lifetime tiempo de vida con formato strtotime, utilizado para cache de tiempo constante
     * @param string $id
     * @param string $group
     * @return string
     */
    public static function start ($lifetime, $id, $group = 'default')
    {
        if ($data = self::get($id, $group)) {
            return $data;
        }
        self::$_lifetime = $lifetime;
        ob_start();
    }
    /**
     * Termina el buffer de salida
     *
     * @param boolean $save indica si al terminar guarda la cache
     * @return boolean
     */
    public static function end ($save = true)
    {
        if (! $save) {
            ob_end_flush();
            return false;
        }
        $value = ob_get_contents();
        ob_end_flush();
        return self::save($value, self::$_lifetime, self::$_id, self::$_group);
    }
    /**
     * Limpia la cache
     *
     * @param string $group
     * @return boolean
     */
    public static function clean ($group = false)
    {
        return self::$_driver->clean($group);
    }
    /**
     * Elimina un elemento de la cache
     *
     * @param string $id
     * @param string $group
     * @return boolean
     */
    public static function remove ($id, $group = 'default')
    {
        return self::$_driver->remove($id, $group);
    }
    /**
     * Asigna el driver para cache
     *
     * @param string $driver (file, sqlite, memsqlite)
     **/
    public static function set_driver ($driver)
    {
        if(!isset(self::$_drivers[$driver])) {
            require_once CORE_PATH . "libs/cache/drivers/{$driver}_cache.php";
            $class = $driver.'cache';
            self::$_drivers[$driver] = new $class();
        }
        
        self::$_driver = self::$_drivers[$driver];
    }
}