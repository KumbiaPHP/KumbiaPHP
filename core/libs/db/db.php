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
 * @package    Db
 * @copyright  Copyright (c) 2005-2012 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */
/**
 * @see DbBaseInterface
 */
require_once CORE_PATH . 'libs/db/db_base_interface.php';
/**
 * @see DbBase
 */
require_once CORE_PATH . 'libs/db/db_base.php';

/**
 * Clase que maneja el pool de conexiones
 *
 * @category   Kumbia
 * @package    Db
 */
class Db
{

    /**
     * Singleton de conexiones a base de datos
     *
     * @var array
     */
    protected static $_connections = array();

    /**
     * Devuelve la conexión, si no existe llama a Db::connect para crearla
     *
     * @param boolean $new nueva conexion //TODO mirar si es necesaria
     * @param string $database base de datos a donde conectar
     * @return db
     */
    public static function factory($database = null, $new = false)
    {

        //Cargo el mode para mi aplicacion
        if (!$database) {
            $database = Config::get('config.application.database');
        }
        //Si no es una conexion nueva y existe la conexion singleton
        if (isset(self::$_connections[$database])) {
            return self::$_connections[$database];
        }

        return self::$_connections[$database] = self::connect($database);
    }

    /**
     * Realiza una conexión directa al motor de base de datos
     * usando el driver de Kumbia
     *
     * @param string $database base de datos a donde conectar
     * @return db
     */
    private static function connect($database)
    {
        $databases = Config::read('databases');
        $config = $databases[$database];

        // carga los valores por defecto para la conexión, si no existen
        $default = array('port' => 0, 'dsn' => NULL, 'dbname' => NULL, 'host' => 'localhost', 'username' => NULL, 'password' => NULL);
        $config = $config + $default;

        //Si usa PDO
        if (isset($config['pdo'])) {
            $dbclass = "DbPdo{$config['type']}";
            $db_file = "libs/db/adapters/pdo/{$config['type']}.php";
        } else {
            $dbclass = "Db{$config['type']}";
            $db_file = "libs/db/adapters/{$config['type']}.php";
        }

        //Carga la clase adaptadora necesaria
        if (!include_once CORE_PATH . $db_file) {
            throw new KumbiaException("No existe la clase $dbclass, necesaria para iniciar el adaptador");
        }

        return new $dbclass($config);
    }

}
