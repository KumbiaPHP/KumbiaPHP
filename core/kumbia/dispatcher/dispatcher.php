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
     * Codigo de error cuando no encuentra la accion
     */
    const NOT_FOUND_ACTION = 100;
    const NOT_FOUND_CONTROLLER = 101;
    const NOT_FOUND_FILE_CONTROLLER = 102;
    const NOT_FOUND_INIT_ACTION = 103;

    /**
     * Realiza el dispatch de una ruta
     *
     * @return Object
     */
    static public function execute ()
    {
        $router_vars = Router::get_vars();
		$action = $router_vars['action'];
		$controller = $router_vars['controller'];
		$module = $router_vars['module'];
		
        $controllers_dir = APP_PATH . 'controllers';
        if ($module) {
            $controllers_dir = $controllers_dir . '/' . $module;
        }

        $app_controller = Util::camelcase($controller) . 'Controller';
        $file = "$controllers_dir/$controller".'_controller.php';
        if(is_file($file)){
			include_once $file;
			/**
			 * Asigna el controlador activo
			 **/
			self::$_controller = $activeController = new $app_controller();

			/**
             * Carga de modelos
             **/
            if(Config::get('application.models_autoload')) {
                Load::all_models();
            } elseif($activeController->models !== null) {
                Load::models($activeController->models);
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
				throw new KumbiaException("No se encontró; la Acción \"$action\". Es necesario definir un método en la clase
					controladora '$controller' llamado '{$action}' para que
					esto funcione correctamente.", Dispatcher::NOT_FOUND_ACTION);
			}
			call_user_func_array(array($activeController , $action), $router_vars['parameters']);
				
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
        } else {
			throw new KumbiaException("No se encontr&oacute; la Clase Controladora \"{$app_controller}\".
				Debe definir esta clase para poder trabajar este controlador", self::NOT_FOUND_CONTROLLER);
        }
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