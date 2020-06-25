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
 * @package    ParseInput
 *
 * @copyright  Copyright (c) 2005 - 2020 KumbiaPHP Team (https://kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

 /**
  * Clase para analizar y retornar los parámetros de la petición.
  */
class ParseInput
{
    /**
     * Permite definir parser personalizados por MIME TYPE
     * Esto es necesario para interpretar las entradas
     * Se define como un MIME type como clave y el valor debe ser un
     * callback que devuelva los datos interpretado.
     */
    const INPUT_TYPE = [
        'application/json' => ['self', 'parseJSON'],
        'application/xml' => ['self', 'parseXML'],
        'text/xml' => ['self', 'parseXML'],
        'text/csv' => ['self', 'parseCSV'],
        'application/x-www-form-urlencoded' => ['self', 'parseForm']
    ];
    
    /**
     * Returns the parsed input data
     *
     * @param string $input
     * 
     * @return mixed
     */
    public static function parse()
    {
        $input = file_get_contents('php://input');
        $format = self::getInputFormat();
        /* verifica si el formato tiene un parser válido */
        if ($format && is_callable(self::INPUT_TYPE[$format])) {
            $result = call_user_func(self::INPUT_TYPE[$format], $input);
            if ($result) {
                return $result;
            }
        }

        return $input;
    }

    /**
     * Retorna el tipo de formato de entrada.
     *
     * @return string
     */
    public static function getInputFormat()
    {
        if (isset($_SERVER['CONTENT_TYPE'])) {
            $str = explode(';', $_SERVER['CONTENT_TYPE']);
            return trim($str[0]);
        }

        return '';
    }

    /**
     * Parse JSON
     * Convierte formato JSON en array asociativo.
     *
     * @param string $input
     *
     * @return array|string
     */
    public static function parseJSON($input)
    {
        return json_decode($input, true);
    }

    /**
     * Parse XML.
     *
     * Convierte formato XML en un objeto, esto será necesario volverlo estandar
     * si se devuelven objetos o arrays asociativos
     *
     * @param string $input
     *
     * @return \SimpleXMLElement|null
     */
    public static function parseXML($input)
    {
        try {
            return new SimpleXMLElement($input);
        } catch (Exception $e) {
            // Do nothing
        }
    }

    /**
     * Parse CSV.
     *
     * Convierte CSV en arrays numéricos,
     * cada item es una linea
     *
     * @param string $input
     *
     * @return array
     */
    public static function parseCSV($input)
    {
        $temp = fopen('php://memory', 'rw');
        fwrite($temp, $input);
        fseek($temp, 0);
        $res = [];
        while (($data = fgetcsv($temp)) !== false) {
            $res[] = $data;
        }
        fclose($temp);

        return $res;
    }

    /**
     * Realiza la conversión de formato de Formulario a array.
     *
     * @param string $input
     *
     * @return array
     */
    public static function parseForm($input)
    {
        parse_str($input, $vars);

        return $vars;
    }
}
