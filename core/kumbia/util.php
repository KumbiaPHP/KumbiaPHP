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
 * Util para uso general del framework
 * 
 * @category   Kumbia
 * @package    Core 
 * @copyright  Copyright (c) 2005-2009 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */
class Util
{
    /**
     * Convierte la cadena con espacios o guión bajo en notacion camelcase
	 *
     * @param string $s cadena a convertir
	 * @param boolean $lower indica si es lower camelcase
     * @return string
     **/
    public static function camelcase($s, $lower=false)
    {
		
		$s = ucwords(strtolower(strtr($s, '_', ' ')));
		$s = str_replace(' ', '', $s);

        /**
         * Notacion lowerCamelCase
         **/
        if($lower) {
            $s = self::lcfirst($s);
        }
        return $s;
    }
	
	/**
	* Descameliza una cadena camelizada y la convierte a smallcase
	*
	 * @param string $s
	 * @return string
	 */
	public static function uncamelize($str) {
			$str = self::lcfirst($str);
		return strtolower(preg_replace('/([A-Z])/', '_\\1', $str));
	}
	
    /**
     * Convierte la cadena CamelCase en notacion smallcase
     * @param string $s cadena a convertir
     * @return string
     **/
    public static function smallcase($s) {
	        return strtolower(preg_replace('/([A-Z])/', "_\\1", $s));
    }
    /**
     * Remplaza en la cadena los espacios por guiónes bajos (underscores)
     * @param string $s
     * @return string
     **/
    public static function underscore($s)
    {
        return strtr($s,' ','_');
    }
    /**
     * Remplaza en la cadena los espacios por dash (guiones)
     * @param string $s
     * @return string
     **/
    public static function dash($s)
    {
        return strtr($s,' ','-');
    }
    /**
     * Remplaza en una cadena los underscore o dashed por espacios
     * @param string $s
     * @return string
     **/
    public static function humanize($s)
    {
        return strtr($s,'_-','  ');
    }
    /**
     * Merge Two Arrays Overwriting Values $a1
     * from $a2
     *
     * @param array $a1
     * @param array $a2
     * @return array
     */
    public static function array_merge_overwrite($a1, $a2)
    {
        foreach($a2 as $key2 => $value2){
            if(!is_array($value2)){
                $a1[$key2] = $value2;
            } else {
                if(!isset($a1[$key2])){
                    $a1[$key2] = null;
                }
                if(!is_array($a1[$key2])){
                    $a1[$key2] = $value2;
                } else {
                    $a1[$key2] = self::arrayMergeOverwrite($a1[$key2], $a2[$key2]);
                }
            }
        }
        return $a1;
    }
    /**
     * Insert para arrays numericos
     *
     * @param &array $array array donde se insertara (por referencia)
     * @param int $index indice donde se realizara la insercion
     * @param mixed $value valor a insertar
     **/
    public static function array_insert(&$array, $index, $value)
    {
        $array2 = array_splice($array, $index);
        array_push($array, $value);
        $array = array_merge($array, $array2);
    }

    /**
     * Convierte los argumentos de una funcion o metodo a parametros por nombre
     *
     * @param string $params argumentos de la funcion de donde se analizaran los argumentos
	 * @return array
     */
    public static function get_params($params)
    {
		$params = explode(', ', $params);
		$data = array();
		foreach($params as $p) {
			$match = explode(': ', $p, 2);
			$data[$match[0]] = $match[1];
		}
		return $data;
    }
	/**
	* Convierte los parametros de una funcion o metodo de parametros por nombre a un array
	*
	* @param array $params 
	* @return array
	*/
	public static function getParams($params){
        $data = array();
        foreach($params as $p) {
            if(is_string($p)) {
                $match = explode(': ', $p, 2);
                if(isset($match[1])) {
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
     * @param string $lista Cadena con Items separados por comas (,).
     * @return string Cadena con Items encerrados en doblecomillas y separados por comas (,).
     */
    public static function encomillar($lista)
    {
        $items = explode(',', $lista);
        $encomillada= '"'.implode('","',$items).'"';
        return $encomillada;
    }

    /**
     * Crea un path.
     *
     * @param string $path ruta a crear
     * @return boolean
     */
    public static function mkpath($path)
    {
        if(@mkdir($path) or file_exists($path)) return true;
        return (mkpath(dirname($path)) and mkdir($path));
    }

    /**
     * Elimina un directorio.
     *
     * @param string $dir ruta de directorio a eliminar
     * @return boolean
     */
    public static function removedir($dir)
    {
 
        /**
            Obtengo los archivos en el directorio a eliminar
        **/
        if($files = array_merge(glob("$dir/*"), glob("$dir/.*"))) {
            /**
                Elimino cada subdirectorio o archivo
            **/
            foreach($files as $file) {
                /**
                    Si no son los directorios "." o ".." 
                **/
                if(!preg_match("/^.*\/?[\.]{1,2}$/",$file)) {
                    if(is_dir($file)) {
                        return self::removeDir($file);
                    } elseif(!@unlink($file)) {
                        return false;
                    }
                }
            }
        }
        return @rmdir($dir);
    }
    /**
     * Coloca la primera letra en minuscula
     *
     * @param $s string cadena a convertir
     * @return string
     **/
    public static function lcfirst($s) {
			$s[0] = strtolower($s[0]);
		return $s;
    }
}