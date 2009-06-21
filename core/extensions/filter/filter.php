<?php
/**
 * Kumbia PHP Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://XXXXXXXX
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Kumbia
 * @package    Filter
 * @copyright  Copyright (c) 2005-2009 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
 
/**
 * @see FilterInterface
 **/
require_once CORE_PATH . 'extensions/filter/filter_interface.php';
 
/**
 * Implementación de Filtros para Kumbia
 *
 * @category  Kumbia
 * @package   Filter
 * @copyright  Copyright (c) 2005-2009 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version   SVN:$id
 */
class Filter
{
    /**
     * Aplica filtro pero de manera estatica
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
		} else {
			$filters = array($filter);
		}
        
        return self::_apply_filters($s, $filters, $options);
    }
    /**
     * Aplica filtros recursivamente de manera estatica
     * 
     * @param mixed $s
     * @param array $options
     * @return mixed
     **/
    protected static function _apply_filters($s, $filters, $options)
    {
       if (is_array($s)) {
            foreach ($s as $key => $value) {
                if (is_array($value) || is_object($value)) {
                    return self::_apply_filters($s[$key], $filters, $options);
                } elseif (is_string($value)) {
                    foreach ($filters as $f) {
						$filter = Util::camelcase($f).'Filter';
						if(!class_exists($filter)) {
							self::_load_filter($f);
						}
                        $s[$key] = call_user_func(array($filter , 'execute'), $value, $options);
                    }
                }
            }
        } elseif (is_object($s)) {
            foreach (get_object_vars($s) as $attr => $value) {
                if (is_array($value) || is_object($value)) {
                    return self::_apply_filters($s->$attr, $filters, $options);
                } elseif (is_string($value)) {
                    foreach ($filters as $f) {
						$filter = Util::camelcase($f).'Filter';
						if(!class_exists($filter)) {
							self::_load_filter($f);
						}
                        $s->$attr = call_user_func(array($filter, 'execute'), $value, $options);
                    }
                }
            }
        } elseif (is_string($s)) {
            foreach ($filters as $f) {
				$filter = Util::camelcase($f).'Filter';
				if(!class_exists($filter)) {
					self::_load_filter($f);
				}
                $s = call_user_func(array($filter, 'execute'), $s, $options);
            }
        }
        return $s;
    }
	/**
	 * Carga un Filtro
	 *
	 * @param string $filter filtro
	 * @throw KumbiaException
	 **/
	protected static function _load_filter($filter)
	{
		$file = CORE_PATH . "extensions/filter/base_filters/{$filter}_filter.php";
		if(!file_exists($file)) {
			$file = APP_PATH . "filters/{$filter}_filter.php";
			if(!file_exists($file)) {
				throw new KumbiaException("Filtro $filter no encontrado");
			}
		}
		include $file;
	}
}