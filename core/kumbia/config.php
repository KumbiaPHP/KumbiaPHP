<?php
/**
 * KumbiaPHP web & app Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @category   Config
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Clase para la carga de Archivos .INI y de configuración.
 *
 * Aplica el patrón Singleton que utiliza un array
 * indexado por el nombre del archivo para evitar que
 * un .ini de configuración sea leido mas de una
 * vez en runtime con lo que aumentamos la velocidad.
 *
 * @category   Kumbia
 */
class Config
{
    /**
     * Contain all the config
     * -
     * Contenido de variables de configuración.
     *
     * @var array
     */
    protected static $vars = [];

    /**
     * Get config vars
     * -
     * Obtiene configuración.
     *
     * @param string $var fichero.sección.variable
     *
     * @return mixed
     */
    public static function get($var)
    {
        $namespaces = explode('.', $var);
        if (!isset(self::$vars[$namespaces[0]])) {
            self::load($namespaces[0]);
        }
        switch (count($namespaces)) {
            case 3:
                return isset(self::$vars[$namespaces[0]][$namespaces[1]][$namespaces[2]]) ?
                             self::$vars[$namespaces[0]][$namespaces[1]][$namespaces[2]] : null;
            case 2:
                return isset(self::$vars[$namespaces[0]][$namespaces[1]]) ?
                             self::$vars[$namespaces[0]][$namespaces[1]] : null;
            case 1:
                return isset(self::$vars[$namespaces[0]]) ? self::$vars[$namespaces[0]] : null;

            default:
                trigger_error('Máximo 3 niveles en Config::get(fichero.sección.variable), pedido: '.$var);
        }
    }

    /**
     * Get all configs
     * -
     * Obtiene toda la configuración.
     *
     * @return array
     */
    public static function getAll()
    {
        return self::$vars;
    }

    /**
     * Set variable in config
     * -
     * Asigna un atributo de configuración.
     *
     * @param string $var   variable de configuración
     * @param mixed  $value valor para atributo
     * 
     * @return void
     */
    public static function set($var, $value)
    {
        $namespaces = explode('.', $var);
        switch (count($namespaces)) {
            case 3:
                self::$vars[$namespaces[0]][$namespaces[1]][$namespaces[2]] = $value;
                break;
            case 2:
                self::$vars[$namespaces[0]][$namespaces[1]] = $value;
                break;
            case 1:
                self::$vars[$namespaces[0]] = $value;
                break;
            default:
                trigger_error('Máximo 3 niveles en Config::set(fichero.sección.variable), pedido: '.$var);
        }
    }

    /**
     * Read config file
     * -
     * Lee y devuelve un archivo de configuración.
     *
     * @param string $file  archivo .php o .ini
     * @param bool   $force forzar lectura de .php o .ini
     *
     * @return array
     */
    public static function &read($file, $force = false)
    {
        if (isset(self::$vars[$file]) && !$force) {
            return self::$vars[$file];
        }
        self::load($file);

        return self::$vars[$file];
    }

    /**
     * Load config file
     * -
     * Lee un archivo de configuración.
     *
     * @param string $file archivo
     * 
     * @return void
     */
    private static function load($file)
    {
        if (file_exists(APP_PATH."config/$file.php")) {
            self::$vars[$file] = require APP_PATH."config/$file.php";

            return;
        }
        // sino carga el .ini
        self::$vars[$file] = parse_ini_file(APP_PATH."config/$file.ini", true);
    }
}
