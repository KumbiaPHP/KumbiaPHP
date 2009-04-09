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
 * @category Kumbia
 * @package Controller
 * @subpackage BuilderController
 * @copyright  Copyright (c) 2005-2007 Andres Felipe Gutierrez (andresfelipe at vagoogle.net)
 * @license http://www.kumbia.org/license.txt GNU/GPL
 */

/**
 * @see BuilderControllerException
 */
require_once CORE_PATH.'library/kumbia/controller/builder/exception.php';

/**
 * Es la clase principal de implementacion de los coders
 *
 * @category Kumbia
 * @package Controller
 * @subpackage BuilderController
 * @copyright Copyright (c) 2005-2007 Andres Felipe Gutierrez (andresfelipe at vagoogle.net)
 * @license http://www.kumbia.org/license.txt GNU/GPL
 *
 */
class BuilderController extends ApplicationController {
	/**
	 * Crea un modelo base en models/
	 *
	 * @param string $name
	 * @param string $controller
	 * @param string $action
	 */
	public function create_model($name, $controller, $action){
		$models_dir = APP_PATH . 'models';
		if(file_exists("$models_dir/$name.php")){
			Flash::error("Error: El modelo '$name' ya existe\n");
		} else {
			$model_name = str_replace(" ", "", ucwords(strtolower(str_replace("_", "", $name))));
			$file = "<?php\n			\n	class $model_name extends ActiveRecord {\n
	}\n	\n?>\n";
			file_put_contents("$models_dir/$name.php", $file);
			Flash::success("Se cre&oacute; correctamente el modelo '$name' en models/$name.php\n");
			$model = $name;
			require_once "$models_dir/$model.php";
			$objModel = str_replace("_", " ", $model);
			$objModel = ucwords($objModel);
			$objModel = str_replace(" ", "", $objModel);
			if(!class_exists($objModel)){
				throw new BuilderControllerException("No se encontr&oacute; la Clase \"$objModel\" Es necesario definir una clase en el modelo
							'$model' llamado '$objModel' para que esto funcione correctamente.");
				return false;
			} else {
				Kumbia::$models[$objModel] = new $objModel($model, false);
				Kumbia::$models[$objModel]->source = $model;
			}
			router::route_to("controller: $controller", "action: $action");
		}
	}

	/**
	 * Crea un controlador en el directorio de controladores
	 *
	 * @param string $controller
	 * @param string $action
	 */
	public function create_controller($controller, $action){
	    $creado = false;
		$controllers_dir = APP_PATH . 'controllers';
		$file = strtolower($controller)."_controller.php";
		if(file_exists("$controllers_dir/$file")){
			Flash::error("Error: El controlador '$controller' ya existe\n");
		} else {
			if($this->post("kind")=="applicationcontroller"){
				$filec = "<?php\n			\n	class ".ucfirst($controller)."Controller extends ApplicationController {\n\n\t\tfunction $action(){\n\n\t\t}\n\n	}\n	\n?>\n";
				if(@file_put_contents("$controllers_dir/$file", $filec)){
				    $creado = true;
				}
			} else {
				$filec = "<?php\n			\n	class ".ucfirst($controller)."Controller extends StandardForm {\n\n\t\tpublic \$scaffold = true;\n\n\t\tpublic function __construct(){\n\n\t\t}\n\n	}\n	\n?>\n";
				file_put_contents("$controllers_dir/$file", $filec);
				if($this->create_model($controller, $controller, "index")){
				    $creado = true;
				}
			}
			if($creado){
			    Flash::success("Se cre&oacute; correctamente el controlador '$controller' en '$controllers_dir/$file'");
			}else {
			    Flash::error("Error: No se pudo escribir en el directorio, verifique los permisos sobre el directorio");
			}
			
		}
		router::route_to("controller: $controller", "action: $action");
	}

	public function index(){
		$this->redirect("");
	}

}