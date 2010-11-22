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
 * Cargador Selectiva
 *
 * @category   Kumbia
 * @package    Kumbia
 * @copyright  Copyright (c) 2005-2009 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */
class Load
{
    /**
     * Carga libreria de APP, si no existe carga del CORE
     *
     * @param string $lib libreria a cargar
     * @throw KumbiaException
     **/
    public static function lib ($lib)
    {
		$file = APP_PATH . "libs/$lib.php";
		if (is_file($file)) {
			return require_once $file;
		} else {
            return self::coreLib($lib);
		}
    }

    /**
     * Carga libreria del core
     *
     * @param string $lib libreria a cargar
     * @throw KumbiaException
     **/
    public static function coreLib ($lib)
    {
		if (! include_once CORE_PATH . "libs/$lib/$lib.php") {
			throw new KumbiaException("Librería: \"$lib\" no encontrada");
		}
    }
    /**
     * Obtiene la instancia de un modelo
     *
     * @param string $model
     * @return obj model
     */
    public static function model ($model)
    {
        //Nombre de la clase
        $Model = Util::camelcase(basename($model));
        //Carga la clase
        if (! class_exists($Model, FALSE)) {
            //Carga la clase
            if (! include_once APP_PATH . "models/$model.php") {
                throw new KumbiaException("No existe el modelo $model");
            }
        }
        return new $Model();
    }
    /**
     * Carga modelos
     *
     * @param string $model
     * @throw KumbiaException
     **/
    public static function models ($model)
    {
        if (is_array($model)) {
            $args = $model;
        } else {
            $args = func_get_args();
        }
        foreach ($args as $model) {
            $file = APP_PATH . "models/$model.php";
            if (is_file($file)) {
                include_once $file;
            } else {
                throw new KumbiaException("Modelo $model no encontrado");
            }
        }
    }
}
