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
 * Cache con Sqlite
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
    protected static function _db() 
    {
        if(self::$_db) {
            return self::$_db;
        }
        
        $db = sqlite_open(APP_PATH . 'temp/cache.db');
        $result = sqlite_query($db, "SELECT COUNT(*) FROM sqlite_master WHERE type='table' AND tbl_name='cache' ");
        $count = sqlite_fetch_single($result);
       
        if(!$count) {
            sqlite_exec($db, ' CREATE TABLE cache (id TEXT, "group" TEXT, value TEXT, lifetime TEXT) ');
        }
        self::$_db = $db;
        
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
        
        $result = sqlite_query(self::_db(), " SELECT value FROM cache WHERE id='$id' AND \"group\"='$group' AND lifetime>'$lifetime' OR lifetime='undefined' ");
        return sqlite_fetch_single($result);
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
        
        $db = self::_db();
        $result = sqlite_query($db, " SELECT COUNT(*) FROM cache WHERE id='$id' AND \"group\"='$group' ");
        $count = sqlite_fetch_single($result);
        
        /**
         * Ya existe el elemento cacheado
         *
         **/
        if($count) {
            return sqlite_exec($db, " UPDATE cache SET value='$value', lifetime='$lifetime' WHERE id='$id' AND \"group\"='$group' ");
        }
        
        return sqlite_exec($db, " INSERT INTO cache (id, \"group\", value, lifetime) VALUES ('$id','$group','$value','$lifetime') ");
    }
    
	/**
	 * Limpia la cache
	 *
	 * @param string $group
	 * @return boolean
	 */
	public static function clean($group=false)
    {
        $db = self::_db();
        if($group) {
            $group = addslashes($group);
            return sqlite_exec(self::_db(), " DELETE FROM cache WHERE \"group\"='$group' ");
        }
        return sqlite_exec(self::_db(), " DELETE FROM cache ");
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
        
        return sqlite_exec(self::_db(), " DELETE FROM cache WHERE id='$id' AND \"group\"='$group' ");
    }
}