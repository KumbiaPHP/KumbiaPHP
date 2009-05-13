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
class Dispatcher
{
    /**
     * Objeto del controlador en ejecución
     *
     * @var mixed
     */
    private static $controller;
    /**
     * Directorio de controladores
     *
     * @var string
     */
    private static $controllers_dir;
    /**
     * Codigo de error cuando no encuentra la accion
     */
    const NOT_FOUND_ACTION = 100;
    const NOT_FOUND_CONTROLLER = 101;
    const NOT_FOUND_FILE_CONTROLLER = 102;
    const NOT_FOUND_INIT_ACTION = 103;

    /**
     * Establece el directorio de los controladores
     *
     * @param string $directory
     */
    static public function set_controllers_dir ($directory)
    {
        self::$controllers_dir = $directory;
    }
    /**
     * Realiza el dispatch de una ruta
     *
     * @return Object
     */
    static public function execute ()
    {
        extract(Router::get_vars());
        $config = Config::read('config.ini');
        $controllers_dir = APP_PATH . 'controllers';
        if ($module) {
            $controllers_dir = $controllers_dir . '/' . $module;
        }

        $app_controller = Util::camelcase($controller) . 'Controller';
        $file = "$controllers_dir/$controller".'_controller.php';
        if(is_file($file)){
			include_once $file;
			$activeController = new $app_controller();
			
			/**
			 * Verifica si el controlador esta persistente en la sesion
			 **/
			if ($activeController->persistent && isset($_SESSION['KUMBIA_CONTROLLERS'][APP_PATH]["$module/$controller"])) {
				$data = unserialize($_SESSION['KUMBIA_CONTROLLERS'][APP_PATH]["$module/$controller"]);
				foreach($data as $k=>$v) {
					$activeController->$k = $v;
				}
				$activeController->cache(false);
				$activeController->response = '';
			}
				
			$activeController->action_name = $action;
			$activeController->module_name = $module;
			$activeController->controller_name = $controller;
			$activeController->id = $id;
			$activeController->all_parameters = $all_parameters;
			$activeController->parameters = $parameters;
			$activeController->view = $action;
				
			/**
			 * Asigna el controlador activo
			 **/
			self::$controller = $activeController;
				
			/**
			 * Carga de modelos
			 **/
			if($config['application']['models_autoload']) {
				Load::all_models();
			} elseif($activeController->models) {
				call_user_func_array(array('Load', 'models'), $activeController->models);
			}
					
			/**
			 * Se ejecutan los filtros before
			 */
			$activeController->initialize();
			$activeController->before_filter();
                
			/**
			 * Se ejecuta el metodo con el nombre de la accion
			 * en la clase
			 */
			if (!method_exists($activeController, $action)) {
				throw new KumbiaException("No se encontró; la Acción \"$action\". Es necesario definir un método en la clase
					controladora '$controller' llamado '{$action}' para que
					esto funcione correctamente.", Dispatcher::NOT_FOUND_ACTION);
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

			/**
			 * Verifica si es persistente
			 *
			 **/
			if($activeController->persistent) {
				$_SESSION['KUMBIA_CONTROLLERS'][APP_PATH]["$module/$controller"] = serialize(get_object_vars($activeController));
			}

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
        return self::$controller;
    }
}