<?php
/**
 * Warning! This IS A ALPHA VERSION NOT USE IN PRODUCTION APP!
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
 * Rest. Clase estática para el manejo de API basada en REST
 * 
 * @category   Kumbia
 * @package    Controller 
 * @copyright  Copyright (c) 2005-2012 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

/**
 * Clase para el manejo de API basada en REST
 *
 * @category   Kumbia
 * @package    Controller
 *
 */
class Rest
{

    private static $code = array(
        201 => 'Creado ', /* Se ha creado un nuevo recuerso (INSERT) */
        400 => 'Bad Request', /* Petición herronea */
        401 => 'Unauthorized', /* La petición requiere loggin */
        403 => 'Forbidden',
        405 => 'Method Not Allowed' /* No está permitido ese metodo */
    );
    /**
     * Array con los tipos de datos soportados para salida
     */
    private static $outputFormat = array('json', 'text', 'html', 'xml', 'cvs', 'php');
    /**
     * Tipo de datos soportados para entrada
     */
    private static $inputFormat = array('json', 'plain', 'x-www-form-urlencoded');
    /**
     * Metodo de petición (GET, POST, PUT, DELETE)
     */
    private static $method = null;
    /**
     * Establece el formato de salida
     */
    private static $oFormat = null;
    /**
     * Establece el formato de entrada
     */
    private static $iFormat = null;

    /**
     * Establece los tipos de respuesta aceptados
     */
    static public function accept($accept)
    {
        self::$outputFormat = is_array($accept) ? $accept : explode(',', $accept);
    }

    /**
     * Define el inicio de un servicio REST
     */
    static public function init()
    {
        $content = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : 'text/html';
        /**
         * Verifico el formato de entrada
         */
        self::$iFormat = str_replace(array('text/', 'application/'), '', $content);

        /* Compruebo el método de petición */
        self::$method = strtolower($_SERVER['REQUEST_METHOD']);
        $format = explode(',', $_SERVER['HTTP_ACCEPT']);
        while (self::$oFormat = array_shift($format)) {
            self::$oFormat = str_replace(array('text/', 'application/'), '', self::$oFormat);
            if (in_array(self::$oFormat, self::$outputFormat)) {
                break;
            }
        }

        /**
         * Si no lo encuentro, revuelvo un error
         */
        if (self::$oFormat == null) {
            return 'error';
        } else {
            View::response(self::$oFormat);
            View::select('response');
            return self::$method;
        }
    }

    /**
     * Retorna los parametros de la petición el función del método
     * de la petición
     */
    static function param()
    {
        $input = file_get_contents('php://input');
        if (strncmp(self::$iFormat, 'json', 4) == 0) {
            return json_decode($input, true);
        } else {
            parse_str($input, $output);
            return $output;
        }
    }

}
