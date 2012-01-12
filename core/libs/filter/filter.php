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
 * Implementación de Filtros para Kumbia
 *
 * @category   Kumbia
 * @package    Filter
 * @copyright  Copyright (c) 2005-2009 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

/**
 * @see FilterInterface
 **/
require_once CORE_PATH . 'libs/filter/filter_interface.php';
 
/**
 * Implementación de Filtros para Kumbia
 *
 * @category   Kumbia
 * @package    Filter
 * @copyright  Copyright (c) 2005-2009 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */
class Filter
{
    /**
     * Aplica filtro de manera estatica
     *
     * @param mixed $s variable a filtrar
     * @param string $filter filtro
	 * @param array $options
     * @return mixed
     **/
    public static function get ($s, $filter, $options=array())
    {
		if(is_string($options)) {
			$filters = func_get_args();
            unset($filters[0]);
            
            $options = array();
			foreach($filters as $f) {
                $filter_class = Util::camelcase($f).'Filter';
                if(!class_exists($filter_class, false)) {
                    self::_load_filter($f);
                }
                
                $s = call_user_func(array($filter_class, 'execute'), $s, $options);
            }
		} else {
            $filter_class = Util::camelcase($filter).'Filter';
            if(!class_exists($filter_class, false)) {
                self::_load_filter($filter);
            }
            $s = call_user_func(array($filter_class, 'execute'), $s, $options);
		}
        
        return $s;
    }
    
    /**
     * Aplica los filtros a un array
     *
     * @param array $s variable a filtrar
     * @param string $filter filtro
	 * @param array $options
     * @return array
     **/
    public static function get_array($array, $filter, $options=array()) 
    {
        $args = func_get_args();

        foreach($array as $k => $v) {
            $args[0] = $v;
            $array[$k] = call_user_func_array(array('self', 'get'), $args);
        }
        
        return $array;
    }
    
    /**
     * Aplica filtros a un objeto
     * 
     * @param mixed $object
     * @param array $options
     * @return object
     **/
    public static function get_object($object, $filter, $options=array())
    {
        $args = func_get_args();

        foreach($object as $k => $v) {
            $args[0] = $v;
            $object->$k = call_user_func_array(array('self', 'get'), $args);
        }
        
        return $object;
    }
	/**
	 * Carga un Filtro
	 *
	 * @param string $filter filtro
	 * @throw KumbiaException
	 **/
	protected static function _load_filter($filter)
	{
		$file = APP_PATH . "extensions/filters/{$filter}_filter.php";
		if(!is_file($file)) {
			$file = CORE_PATH . "libs/filter/base_filter/{$filter}_filter.php";
			if(!is_file($file)) {
				throw new KumbiaException("Filtro $filter no encontrado");
			}
		}
        
        include $file;
	}
}