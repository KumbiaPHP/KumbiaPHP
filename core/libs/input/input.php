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
 * @package    Input
 *
 * @copyright  Copyright (c) 2005 - 2020 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/** 
 * Clase para manejar los datos del request
 *
 * @category   Kumbia
 * @package    Input
 */
class Input
{
    /**
     * Verifica o obtiene el método de la petición
     *
     * @param string $method Http method
     * @return mixed
     */
    public static function is(string $method = '')
    {
        if ($method) {
            return $method === $_SERVER['REQUEST_METHOD'];
        }
        return $_SERVER['REQUEST_METHOD'];
    }

    /**
     * Indica si el request es AJAX
     *
     * @return boolean
     */
    public static function isAjax(): bool
    {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');
    }

    /**
     * Detecta si el Agente de Usuario (User Agent) es un móvil
     *
     * @return boolean
     */
    public static function isMobile(): bool
    {
        return (bool) strpos(mb_strtolower($_SERVER['HTTP_USER_AGENT']), 'mobile');
    }

    /**
     * Obtiene un valor del arreglo $_POST
     *
     * @param string $var
     * @return mixed
     */
    public static function post(string $var = '')
    {
        return self::getFilter($_POST, $var);
    }

    /**
     * Obtiene un valor del arreglo $_GET, aplica el filtro FILTER_SANITIZE_STRING
     * por defecto
     *
     * @param string $var
     * @return mixed
     */
    public static function get(string $var = '')
    {
        return self::getFilter($_GET, $var);
    }

    /**
     * Obtiene un valor del arreglo $_REQUEST
     *
     * @param string $var
     * @return mixed
     */
    public static function request(string $var = '')
    {
        return self::getFilter($_REQUEST, $var);
    }


    /**
     * Obtiene un valor del arreglo $_SERVER
     *
     * @param string $var
     * @return mixed
     */
    public static function server(string $var = '')
    {
        return self::getFilter($_SERVER, $var);
    }

    /**
     * Verifica si existe el elemento indicado en $_POST
     *
     * @param string $var elemento a verificar
     * @return boolean
     */
    public static function hasPost(string $var): bool
    {
        return (bool) self::post($var);
    }

    /**
     * Verifica si existe el elemento indicado en $_GET
     *
     * @param string $var elemento a verificar
     * @return boolean
     */
    public static function hasGet(string $var): bool
    {
        return (bool) self::get($var);
    }

    /**
     * Verifica si existe el elemento indicado en $_REQUEST
     *
     * @param string $var elemento a verificar
     * @return boolean
     */
    public static function hasRequest(string $var): bool
    {
        return (bool) self::request($var);
    }

    /**
     * Elimina elemento indicado en $_POST
     *
     * @param string $var elemento a verificar
     * @return void
     */
    public static function delete(string $var = ''): void
    {
        if ($var) {
            $_POST[$var] = [];
            return;
        }
        $_POST = [];
    }

    /**
     * Permite Obtener el Agente de Usuario (User Agent)
     * @return String
     */
    public static function userAgent(): string
    {
        return $_SERVER['HTTP_USER_AGENT'];
    }

    /**
     * Permite obtene la IP del cliente, aún cuando usa proxy
     * @return String
     */
    public static function ip(): string
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        return $_SERVER['REMOTE_ADDR'];
    }


    /**
     * Obtiene y filtra un valor del arreglo $_REQUEST
     * Por defecto, usa SANITIZE
     *
     * @param string $var
     * @return mixed
     */
    public static function filter(string $var)
    {
        //TODO
    }

    /**
     * Devuelve el valor dentro de un array con clave en formato uno.dos.tres
     * @param array  $var array que contiene la variable
     * @param string $str clave a usar
     * @return mixed
     */
    protected static function getFilter(array $var, string $str)
    {
        if (empty($str)) {
            return filter_var_array($var);
        }
        $arr = explode('.', $str);
        $value = $var;
        foreach ($arr as $key) {
            if (isset($value[$key])) {
                $value = $value[$key];
            } else {
                $value = null;
                break;
            }
        }
        return is_array($value) ? filter_var_array($value) : filter_var($value);
    }
}
