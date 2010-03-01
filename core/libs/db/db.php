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
 * Clase que maneja el pool de conexiones
 * 
 * @category   Kumbia
 * @package    Db 
 * @copyright  Copyright (c) 2005-2009 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

/**
 * @see DbBaseInterface
 */
require CORE_PATH . 'libs/db/db_base_interface.php';
/**
 * @see DbBase
 */
require CORE_PATH . 'libs/db/db_base.php';

/**
* Carga el modelo base
*/
require APP_PATH . 'active_record.php';	

/**
 * Clase que maneja el pool de conexiones
 *
 */
class Db
{
    /**
     * Singleton de conexiones a base de datos
     *
     * @var array
     **/
    protected static $_connections = array();

    /**
     * Realiza una conexión directa al motor de base de datos
     * usando el driver de Kumbia
     *
     * @param boolean $new nueva conexion
     * @param string $database base de datos a donde conectar
     * @return db
     */
    public static function factory ($database = null, $new = false)
    {
        /**
         * Cargo el mode para mi aplicacion
         **/
        if (! $database) {
            $database = Config::get('config.application.database');
        }
        $databases = Config::read('databases');
        $config = $databases[$database];
        /**
         * Cargo valores por defecto para la conexion
         * en caso de que no existan
         **/
        if (! isset($config['port'])) {
            $config['port'] = 0;
        }
        if (! isset($config['dsn'])) {
            $config['dsn'] = '';
        }
        if (! isset($config['host'])) {
            $config['host'] = '';
        }
        if (! isset($config['username'])) {
            $config['username'] = '';
        }
        if (! isset($config['password'])) {
            $config['password'] = '';
        }
        //Si no es una conexion nueva y existe la conexion singleton
        if (! $new && isset(self::$_connections[$database])) {
            return self::$_connections[$database];
        }
        //Cargo la clase adaptadora necesaria
        if (isset($config['pdo'])) {
            try {
            	$connection = new PDO($config['type'] . ":" . $config['dsn'], $config['username'], $config['password']);
            } catch (PDOException $e) {
                throw new KumbiaException($e->getMessage());
            }
        } else {
            $dbclass = "Db{$config['type']}";
            //if (! class_exists($dbclass)) {
                require_once CORE_PATH . 'libs/db/adapters/' . $config['type'] . '.php';
            //}
            
            if (! class_exists($dbclass)) {
                throw new KumbiaException("No existe la clase $dbclass, necesaria para iniciar el adaptador");
            }
            $connection = new $dbclass($config);
        }

        //Si no es para conexion nueva, la cargo en el singleton
        if (! $new) {
            self::$_connections[$database] = $connection;
        }
        return $connection;
    }
}