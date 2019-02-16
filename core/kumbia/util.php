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
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Utilidades para uso general del framework.
 *
 * Manejo de cadenas de caracteres.
 * Conversión de parametros con nombre a arreglos.
 *
 * @category   Kumbia
 */
class Util
{
    /**
     * Convierte la cadena con espacios o guión bajo en notación camelcase.
     *
     * @param string $str   cadena a convertir
     * @param bool   $lower indica si es lower camelcase
     *
     * @return string
     * */
    public static function camelcase($str, $lower = false)
    {
        // Notacion lowerCamelCase
        if ($lower) {
            return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $str))));
        }

        return str_replace(' ', '', ucwords(str_replace('_', ' ', $str)));
    }

    /**
     * Convierte la cadena CamelCase en notación smallcase.
     *
     * @param string $str cadena a convertir
     *
     * @return string
     * */
    public static function smallcase($str)
    {
        return strtolower(preg_replace('/([A-Z])/', '_\\1', lcfirst($str)));
    }

    /**
     * Remplaza en la cadena los espacios por guiónes bajos (underscores).
     *
     * @param string $str
     *
     * @return string
     * */
    public static function underscore($str)
    {
        return strtr($str, ' ', '_');
    }

    /**
     * Remplaza en la cadena los espacios por dash (guiones).
     *
     * @param string $str
     *
     * @return string
     */
    public static function dash($str)
    {
        return strtr($str, ' ', '-');
    }

    /**
     * Remplaza en una cadena los underscore o dashed por espacios.
     *
     * @param string $str
     *
     * @return string
     */
    public static function humanize($str)
    {
        return strtr($str, '_-', '  ');
    }

    /**
     * Convierte los parámetros de una función o método de parámetros por nombre a un array.
     *
     * @param array $params
     *
     * @return array
     */
    public static function getParams($params)
    {
        $data = array();
        foreach ($params as $p) {
            if (is_string($p)) {
                $match = explode(': ', $p, 2);
                if (isset($match[1])) {
                    $data[$match[0]] = $match[1];
                } else {
                    $data[] = $p;
                }
            } else {
                $data[] = $p;
            }
        }

        return $data;
    }

    /**
     * Recibe una cadena como: item1,item2,item3 y retorna una como: "item1","item2","item3".
     *
     * @param string $lista cadena con Items separados por comas (,)
     *
     * @return string cadena con Items encerrados en doblecomillas y separados por comas (,)
     */
    public static function encomillar($lista)
    {
        $items = explode(',', $lista);

        return '"'.implode('","', $items).'"';
    }
}
