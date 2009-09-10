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
		$activeController = new ReflectionClass($app_controller);

		//Asigna el controlador activo
		self::$_controller = $activeController->newInstance($module, $controller, $action, $id, $all_parameters, $parameters);

        //Carga de modelos
		if(Config::get('config.application.database')) {
			if(Config::get('config.application.models_autoload')) {
				Load::models();
			} elseif(self::$_controller->models !== null) {
				Load::models(self::$_controller->models);
			}
		}
		
		// Se ejecutan los filtros before
		if(self::$_controller->initialize() === false) {
			return self::$_controller;
		}
		if(self::$_controller->before_filter() === false) {
			return self::$_controller;
		}

		//Se ejecuta el metodo con el nombre de la accion
		//en la clase de acuerdo al convenio
		if(!$activeController->hasMethod($action)){			
			throw new KumbiaException(null,'no_action');	
		}
		
		//Obteniendo el metodo
		$reflectionMethod = $activeController->getMethod($action);
		
		//se verifica que el metodo sea tenga modificador de acceso final
		if(!$reflectionMethod->isFinal()){
		    throw new KumbiaException(null,'no_action');
		}
		
		//se verifica que los parametros que recibe 
		//la action sea la cantidad correcta
        $num_params = count($parameters);
		if(self::$_controller->limit_params && ($num_params < $reflectionMethod->getNumberOfRequiredParameters()
            ||  $num_params > $reflectionMethod->getNumberOfParameters())){
            
			throw new KumbiaException('Parametros no cuadran');
		}
		$reflectionMethod->invokeArgs(self::$_controller, $parameters);

        //Corre los filtros after
		self::$_controller->after_filter();
		self::$_controller->finalize();

		//Elimino del controlador los modelos inyectados
		foreach (Load::get_injected_models() as $model) {
			unset(self::$_controller->$model);
		}
		
		//Limpia el buffer de modelos inyectados
		Load::reset_injected_models();

		return self::$_controller;
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