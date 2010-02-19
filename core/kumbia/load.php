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
     * Modelos inyectados en los controladores
     *
     * @var array
     **/
    protected static $_injected_models = array();
    /**
     * Carga librerias 
     *
     * @param string $lib libreria a cargar
     * @throw KumbiaException
     **/
    public static function lib ($lib)
    {
		$file = APP_PATH . "libs/$lib.php";
		if (! is_file($file)) {
			if (! include_once(CORE_PATH . "libs/$lib/$lib.php")) {
                throw new KumbiaException("Librería: \"$lib\" no encontrada");
			}
            
            return;
		}
		
		include_once $file;
    }
    
    /**
     * Carga modelos
     *
     * @param string $model
     * @throw KumbiaException
     **/
    public static function models ($model = NULL)
    {
        //Si se utiliza base de datos

        require_once CORE_PATH . 'libs/db/db.php';

        $controller = Dispatcher::get_controller();
        if (! $model) {
            self::_all_models($controller);
            return;
        } elseif (is_array($model)) {
            $args = $model;
        } else {
            $args = func_get_args();
        }
        foreach ($args as $model) {
            $file = APP_PATH . "models/$model.php";
            if (is_file($file)) {
                include_once $file;
                if ($controller) {
                    $Model = Util::camelcase(basename($model));
                    $controller->$Model = new $Model();
                    self::$_injected_models[] = $Model;
                }
            } elseif (is_dir(APP_PATH . "models/$model")) {
                self::_all_models($controller, $model);
            } else {
                throw new KumbiaException("Modelo $model no encontrado");
            }
        }
    }
    /**
     * Carga todos los modelos
     *
     * @param Controller $controller controlador
     * @param string $dir directorio a cargar
     **/
    private static function _all_models ($controller, $dir = NULL)
    {
        foreach (new DirectoryIterator(APP_PATH . "models/$dir") as $file) {
            if ($file->isDot() || $file->isDir()) {
                continue;
            }
            if ($file->isFile()) {
                include_once $file->getPathname();
                if ($controller) {
                    $Model = Util::camelcase(basename($file->getFilename(), '.php'));
                    $controller->$Model = new $Model();
                    self::$_injected_models[] = $Model;
                }
            }
        }
    }
    /**
     * Obtiene los nombres de modelos inyectados en los controladores
     * en notacion CamelCase (es decir el nombre de clase)
     *
     * @return array
     **/
    public static function get_injected_models ()
    {
        return self::$_injected_models;
    }
    /**
     * Limpia el buffer de modelos inyectados
     *
     **/
    public static function reset_injected_models ()
    {
        self::$_injected_models = array();
    }
    /**
     * Obtiene la instancia de un modelo
     *
     * @param string $model
     * @return obj model
     */
    public static function model ($model)
    { 
        //Si se utiliza base de datos
        require_once CORE_PATH . 'libs/db/db.php';
        //Nombre de la clase
        $Model = Util::camelcase(basename($model));
        //Carga la clase
        if (! class_exists($Model, FALSE)) {
            //Carga la clase
            if (! include APP_PATH . "models/$model.php") {
                throw new KumbiaException("No existe el modelo $model");
            }
        }
        return new $Model();
    }
    
}