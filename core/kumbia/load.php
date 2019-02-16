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
 * Cargador Selectivo.
 *
 * Clase para la carga de librerias tanto del core como de la app.
 * Carga de los modelos de una app.
 *
 * @category   Kumbia
 */
class Load
{
    /**
     * Carga libreria de APP, si no existe carga del CORE.
     *
     * @param string $lib libreria a cargar
     * @throw KumbiaException
     */
    public static function lib($lib)
    {
        $file = APP_PATH."libs/$lib.php";
        if (is_file($file)) {
            return include $file;
        }

        return self::coreLib($lib);
    }

    /**
     * Carga libreria del core.
     *
     * @param string $lib libreria a cargar
     * @throw KumbiaException
     */
    public static function coreLib($lib)
    {
        if (!include CORE_PATH."libs/$lib/$lib.php") {
            throw new KumbiaException("Librería: \"$lib\" no encontrada");
        }
    }

    /**
     * Obtiene la instancia de un modelo.
     *
     * @param string $model  modelo a instanciar en small_case
     * @param array  $params parámetros para instanciar el modelo
     *
     * @return obj model
     */
    public static function model($model, array $params = array())
    {
        //Nombre de la clase
        $Model = Util::camelcase(basename($model));
        //Si no esta cargada la clase
        if (!class_exists($Model, false)) {
            //Carga la clase
            if (!include APP_PATH."models/$model.php") {
                throw new KumbiaException($model, 'no_model');
            }
        }

        return new $Model($params);
    }

    /**
     * Carga modelos.
     *
     * @param string $model en small_case
     * @throw KumbiaException
     */
    public static function models($model)
    {
        $args = is_array($model) ? $model : func_get_args();
        foreach ($args as $model) {
            $Model = Util::camelcase(basename($model));
            //Si esta cargada continua con la siguiente clase
            if (class_exists($Model, false)) {
                continue;
            }
            if (!include APP_PATH."models/$model.php") {
                throw new KumbiaException($model, 'no_model');
            }
        }
    }
}
