<?php

/**
 * Kumbia PHP Framework
 *
 * LICENSE
 *
 * This source file is subject to the GNU/GPL that is bundled
 * with this package in the file docs/LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.kumbia.org/license.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kumbia@kumbia.org so we can send you a copy immediately.
 *
 * @category  Kumbia
 * @package   Cache
 * @copyright Copyright (c) 2005-2007 Andres Felipe Gutierrez (andresfelipe at vagoogle.net)
 * @copyright Copyright (C) 2007-2007 Roger Jose Padilla Camacho(rogerjose81 at gmail.com)
 * @copyright Copyright (C) 2007-2009 Deivinson Jose Tejeda Brito(deivinsontejeda at gmail.com)
 * @license   http://www.kumbia.org/license.txt GNU/GPL
 */
 
/**
 * @see CacheInterface
 */
include CORE_PATH . 'extensions/cache/cache_interface.php';
/**
 * @see FileCache
 */
include CORE_PATH . 'extensions/cache/drivers/file_cache.php';

/**
 * Clase que implementa un componente de cacheo (En Desarrollo)
 *
 * @category  Kumbia
 * @package   Cache
 * @copyright Copyright (c) 2005-2007 Andres Felipe Gutierrez (andresfelipe at vagoogle.net)
 * @copyright Copyright (C) 2007-2009 Deivinson Jose Tejeda Brito(deivinsontejeda at gmail.com)
 * @license   http://www.kumbia.org/license.txt GNU/GPL
 * @access    public
 */
class Cache
{
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
	 * Indica si la cache esta activa 
	 *
	 * @var boolean
	 **/
	protected static $_active = true;
	 
	/**
	 * Carga un elemento cacheado
	 *
	 * @param string $id
	 * @param string $group
	 * @return string
	 */
	public static function get($id, $group='default') 
    {
		if(!self::$_active)
			return null;
			
        self::$_id = $id;
        self::$_group = $group;
        return call_user_func(array(self::get_driver(), 'get'), $id, $group);
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
	public static function save($value, $lifetime=null, $id=false, $group='default')
    {
		if(!self::$_active)
			return false;
	
        /**
         * Verifica si se ha pasado un id
         **/
        if(!$id) {
            $id = self::$_id;
            $group = self::$_group;
        }
        
        if($lifetime)
            $lifetime = strtotime($lifetime);
            
        return call_user_func(array(self::get_driver(), 'save'), $id, $group, $value, $lifetime);
    }
	/**
	 * Inicia el cacheo del buffer de salida hasta que se llame a end
	 *
     * @param string $lifetime tiempo de vida con formato strtotime, utilizado para cache de tiempo constante
     * @param string $id
     * @param string $group
     * @return string
	 */
	public static function start($lifetime, $id, $group='default')
    {
        if($data = self::get($id, $group))
            return $data;
            
        self::$_lifetime = $lifetime;
        ob_start();
    }
	/**
	 * Termina el buffer de salida
	 *
	 * @param boolean $save indica si al terminar guarda la cache
	 * @return boolean
	 */
	public static function end($save=true)
    {
		if(!$save) {
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
	public static function clean($group=false)
    {
        return call_user_func(array(self::get_driver(), 'clean'), $group);
    }
	/**
	 * Elimina un elemento de la cache
	 *
	 * @param string $id
	 * @param string $group
	 * @return boolean
	 */
	public static function remove($id, $group='default')
    {
        return call_user_func(array(self::get_driver(), 'remove'), $id, $group);
    }
    /**
     * Obtiene el driver para cache
     *
     * @return string
     **/
    public static function get_driver()
    {
        return 'FileCache';
    }
	/**
	 * Activa la cache
	 *
	 * @param boolean $active
	 **/
	public static function active($active)
	{
		self::$_active = $active;
	}
}