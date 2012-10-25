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
 * @package    Config 
 * @copyright  Copyright (c) 2005-2012 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

/**
 * Clase para la carga de Archivos .INI y de configuración
 *
 * Aplica el patrón Singleton que utiliza un array
 * indexado por el nombre del archivo para evitar que
 * un .ini de configuración sea leido mas de una
 * vez en runtime con lo que aumentamos la velocidad.
 *
 * @category   Kumbia
 * @package    Config
 */
final class Config
{

    /**
     * Contenido de variables de configuracion
     *
     * @var array
     */
    protected static $_vars = array();

    /**
     * Obtiene un atributo de configuracion
     *
     * @param string $var nombre de variable de configuracion
     * @return mixed
     */
    public static function get($var)
    {
        $namespaces = explode('.', $var);
        switch (count($namespaces)) {
            case 3:
                if (isset(self::$_vars[$namespaces[0]][$namespaces[1]][$namespaces[2]])) {
                    return self::$_vars[$namespaces[0]][$namespaces[1]][$namespaces[2]];
                }
                break;
            case 2:
                if (isset(self::$_vars[$namespaces[0]][$namespaces[1]])) {
                    return self::$_vars[$namespaces[0]][$namespaces[1]];
                }
                break;
            case 1:
                if (isset(self::$_vars[$namespaces[0]])) {
                    return self::$_vars[$namespaces[0]];
                }
                break;
        }
        return NULL;
    }

    /**
     * Asigna un atributo de configuracion
     *
     * @param string $var variable de configuracion
     * @param mixed $value valor para atributo
     */
    public static function set($var, $value)
    {
        $namespaces = explode('.', $var);
        switch (count($namespaces)) {
            case 3:
                self::$_vars[$namespaces[0]][$namespaces[1]][$namespaces[2]] = $value;
                break;
            case 2:
                self::$_vars[$namespaces[0]][$namespaces[1]] = $value;
                break;
            case 1:
                self::$_vars[$namespaces[0]] = $value;
                break;
        }
    }

    /**
     * Lee un archivo de configuracion
     *
     * @param $file archivo .ini
     * @param boolean $force forzar lectura de .ini
     * @return array
     */
    public static function & read($file, $force = FALSE)
    {
        if (isset(self::$_vars[$file]) && !$force) {
            return self::$_vars[$file];
        }

        self::$_vars[$file] = parse_ini_file(APP_PATH . "config/$file.ini", TRUE);
        return self::$_vars[$file];
    }

}
