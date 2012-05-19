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
 * Validate es una Clase que realiza validaciones Lógicas
 * 
 * @category   KumbiaPHP
 * @package    validate 
 * @copyright  Copyright (c) 2005-2012 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */
class Validate
{
	/**
	 * Constantes para definir los patrones
	 */
  
	/*
	 * El valor deber ser solo letras y números
	 */
	const IS_ALPHANUM = '/^[\p{Ll}\p{Lm}\p{Lo}\p{Lt}\p{Lu}\p{Nd}]+$/mu';
	 
	 
	 
    /**
     * Almacena el mensaje de error
     *
     * @var String
     */
    public static $error = NULL;
    /**
     * Almacena la Expresion Regular
     *
     * @var String
     */
    public static $regex = NULL;
    /**
     * Valida que int
     *
     * @param int $check
     * @return bool
     */
    public static function int($check)
    {
        return filter_var($check, FILTER_VALIDATE_INT);
    }
    
    /**
     * Valida que una cadena este entre un rango.
     * Los espacios son contados
     * Retorna true si el string $value se encuentra entre min and max
     *
     * @param string $value
     * @param int $min
     * @param int $max
     * @return bool
     */
    public static function maxLength($value, $max, $min = null)
    {
        $length = strlen($value);
        if($min and $length < $min){
			return false;
		}
        return ($length <= $max);
    }
    
    /**
     * Valida que es un número se encuentre 
     * en un rango minímo y máximo
     * 
     * @param int $value
     * @param int $min
     * @param int $max
     */
    public static function range($value, $min=0, $max=NULL)
    {
        $int_options = array('options' => array('min_range'=>$min, 'max_range'=>$max));
        return filter_var($value, FILTER_VALIDATE_INT, $int_options);
    }

    /**
     * Valida que un valor se encuentre en una lista
     * Retorna tru si el string $value se encuentra en la lista $list
     *
     * @param string $value
     * @param array $list
     * @return bool
     */
    public static function inList($value, $list)
    {
        return in_array($value, $list);
    }
    
    /**
     * Valida que una cadena sea un mail
     *
     * @param string $mail
     * @return bool
     */
    public static function mail($mail)
    {
        return filter_var($mail, FILTER_VALIDATE_EMAIL);
    }
    /**
     * Valida URL
     *
     * @param string $url
     * @return bool
     */
    public static function url($url, $flag = 0)
    {
        return filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_HOST_REQUIRED | $flag);
    }
    
    /**
     * Valida que sea una IP, por defecto v4
     *
     * @param String $ip
     * @return bool
     */
    public static function ip($ip, $flags = FILTER_FLAG_IPV4)
    {
        return filter_var($ip, FILTER_VALIDATE_IP, $flags);
    }
    
    /**
     * Valida que un string no sea null
     *
     * @param string $check
     * @return bool
     */
    public static function required($check)
    {
        return !empty($check) && $check!='0';
    }
    
    /**
     * Valida que un String sea alpha-num (incluye caracteres acentuados)
     *
     * @param string $string
     * @return bool
     */
    public static function alphanum($string)
    {
        return self::pattern($string, self::IS_ALPHANUM);
    }
    
    /**
     * Valida una fecha
     *
     * @param string $value fecha a validar acorde al formato indicado
     * @param string $format formato de fecha. acepta: d-m-y, y-m-d, m-d-y, donde el "-" puede ser cualquier caracter 
     *                       de separacion incluso un espacio en blanco o ".", exceptuando (d,m,y o números).
     * @return boolean
     */
    public static function date($value, $format = 'd-m-y')
    {
        // busca el separador removiendo los caracteres de formato
        $separator = str_replace(array('d' , 'm' , 'y'), '', $format);
        $separator = $separator[0]; // el separador es el primer caracter
        if ($separator && substr_count($value, $separator) == 2) {
            switch (str_replace($separator, '', $format)) {
                case 'dmy':
                    list ($day, $month, $year) = explode($separator, $value);
                    break;
                case 'mdy':
                    list ($month, $day, $year) = explode($separator, $value);
                    break;
                case 'ymd':
                    list ($year, $month, $day) = explode($separator, $value);
                    break;
                default:
                    return false;
            }
            if (ctype_digit($month) && ctype_digit($day) && ctype_digit($year)) {
                return checkdate($month, $day, $year);
            }
        }
        return false;
    }
    
    /**
     * Valida un string dada una Expresion Regular
     *
     * @param string $check
     * @param string $regex
     * @return bool
     */
    public static function pattern($check, $regex)
    {
        return filter_var($check, FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => $regex)));
    }
    
    /**
     * Valida si es un número decimal
     * 
     * @param string $value
     * @param string $decimal
     * @return boolean
     */
    public static function decimal($value, $decimal = ',')
    {
		return filter_var($value, FILTER_VALIDATE_FLOAT, array('options' => array('decimal' => $decimal)));
	}
}
