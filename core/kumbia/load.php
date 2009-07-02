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
	 * Carga librerias del core 
	 *
	 * @param string $dir directorio ubicado en el core
	 * @param string $lib libreria a cargar
	 * @param boolean $convenant utilizar convenio
	 * @throw KumbiaException
	 **/
	public static function modules($dir, $lib, $convenant=false)
	{
		if($convenant) {
			$file = APP_PATH . "modules/$dir/$lib/$lib.php";
		} else {
			$file = APP_PATH . "modules/$dir/$lib.php";
		}
		
		if (!is_file($file)) {
            if($convenant) {
                $file = CORE_PATH . "modules/$dir/$lib/$lib.php";
            } else {
                $file = CORE_PATH . "modules/$dir/$lib.php";
            }
        
            if (!is_file($file)) {
                throw new KumbiaException("$dir $lib no encontrada");
            }
		}
        include_once $file;
	}
    /**
     * Carga las extensions
     *
     * @param string $extension
     * @throw KumbiaException
     **/
    public static function extensions ($extension)
    {
        $args = func_get_args();
        foreach ($args as $extension) {
			self::modules('extensions', $extension, true);
        }
    }
    /**
     * Carga librerias de terceros
     *
     * @param string $vendor
     * @throw KumbiaException
     **/
    public static function vendors ($vendor)
    {
        $args = func_get_args();
        foreach ($args as $vendor) {
			self::modules('vendors', $vendor, true);
        }
    }
    /**
     * Carga los helpers
     *
     * @param string $helper
     * @throw KumbiaException
     **/
    public static function helpers ($helper)
    {
        $args = func_get_args();
        foreach ($args as $helper) {
            self::modules('helpers', $helper);
        }
    }
    /**
     * Carga un Utils
     *
     * @param string $utils
     */
    public static function utils($utils)
    {
        $args = func_get_args();
        foreach ($args as $util) {
            self::modules('utils', $util);
        }
    }
    /**
     * Carga modelos
     *
     * @param string $model
     * @throw KumbiaException
     **/
    public static function models ($model=null)
    {
        /**
         * Si se utiliza base de datos
         **/
        if (! class_exists('Db', false) && Config::get('config.application.database')) {
            require CORE_PATH . 'modules/extensions/db/db.php';
        }
		
		$controller = Dispatcher::get_controller();
		
        if(!$model) {
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
    public static function _all_models ($controller, $dir=null)
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
        $boot = Config::read('boot.ini');
        if (isset($boot['modules']['vendors']) && $boot['modules']['vendors']) {
            $vendors = explode(',', str_replace(' ', '', $boot['modules']['vendors']));
            foreach ($vendors as $vendor) {
                self::modules('vendors', $vendor, true);
            }
            unset($vendors);
        }
        if (isset($boot['modules']['extensions']) && $boot['modules']['extensions']) {
            $extensions = explode(',', str_replace(' ', '', $boot['modules']['extensions']));
            foreach ($extensions as $extension) {
                self::modules('extensions', $extension, true);
            }
            unset($extensions);
        }
        if (isset($boot['modules']['utils']) && $boot['modules']['utils']) {
            $utils = explode(',', str_replace(' ', '', $boot['modules']['utils']));
            foreach ($utils as $util) {
                self::modules('utils', $util);
            }
            unset($utils);
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
        if (! class_exists('Db', false) && Config::get('config.application.database')) {
            require CORE_PATH . 'modules/extensions/db/db.php';
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
            if (!is_file($file)) {
                throw new KumbiaException("No existe el modelo $model");
            }
            include $file;
        }
        return new $Model();
    }
}