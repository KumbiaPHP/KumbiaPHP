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
 * @copyright  Copyright (c) 2005 - 2020 KumbiaPHP Team (https://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Loads the .ini files and configuration 
 * 
 * Applies the Singleton pattern which uses an indexed array by file name 
 * in order to avoid a file can be read more than once
 * 
 * -
 * 
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
     * @param string $var file.section.var - fichero.sección.variable
     *
     * @return mixed
     */
    public static function get(string $var)
    {
        $namespaces = explode('.', $var);
        if (! isset(self::$vars[$namespaces[0]])) {
            self::load($namespaces[0]);
        }
        switch (count($namespaces)) {
            case 3:
                return self::$vars[$namespaces[0]][$namespaces[1]][$namespaces[2]] ?? null;
            case 2:
                return self::$vars[$namespaces[0]][$namespaces[1]] ?? null;
            case 1:
                return self::$vars[$namespaces[0]] ?? null;

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
    public static function getAll() : array
    {
        return self::$vars;
    }

    /**
     * Set variable in config
     * -
     * Asigna un atributo de configuración.
     *
     * @param string $var   configuration var - variable de configuración
     * @param mixed  $value value for the attribute - valor para atributo
     * 
     * @return void
     */
    public static function set($var, $value)
    {
        $namespaces = explode('.', $var);
        switch (count($namespaces)) {
            case 3:
                self::$vars[$namespaces[0]][$namespaces[1]][$namespaces[2]] = $value;
                return;
            case 2:
                self::$vars[$namespaces[0]][$namespaces[1]] = $value;
                return;
            case 1:
                self::$vars[$namespaces[0]] = $value;
                return;
            default:
                trigger_error('Máximo 3 niveles en Config::set(fichero.sección.variable), pedido: '.$var);
        }
    }

    /**
     * Read config file
     * -
     * Lee y devuelve un archivo de configuración.
     *
     * @param string $file  .php or .ini
     * @param bool   $force force reading a file - forzar lectura de .php o .ini
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
     * @param string $file
     * 
     * @return void
     */
    private static function load(string $file) : void
    {
        self::$vars[$file] = require APP_PATH."config/$file.php";
    }
}
