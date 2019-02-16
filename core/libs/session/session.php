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
 * @package    Session
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/*Session start*/
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
/**
 * Modelo orientado a objetos para el acceso a datos en Sesiones
 *
 * @category   Kumbia
 * @package    Session
 */
class Session
{
    const SESSION = 'KUMBIA_SESSION';
    const SPACE = 'default';
    /**
     * Crear o especificar el valor para un indice de la sesión
     * actual
     *
     * @param string $index
     * @param mixed  $value
     * @param string $namespace
     */
    public static function set($index, $value, $namespace = self::SPACE)
    {
        $_SESSION[self::SESSION][APP_PATH][$namespace][$index] = $value;
    }

    /**
     * Obtener el valor para un indice de la sesión
     *
     * @param string $index
     * @param string $namespace
     * @return mixed
     */
    public static function get($index, $namespace = self::SPACE)
    {
        if (isset($_SESSION[self::SESSION][APP_PATH][$namespace][$index])) {
            return $_SESSION[self::SESSION][APP_PATH][$namespace][$index];
        }
    }

    /**
     * Elimina un indice
     *
     * @param string $index
     * @param string $namespace
     */
    public static function delete($index, $namespace = self::SPACE)
    {
        unset($_SESSION[self::SESSION][APP_PATH][$namespace][$index]);
    }

    /**
     * Verifica si el indice esta cargado en sesión
     *
     * @param string $index
     * @param string $namespace
     * @return boolean
     */
    public static function has($index, $namespace = self::SPACE)
    {
        return isset($_SESSION[self::SESSION][APP_PATH][$namespace][$index]);
    }
}
