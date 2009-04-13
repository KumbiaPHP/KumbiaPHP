<?php
/**
 * Kumbia PHP Framework
 *
 * LICENSE
 *
 * This source file is subject to the GNU/GPL that is bundled
 * with this package in the file docs/LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.kumbia.org/license.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kumbia@kumbia.org so we can send you a copy immediately.
 *
 * @category   Kumbia
 * @package    Controller
 * @subpackage Dispatcher
 * @copyright  Copyright (c) 2005-2007 Andres Felipe Gutierrez (andresfelipe at vagoogle.net)
 * @copyright  Copyright (c) 2008-2008 Emilio Rafael Silveira Tovar (emilio.rst at gmail.com)
 * @copyright  Copyright (c) 2007-2009 Deivinson Jose Tejeda Brito (deivinsontejeda at gmail.com)
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt GNU/GPL
 */

/**
 * Clase para manejar excepciones ocurridas en la clase Logger
 *
 * @category  Kumbia
 * @package   Dispatcher
 * @abstract
 * @copyright Copyright (c) 2005-2007 Andres Felipe Gutierrez (andresfelipe at vagoogle.net)
 * @license   http://www.kumbia.org/license.txt GNU/GPL
 * @access    public
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

        $app_controller = camelize($controller) . 'Controller';
        $file = "$controllers_dir/$controller".'_controller.php';
        if(include_once $file){
            /*
             * incluyendo el controller
             */
            //require_once "$controllers_dir/$controller".'_controller.php';
            if (class_exists($app_controller)) {
                /*
                 * se obtiene la superclase
                 */
                $super_class = get_parent_class($app_controller);
                if ($super_class='StandardForm') {
                    require_once CORE_PATH . 'generator/generator.php';
                }

				/**
				 * Verifica si el controlador esta persistente en la sesion
				 **/
                if (isset($_SESSION['KUMBIA_CONTROLLERS'][APP_PATH]["$module/$controller"])) {
					$activeController = unserialize($_SESSION['KUMBIA_CONTROLLERS'][APP_PATH]["$module/$controller"]);
                } else {
					$activeController = new $app_controller();
                    $activeController->module_name = $module;
                    $activeController->controller_name = $controller;
                }
				
                $activeController->response = '';
                $activeController->action_name = $action;
                $activeController->view = $action;

                $activeController->id = $id;
                $activeController->all_parameters = $all_parameters;
                $activeController->parameters = $parameters;
                try {
                    /**
                     * Se ejecutan los filtros before
                     */
                    $activeController->initialize();
                    $activeController->before_filter();
                    /**
                     * Se agrega una referencia a los modelos como propiedades del controlador
                     */
                    foreach (Kumbia::$models as $model_name => $model) {
                        $activeController->{$model_name} = $model;
                    }
                    /**
                     * Se ejecuta el metodo con el nombre de la accion
                     * en la clase
                     */
                    if (! method_exists($activeController, $action)) {
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
                } catch (Exception $e) {
                    $cancel_throw_exception = false;
                    throw $e;
                }

                try {
                    foreach (Kumbia::$models as $model_name => $model) {
                        unset($activeController->{$model_name});
                    }

					/**
					 * Verifica si es persistente
					 *
					 **/
					if($activeController->persistent) {
						$_SESSION['KUMBIA_CONTROLLERS'][APP_PATH]["$module/$controller"] = serialize($activeController);
					}
                } catch (PDOException $e) {
                    throw new KumbiaException($e->getMessage(), $e->getCode());
                }

                self::$controller = $activeController;
                return $activeController;
            } else {
                throw new KumbiaException("
                    No se encontr&oacute; el Clase Controladora \"{$app_controller}\".
                    Debe definir esta clase para poder trabajar este controlador", self::NOT_FOUND_CONTROLLER);
            }
        } else {
            if (Config::get('config.application.interactive')) {
                /**
                 * @see InteractiveBuilder
                 */
                require CORE_PATH.'generator/builder.php';
                InteractiveBuilder::create_controller($controller, $action);
                throw new KumbiaException("No se encontr&oacute; el Controlador \"$controllers_dir/$controller\". Hubo un problema al cargar el controlador, probablemente
                    el archivo no exista en el directorio de módulos o exista algun error de sintaxis.", self::NOT_FOUND_FILE_CONTROLLER);
            } else {
                return false;
            }
        }
    }
    /**
     * Obtener el controlador en ejecucion
     *
     * @return mixed
     */
    public static function get_controller ()
    {
        return self::$controller;
    }
}