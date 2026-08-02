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
 * @package    Security
 *
 * @copyright  Copyright (c) 2005 - 2023 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Clase para el almacenar valores durante una petición.
 *
 * Permite almacenar valores durante la ejecución de la aplicación. Implementa el
 * patrón de diseño Registry
 *
 * @category   Kumbia
 * @package    Security
 *
 */
class Registry
{

    /**
     * Variable donde se guarda el registro
     *
     * @var array<string, mixed>
     */
    private static $registry = [];

    /**
     * Establece un valor del registro
     *
     * @param string $index
     * @param mixed  $value
     */
    public static function set($index, $value)
    {
        self::$registry[$index] = $value;
    }

    /**
     * Agrega un valor al registro a uno ya establecido
     *
     * @param string $index
     * @param mixed  $value
     */
    public static function append($index, $value)
    {
        self::exist($index);
        self::$registry[$index][] = $value;
    }

    /**
     * Agrega un valor al registro al inicio de uno ya establecido
     *
     * @param string $index
     * @param mixed  $value
     */
    public static function prepend($index, $value)
    {
        self::exist($index);
        array_unshift(self::$registry[$index], $value);
    }

    /**
     * Obtiene un valor del registro
     *
     * @param string $index
     * @return mixed
     */
    public static function get($index)
    {
        return self::$registry[$index] ?? null;
    }

    /**
     * Asegura que el índice contenga un array para agregar valores
     *
     * @param string $index
     */
    protected static function exist($index)
    {
        if (!array_key_exists($index, self::$registry)) {
            self::$registry[$index] = [];
        } elseif (!is_array(self::$registry[$index])) {
            self::$registry[$index] = [self::$registry[$index]];
        }
    }
}
