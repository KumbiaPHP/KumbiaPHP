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
 * @copyright  Copyright (c) 2005 - 2024 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Clase para la carga de Archivos de configuración.
 * 
 * Aplica el patrón Singleton que utiliza un array
 * indexado por el nombre del archivo para evitar que
 * un fichero de configuración sea leido mas de una
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
     * @var array<array-key,mixed>
     */
    protected static $config = [];

    /**
     * Get config
     * -
     * Obtiene configuración.
     *
     * @param string $var fichero.sección.variable
     *
     * @throws KumbiaException
     * @return mixed
     */
    public static function get($var)
    {
        $sections = explode('.', $var);
        self::$config[$sections[0]] ??= self::load($sections[0]);

        return match(count($sections)) {
            3 => self::$config[$sections[0]][$sections[1]][$sections[2]] ?? null,
            2 => self::$config[$sections[0]][$sections[1]] ?? null,
            1 => self::$config[$sections[0]] ?? null,
            default => throw new KumbiaException('Máximo 3 niveles en Config::get(fichero.sección.variable), pedido: '.$var)
        };
    }

    /**
     * Get all configs
     * -
     * Obtiene toda la configuración.
     *
     * @return array<array-key,mixed>
     */
    public static function getAll()
    {
        return self::$config;
    }

    /**
     * Set variable in config
     * -
     * Asigna un atributo de configuración.
     *
     * @param string $var   variable de configuración
     * @param mixed  $value valor para atributo
     * 
     * @throws KumbiaException
     * @return void
     */
    public static function set($var, $value)
    {
        $sections = explode('.', $var);
        match(count($sections)) {
            3 => self::$config[$sections[0]][$sections[1]][$sections[2]] = $value,
            2 => self::$config[$sections[0]][$sections[1]] = $value,
            1 => self::$config[$sections[0]] = $value,
            default => throw new KumbiaException('Máximo 3 niveles en Config::set(fichero.sección.variable), pedido: '.$var)
        };
    }

    /**
     * Read config file
     * -
     * Lee y devuelve un archivo de configuración.
     *
     * @param string $file  archivo .php o .ini
     * @param bool   $force forzar lectura de .php o .ini
     *
     * @return array<array-key,mixed>
     */
    public static function read($file, $force = false)
    {
        if ($force) {
            return self::$config[$file] = self::load($file);
        }

        return self::$config[$file] ??= self::load($file);
    }

    /**
     * Load config file
     * -
     * Lee un archivo de configuración.
     *
     * @param string $file archivo
     * 
     * @return array<array-key,mixed>
     */
    private static function load($file): array
    {
        if (is_file(APP_PATH."config/$file.php")) {

            return require APP_PATH."config/$file.php";
        }
        // sino carga el .ini desaconsejado por rendimiento (legacy)
        return parse_ini_file(APP_PATH."config/$file.ini", true);
    }
}
