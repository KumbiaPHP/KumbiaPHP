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
 * Cacheo de Archivos
 * 
 * @category   Kumbia
 * @package    Cache
 * @subpackage Drivers 
 * @copyright  Copyright (c) 2005-2009 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */
class SqliteCache implements CacheInterface
{
    /**
     * Conexion a la base de datos Sqlite
     *
     * @var resource
     **/
    protected static $_db = null;
    /**
     * Abre una conexión SqLite a la base de datos cache
     *
     * @return resource
     * @throw KumbiaException
     **/
    protected static function _get_db() 
    {
        if(self::$_db) {
            return self::$_db;
        }
        
        self::$_db = $db = new PDO('sqlite:' . APP_PATH . 'temp/cache.sq3');;
        return $db;
    }
	/**
	 * Carga un elemento cacheado
	 *
	 * @param string $id
	 * @param string $group
	 * @return string
	 */
	public static function get($id, $group) 
    {
        $id = addslashes($id);
        $group = addslashes($group);
        
        $id = addslashes($id);
        $group = addslashes($group);
        $lifetime = time();
        
        $db = self::_get_db();
        $query = $db->query(" SELECT value FROM cache WHERE id='$id' AND \"group\"='$group' AND lifetime>'$lifetime' OR lifetime='undefined' ");
        if($row = $query->fetch(PDO::FETCH_NUM)) {
            return $row[0];
        }
        
        return null;
    }
	/**
	 * Guarda un elemento en la cache con nombre $id y valor $value
	 *
	 * @param string $id
	 * @param string $group
	 * @param string $value
	 * @param int $lifetime tiempo de vida en forma timestamp de unix
	 * @return boolean
	 */
	public static function save($id, $group, $value, $lifetime)
    {
        if($lifetime == null) {
            $lifetime = 'undefined';
        }
        
        $id = addslashes($id);
        $group = addslashes($group);
        $value = addslashes($value);
        
        $db = self::_get_db();
        $query = $db->query(" SELECT COUNT(*) FROM cache WHERE id='$id' AND \"group\"='$group' ");
        $row = $query->fetch(PDO::FETCH_NUM);
        
        /**
         * Ya existe el elemento cacheado
         *
         **/
        if($row[0]) {
            $db->exec(" UPDATE cache SET value='$value', lifetime='$lifetime' WHERE id='$id' AND \"group\"='$group' ");
        } else {
            $db->exec(" INSERT INTO cache (id, \"group\", value, lifetime) VALUES ('$id','$group','$value','$lifetime') ");
        }
    
        return true;
    }
    
	/**
	 * Limpia la cache
	 *
	 * @param string $group
	 * @return boolean
	 */
	public static function clean($group=false)
    {
        $db = self::_get_db();
        if($group) {
            $group = addslashes($group);
            $db->exec(" DELETE FROM cache WHERE \"group\"='$group' ");
        } else {
            $db->exec(" DELETE FROM cache ");
        }
        
        return true;
    }
	/**
	 * Elimina un elemento de la cache
	 *
	 * @param string $id
	 * @param string $group
	 * @return boolean
	 */
	public static function remove($id, $group)
    {
        $id = addslashes($id);
        $group = addslashes($group);
        
        $db = self::_get_db();
        $db->exec(" DELETE FROM cache WHERE id='$id' AND \"group\"='$group' ");
        
        return true;
    }
}