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
            $file = CORE_PATH . "libs/$lib/$lib.php";
            if (! is_file($file)) {
                throw new KumbiaException("$lib no encontrada");
            }
        }
        include_once $file;
    }
    
    /**
     * Carga modelos
     *
     * @param string $model
     * @throw KumbiaException
     **/
    public static function models ($model = null)
    {
        /**
         * Si se utiliza base de datos
         **/
        if (! class_exists('Db', false)) {
            require CORE_PATH . 'libs/db/db.php';
        }
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
                self::_all_models($controller, $dir);
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
    private static function _all_models ($controller, $dir = null)
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
     * Inicia el boot
     *
     **/
    public static function boot ()
    {
        $boot = Config::read('boot');
        if (!empty($boot['modules']['libs'])) {
            $libs = explode(',', str_replace(' ', '', $boot['modules']['libs']));
            foreach ($libs as $lib) {
                self::lib($lib);
            }
            unset($libs);
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
        /**
         * Si se utiliza base de datos
         **/
        if (! class_exists('Db', false)) {
            require CORE_PATH . 'libs/db/db.php';
        }
        /**
         * Nombre de la clase
         **/
        $Model = Util::camelcase(basename($model));
        /**
         * Carga la clase
         **/
        if (! class_exists($Model, false)) {
            /**
             * Carga la clase
             **/
            $file = APP_PATH . "models/$model.php";
            if (! is_file($file)) {
                throw new KumbiaException("No existe el modelo $model");
            }
            include $file;
        }
        return new $Model();
    }
    
}