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
 * Clase para manejar las peticiones de KumbiaPHP Framework
 * 
 * @category   Kumbia
 * @package    Dispatcher 
 * @copyright  Copyright (c) 2005-2009 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */
final class Dispatcher
{
    /**
     * Objeto del controlador en ejecución
     *
     * @var mixed
     */
    private static $_controller;

    /**
     * Realiza el dispatch de una ruta
     *
     * @return Object
     */
    static public function execute ()
    {
        extract(Router::get(), EXTR_OVERWRITE);
		
        $controllers_dir = APP_PATH . 'controllers';
        if ($module) {
            $controllers_dir = $controllers_dir . '/' . $module;
        }

        $app_controller = Util::camelcase($controller) . 'Controller';
        $file = "$controllers_dir/$controller".'_controller.php';
        if(!is_file($file)){
			throw new KumbiaException(null,'no_controller');
		}
		include_once $file;
		/**
		  * Asigna el controlador activo
		 **/
		self::$_controller = $activeController = new $app_controller($module, $controller, $action, $id, $all_parameters, $parameters);

		/**
         * Carga de modelos
        **/
		if(Config::get('config.application.database')) {
			if(Config::get('config.application.models_autoload')) {
				Load::models();
			} elseif($activeController->models !== null) {
				Load::models($activeController->models);
			}
		}
		/**
		 * Se ejecutan los filtros before
		 */
		if($activeController->initialize() === false) {
			return $activeController;
		}
		if($activeController->before_filter() === false) {
			return $activeController;
		}
			
		/**
		 * Se ejecuta el metodo con el nombre de la accion
		 * en la clase
		 */
		if (!method_exists($activeController, $action)) {			
			throw new KumbiaException(null,'no_action');	
		}
		call_user_func_array(array($activeController , $action), $parameters);
				
		/**
		 * Corre los filtros after
		 */
		$activeController->after_filter();
		$activeController->finalize();

		/**
		 * Elimino del controlador los modelos inyectados
		 **/
		foreach (Load::get_injected_models() as $model) {
			unset($activeController->$model);
		}
		/**
		 * Limpia el buffer de modelos inyectados
		 **/
		Load::reset_injected_models();

		return $activeController;
    }
    /**
     * Obtener el controlador en ejecucion
     *
     * @return mixed
     */
    public static function get_controller()
    {
        return self::$_controller;
    }
}
