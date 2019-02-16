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
 * @package    Db
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */
/**
 * @see DbBaseInterface
 */
require_once __DIR__.'/db_base_interface.php';
/**
 * @see DbBase
 */
require_once __DIR__.'/db_base.php';

/**
 * Clase que maneja el pool de conexiones.
 *
 * @category   Kumbia
 */
class Db
{
    /**
     * Singleton de conexiones a base de datos.
     *
     * @var array
     */
    protected static $_connections = array();

    /**
     * Devuelve la conexión, si no existe llama a Db::connect para crearla.
     *
     * @param string $database base de datos a donde conectar
     *
     * @return DbBase
     */
    public static function factory($database = null)
    {
        //Cargo el mode para mi aplicación
        if (!$database) {
            $database = Config::get('config.application.database');
        }
        //Si no es una conexión nueva y existe la conexión singleton
        if (isset(self::$_connections[$database])) {
            return self::$_connections[$database];
        }

        return self::$_connections[$database] = self::connect($database);
    }

    /**
     * Realiza una conexión directa al motor de base de datos
     * usando el driver de Kumbia.
     *
     * @param string $database base de datos a donde conectar
     *
     * @return DbBase
     */
    private static function connect($database)
    {
        $config = Config::read('databases')[$database];

        // carga los valores por defecto para la conexión, si no existen
        $config = $config + ['port' => 0, 'dsn' => null, 'dbname' => null, 'host' => 'localhost',
                             'username' => null, 'password' => null, 'pdo' => false, 'charset' => '', ];
        $path = __DIR__;

        //Si usa PDO
        if ($config['pdo']) {
            $dbclass = "DbPdo{$config['type']}";
            $db_file = "$path/adapters/pdo/{$config['type']}.php";
        } else {
            if ($config['type'] === 'mysqli') {
                $config['type'] = 'mysql';
            }
            $dbclass = "Db{$config['type']}";
            $db_file = "$path/adapters/{$config['type']}.php";
        }

        //Carga la clase adaptadora necesaria
        if (!include_once $db_file) {
            throw new KumbiaException("No existe la clase $dbclass, necesaria para iniciar el adaptador");
        }

        return new $dbclass($config);
    }
}
