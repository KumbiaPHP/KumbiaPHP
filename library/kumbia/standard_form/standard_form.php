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
 * @subpackage StandardForm
 * @copyright  Copyright (c) 2005-2007 Andres Felipe Gutierrez (andresfelipe at vagoogle.net)
 * @copyright  Copyright (c) 2008-2008 Emilio Rafael Silveira Tovar (emilio.rst at gmail.com)
 * @copyright  Copyright (c) 2007-2008 Deivinson Jose Tejeda Brito (deivinsontejeda at gmail.com)
 * @license http://www.kumbia.org/license.txt GNU/GPL
 */

/**
 * La Clase StandardForm es la base principal para la generacin de formularios
 * de tipo Standard
 *
 * Notas de Version:
 * Desde Kumbia-0.4.7, StandardForm mantiene los valores de la entrada
 * cuando los metodos before_ o validation devuelven false;
 *
 * @category Kumbia
 * @package Controller
 * @subpackage StandardForm
 * @copyright Copyright (c) 2005-2007 Andres Felipe Gutierrez (andresfelipe at vagoogle.net)
 * @license http://www.kumbia.org/license.txt GNU/GPL
 */
abstract class StandardForm extends ApplicationController  {

	/**
	 * Tabla con la que trabaja el formulario
	 *
	 * @var string
	 */
	public $source;

	/**
	 * Lista de campos a ignorar en la generaci�n
	 *
	 * @var array
	 */
	public $ignore_list = array();

	/**
	 * Array que contiene los meta-datos internos
	 * para generar el formulario
	 *
	 * @var string
	 */
	public $form = array();

	/**
	 * Hace que el formulario no sea persistente
	 *
	 * @var unknown_type
	 */
	static public $force = false;

	/**
	 * Numero de Minutos que el layout ser� cacheado
	 *
	 * @var integer
	 */
	public $cache_layout = 0;

	/**
	 * Numero de Minutos que la vista ser� cacheada
	 *
	 * @var integer
	 */
	public $cache_view = 0;

	/**
	 * Indica si se deben leer los datos de la base
	 * de datos para generar el formulario
	 *
	 * @var boolean
	 */
	public $scaffold = false;

	/**
	 * Indica el tipo de respuesta que ser� generado
	 *
	 * @var string
	 */
	public $view;

	/**
	 * Indica si se debe mantener los valores del
	 * formulario o no
	 *
	 * @var boolean
	 */
	public $keep_action = true;

	/**
	 * Mensaje de exito al insertar
	 *
	 * @var string
	 */
	public $success_insert_message = "";

	/**
	 * Mensaje de Fallo al insertar
	 *
	 * @var string
	 */
	public $failure_insert_message = "";

	/**
	 * Mensaje de Suceso al Actualizar
	 *
	 * @var string
	 */
	public $success_update_message = "";

	/**
	 * Mensaje de Fallo al Actualizar
	 *
	 * @var string
	 */
	public $failure_update_message = "";

	/**
	 * Mensaje de Exito al Borrar
	 *
	 * @var string
	 */
	public $success_delete_message = "";

	/**
	 * Mensaje de fallo al borrar
	 *
	 * @var string
	 */
	public $failure_delete_message = "";

	public function __construct(){
		if(method_exists($this, "initialize")){
			$this->initialize();
		}
	}

	/**
	 * Obtiene el Valor de Source cuando no esta disponible
	 */
	public function __get($property){
		if($property=="source"){
			if(!$this->source){
				ActiveRecord::sql_sanizite($_REQUEST["controller"]);
				return $this->source = $_REQUEST["controller"];
			}
		}
		return $this->$property;
	}

	/**
	 * Emula la acci&oacute;n Report llamando a show
	 */
	public public function report(){

		$this->view = 'index';

		if(!kumbia::is_model($this->source)){
			throw new KumbiaException('No hay un modelo "'.$this->source.'" para hacer la operaci&oacute;n de reporte');
			$this->_create_model();
			return router::route_to('action: index');
		}

		$modelName = camelize($this->source);

		if(!$this->{$modelName}->is_dumped()){
			$this->{$modelName}->dump_model();
		}

		foreach($this->{$modelName}->attributes_names as $field_name){
			if(isset($_REQUEST["fl_$field_name"])){
				$this->{$modelName}->$field_name = $_REQUEST["fl_$field_name"];
			}

		}

		/**
		 * Busca si existe un m&eacute;todo o un llamado variable al m&eacute;todo
		 * before_report, si este m&eacute;todo devuelve false termina la ejecuci�n
		 * de la acci&oacute;n
		 */
		if(method_exists($this, "before_report")){
			if($this->before_report()===false){
				return null;
			}
			if(Router::get_routed()){
				return null;
			}
		} else {
			if(isset($this->before_report)){
				if($this->{$this->before_report}()===false){
					return null;
				}
				if(Router::get_routed()){
					return null;
				}
			}
		}

		require_once CORE_PATH.'library/kumbia/report/report.php';
		Generator::scaffold(&$this->form, $this->scaffold);
		Report::generate($this->form);

		/**
		 * Busca si existe un m&eacute;todo o un llamado variable al m&eacute;todo
		 * after_insert, si este m&eacute;todo devuelve false termina la ejecuci�n
		 * de la acci&oacute;n
		 */
		if(method_exists($this, "after_report")){
			if($this->after_report()===false){
				return null;
			}
			if(Router::get_routed()){
				return null;
			}
		} else {
			if(isset($this->after_report)){
				if($this->{$this->after_report}()===false){
					return null;
				}
				if(Router::get_routed()){
					return null;
				}
			}
		}

		return router::route_to('action: index');
	}

	/**
	 * Invoca al Kumbia Builder a crear un modelo en caso de que no exista
	 */
	private function _create_model(){
		$config = Config::read("config.ini");
		$active_app = Router::get_application();
		if(!$config->$active_app->interactive){
			InteractiveBuilder::create_model($this->source, $this->controller_name, $this->action_name);
		}
	}

	/**
	 * Metodo Insert por defecto del Formulario
	 *
	 */
	public function insert(){

		$this->view = 'index';
		$this->keep_action = "";

		Generator::scaffold(&$this->form, $this->scaffold);

		if(!kumbia::is_model($this->source)){
			throw new KumbiaException('No hay un modelo "'.$this->source.'" para hacer la operaci&oacute;n de inserci&oacute;n');
			$this->_create_model();
			return router::route_to('action: index');
		}

		$modelName = camelize($this->source);

		if(!$this->{$modelName}->is_dumped()){
			$this->{$modelName}->dump_model();
		}

		foreach($this->{$modelName}->attributes_names as $field_name){
		    if(isset($_REQUEST["fl_$field_name"])){
		        $this->{$modelName}->$field_name = $_REQUEST["fl_$field_name"];
		    }
		}

		/**
		 * Busca si existe un m&eacute;todo o un llamado variable al m&eacute;todo
		 * validation, si este m&eacute;todo devuelve false termina la ejecuci�n
		 * de la acci&oacute;n
		 */
		if(method_exists($this, "validation")){
			if($this->validation()===false){
				$this->keep_action = "insert";
				if(!Router::get_routed()){
					return router::route_to('action: index');
				}
			}
			if(Router::get_routed()){
				return;
			}
		} else {
			if(isset($this->validation)){
				if($this->{$this->validation}()===false){
					$this->keep_action = "insert";
					if(!Router::get_routed()){
						return router::route_to('action: index');
					}
				}
				if(Router::get_routed()){
					return;
				}
			}
		}

		/**
		 * Busca si existe un m&eacute;todo o un llamado variable al m&eacute;todo
		 * before_insert, si este m&eacute;todo devuelve false termina la ejecucin
		 * de la acci&oacute;n
		 */
		if(method_exists($this, "before_insert")){
			if($this->before_insert()===false){
				$this->keep_action = "insert";
				if(!Router::get_routed()){
					return router::route_to('action: index');
				}
			}
			if(Router::get_routed()){
				return;
			}
		} else {
			if(isset($this->before_insert)){
				if($this->{$this->before_insert}()===false){
					$this->keep_action = "insert";
					if(!Router::get_routed()){
						return router::route_to('action: index');
					}
				}
				if(Router::get_routed()){
					return;
				}

			}
		}

		/**
		 * Subimos los archivos de Imagenes del Formulario
		 */
		foreach($this->form['components'] as $fkey => $rrow){
			if($this->form['components'][$fkey]['type']=='image'){
				if(isset($_FILES["fl_".$fkey])){
					move_uploaded_file($_FILES["fl_".$fkey]['tmp_name'], htmlspecialchars("public/img/upload/{$_FILES["fl_".$fkey]['name']}"));
					$this->{$modelName}->$fkey = urlencode(htmlspecialchars("upload/".$_FILES["fl_".$fkey]['name']));
				}
			}
		}

		/**
		 * Utilizamos el modelo ActiveRecord para insertar el registro
		 * por lo tanto los
		 */
		$this->{$modelName}->id = null;
		if($this->{$modelName}->create()){
			if($this->success_insert_message){
				Flash::success($this->success_insert_message);
			} else {
				Flash::success("Se insert&oacute; correctamente el registro");
			}
		} else {
			if($this->failure_insert_message){
				Flash::error($this->failure_insert_message);
			} else {
				Flash::error("Hubo un error al insertar el registro");
			}
		}

		foreach($this->{$modelName}->attributes_names as $field_name){
			$_REQUEST["fl_$field_name"] = $this->{$modelName}->$field_name;
		}

		/**
		 * Busca si existe un m&eacute;todo o un llamado variable al m&eacute;todo
		 * after_insert
		 */
		if(method_exists($this, "after_insert")){
			$this->after_insert();
			if(Router::get_routed()){
				return;
			}
		} else {
			if(isset($this->after_insert)){
				$this->{$this->after_insert}();
			}
			if(Router::get_routed()){
				return;
			}
		}

		// Muestra el Formulario en la accion show
		return router::route_to('action: index');

	}

	/**
	 * Emula la acci&oacute;n Update llamando a show
	 *
	 */
	public function update(){

		$this->view = 'index';
		$this->keep_action = "";

		Generator::scaffold(&$this->form, $this->scaffold);

		if(!kumbia::is_model($this->source)){
			throw new KumbiaException('No hay un modelo "'.$this->source.'" para hacer la operaci&oacute;n de actualizaci&oacute;n');
			$this->_create_model();
			return router::route_to('action: index');
		}

		$modelName = camelize($this->source);

		if(!$this->{$modelName}->is_dumped()){
			$this->{$modelName}->dump_model();
		}

		/**
		 * Subimos los archivos de Im&aacute;genes del Formulario
		 */
		foreach($this->form['components'] as $fkey => $rrow){
			if($this->form['components'][$fkey]['type']=='image'){
				if(isset($_FILES["fl_".$fkey])){
					move_uploaded_file($_FILES["fl_".$fkey]['tmp_name'], htmlspecialchars("public/img/upload/{$_FILES["fl_".$fkey]['name']}"));
					$this->{$modelName}->$fkey = urlencode(htmlspecialchars("upload/".$_FILES["fl_".$fkey]['name']));
				}
			}
		}

		foreach($this->{$modelName}->attributes_names as $field_name){
			if(isset($_REQUEST["fl_$field_name"])){
				$this->{$modelName}->$field_name = $_REQUEST["fl_$field_name"];
			}
		}

		/**
		 * Busca si existe un m&eacute;todo o un llamado variable al m&eacute;todo
		 * validation, si este m&eacute;todo devuelve false termina la ejecuci�n
		 * de la acci&oacute;n
		 */
		if(method_exists($this, "validation")){
			if($this->validation()===false){
				$this->keep_action = "update";
				if(!Router::get_routed()){
					return router::route_to('action: index');
				}
			}
			if(Router::get_routed()){
				return;
			}
		} else {
			if(isset($this->validation)){
				if($this->{$this->validation}()===false){
					$this->keep_action = "update";
					if(!Router::get_routed()){
						return router::route_to('action: index');
					}
				}
				if(Router::get_routed()){
					return;
				}
			}
		}

		/**
		 * Busca si existe un metodo o un llamado variable al metodo
		 * before_update, si este metodo devuelve false termina la ejecucion
		 * de la accion
		 */
		if(method_exists($this, "before_update")){
			if($this->before_update()===false){
				$this->keep_action = "update";
				if(!Router::get_routed()){
					return router::route_to('action: index');
				}
			}
			if(Router::get_routed()){
				return null;
			}
		} else {
			if(isset($this->before_update)){
				if($this->{$this->before_update}()===false){
					$this->keep_action = "update";
					if(!Router::get_routed()){
						return router::route_to('action: index');
					}
				}
				if(Router::get_routed()){
					return null;
				}
			}
		}

		/**
		 * Utilizamos el modelo ActiveRecord para actualizar el registro
		 */
		if($this->{$modelName}->update()){
			if($this->success_update_message){
				Flash::success($this->success_update_message);
			} else {
				Flash::success("Se actualiz&oacute; correctamente el registro");
			}
		} else {
			if($this->failure_update_message){
				Flash::error($this->failure_update_message);
			} else {
				Flash::error("Hubo un error al actualizar el registro");
			}
		}

		foreach($this->{$modelName}->attributes_names as $field_name){
			$_REQUEST["fl_$field_name"] = $this->{$modelName}->$field_name;
		}

		/**
		 * Busca si existe un m&eacute;todo o un llamado variable al m&eacute;todo
		 * after_update
		 */
		if(method_exists($this, "after_update")){
			$this->after_update();
			if(Router::get_routed()){
				return;
			}
		} else {
			if(isset($this->after_update)){
				$this->{$this->after_update}();
				if(Router::get_routed()){
					return;
				}
			}
		}

		// Muestra el Formulario en la accion index
		return router::route_to('action: index');

	}

	/**
	 * Permite mostrar/ocultar los asteriscos al lado
	 * de los componentes del formulario
	 *
	 * @param boolean $option
	 */
	public function show_not_nulls($option = true){
		$this->form['show_not_nulls'] = $option;
	}

	/*
	 * Muestra una leyenda personalizada para los campos not_null
	 * @param string
	 */
	public function set_message_not_null($msj=''){
		$this->form['msj_not_null'] = $msj;
	}

	/**
	 * Emula la accion Delete llamando a show
	 *
	 */
	public function delete(){

		$this->view = 'index';

		Generator::scaffold(&$this->form, $this->scaffold);

		if(!kumbia::is_model($this->source)){
			throw new KumbiaException('No hay un modelo "'.$this->source.'" para hacer la operaci&oacute;n de eliminaci&oacute;n');
			$this->_create_model();
			return router::route_to('action: index');
		}

		$modelName = camelize($this->source);

		if(!$this->{$modelName}->is_dumped()){
			$this->{$modelName}->dump_model();
		}

		foreach($this->{$modelName}->attributes_names as $field_name){
			if(isset($_REQUEST["fl_$field_name"])){
				$this->{$modelName}->$field_name = $_REQUEST["fl_$field_name"];
			} else {
				$this->{$modelName}->$field_name = "";
			}
		}

		/**
		 * Busca si existe un m&eacute;todo o un llamado variable al m&eacute;todo
		 * before_delete, si este m&eacute;todo devuelve false termina la ejecuci�n
		 * de la acci&oacute;n
		 */
		if(method_exists($this, "before_delete")){
			if($this->before_delete()===false){
				if(!Router::get_routed()){
					return router::route_to('action: index');
				}
			}
			if(Router::get_routed()){
				return null;
			}
		} else {
			if(isset($this->before_delete)){
				if($this->{$this->before_delete}()===false){
					if(!Router::get_routed()){
						return router::route_to('action: index');
					}
				}
				if(Router::get_routed()){
					return null;
				}
			}
		}

		/**
		 * Utilizamos el modelo ActiveRecord para eliminar el registro
		 */
		if($this->{$modelName}->delete()){
			if($this->success_delete_message){
				Flash::success($this->success_delete_message);
			} else {
				Flash::success("Se elimin&oacute; correctamente el registro");
			}
		} else {
			if($this->failure_delete_message){
				Flash::error($this->failure_delete_message);
			} else {
				Flash::error("Hubo un error al eliminar el registro");
			}
		}

		foreach($this->{$modelName}->attributes_names as $field_name){
			$_REQUEST["fl_$field_name"] = $this->{$modelName}->$field_name;
		}

		/**
		 * Busca si existe un m&eacute;todo o un llamado variable al m&eacute;todo
		 * after_delete
		 */
		if(method_exists($this, "after_delete")){
			$this->after_delete();
			if(Router::get_routed()){
				return;
			}
		} else {
			if(isset($this->after_delete)){
				$this->{$this->after_delete}();
				if(Router::get_routed()){
					return;
				}
			}
		}

		// Muestra el Formulario en la accion index
		return router::route_to('action: index');
	}

	/**
	 * Emula la acci&oacute;n Query llamando a show
	 */
	public function query(){

		$this->view = 'index';

		Generator::scaffold(&$this->form, $this->scaffold);

		if(!kumbia::is_model($this->source)){
			throw new KumbiaException('No hay un modelo "'.$this->source.'" para hacer la operaci&oacute;n de consulta');
			$this->_create_model();
			return router::route_to('action: index');
		}

		if(isset($this->form['dataFilter'])) {
			if($this->form['dataFilter']){
				$dataFilter = $form['dataFilter'];
			} else {
				$dataFilter = "1=1";
			}
		} else {
			$dataFilter = "1=1";
		}

		if(!isset($this->form['joinTables'])) {
			$this->form['joinTables'] = "";
			$tables = "";
		} else {
			if($this->form['joinTables']) {
				$tables = ",".$this->form['joinTables'];
			} else {
				$tables = "";
			}
		}
		if(!isset($this->form['joinConditions'])) {
			$this->form['joinConditions'] = "";
			$joinConditions = "";
		} else {
			$joinConditions = "";
		}
		if($this->form['joinConditions']) $joinConditions = " and ".$this->form['joinConditions'];

		$modelName = camelize($this->source);

		if(!$this->{$modelName}->is_dumped()){
			$this->{$modelName}->dump_model();
		}

		$query =  "select * from ".$this->form['source']."$tables where $dataFilter $joinConditions ";
		$source = $this->form['source'];

		$form = $this->form;
		$config = Config::read('environment.ini');
		$mode = $this->{$modelName}->get_mode();
		foreach($this->{$modelName}->attributes_names as $fkey){
			if(!isset($_REQUEST["fl_".$fkey])){
				$_REQUEST["fl_".$fkey] = "";
			}
			if(trim($_REQUEST["fl_".$fkey])&&$_REQUEST["fl_".$fkey]!='@'){
				if(!isset($form['components'][$fkey]['valueType'])){
					$form['components'][$fkey]['valueType'] = "";
				}
				if($form['components'][$fkey]['valueType']=='numeric'||$form['components'][$fkey]['valueType']=='date'){
					if($config->$mode->type!='oracle'){
						$query.=" and $source.$fkey = '".$_REQUEST["fl_".$fkey]."'";
					} else {
						if($form['components'][$fkey]['valueType']=='date'){
							$query.=" and $source.$fkey = TO_DATE('".$_REQUEST["fl_".$fkey]."', 'YYYY-MM-DD')";
						} else {
							$query.=" and $source.$fkey = '".$_REQUEST["fl_".$fkey]."'";
						}
					}
				} else {
					if($form['components'][$fkey]['type']=='hidden'){
						$query.=" and $source.$fkey = '".$_REQUEST["fl_".$fkey]."'";
					} else {
						if($form['components'][$fkey]['type']=='check'){
							if($_REQUEST["fl_".$fkey]==$form['components'][$fkey]['checkedValue']){
								$query.=" and $source.$fkey = '".$_REQUEST["fl_".$fkey]."'";
							}
						} else {
							if($form['components'][$fkey]['type']=='time'){
								if($_REQUEST["fl_".$fkey]!='00:00'){
									$query.=" and $source.$fkey = '".$_REQUEST["fl_".$fkey]."'";
								}
							} else {
								if(isset($form['components'][$fkey]['primary'])&&$form['components'][$fkey]['primary']){
									$query.=" and $source.$fkey = '".$_REQUEST["fl_".$fkey]."'";
								} else {
									$query.=" and $source.$fkey like '%".$_REQUEST["fl_".$fkey]."%'";
								}
							}
						}
					}
				}
			}
		}

		$this->query = $query;

		$_REQUEST['queryStatus'] = true;
		$_REQUEST['id'] = 0;

		$this->fetch(0);

	}

	/**
	 * Metodo de ayuda para el componente helpText
	 *
	 */
	public function __autocomplete(){

	}

	/**
	 * Metodo de ayuda para el componente helpText
	 */
	public function __check_value_in(){
		$this->set_response('xml');
		$db = db::raw_connect();
		$_REQUEST['condition'] = str_replace(";", "", urldecode($_REQUEST['condition']));
		ActiveRecord::sql_item_sanizite($_REQUEST['ftable']);
		ActiveRecord::sql_item_sanizite($_REQUEST['dfield']);
		ActiveRecord::sql_item_sanizite($_REQUEST['name']);
		ActiveRecord::sql_item_sanizite($_REQUEST['crelation']);
		$_REQUEST['ftable'] = str_replace(";", "", $_REQUEST['ftable']);
		$_REQUEST['dfield'] = str_replace(";", "", $_REQUEST['dfield']);
		$_REQUEST['name'] = str_replace(";", "", $_REQUEST['name']);
		if($_REQUEST["crelation"]){
			$db->query("select ".$_REQUEST["dfield"]." from ".$_REQUEST['ftable']. " where ".$_REQUEST['crelation']." = '".$_REQUEST['value']."'");
		} else {
			$db->query("select ".$_REQUEST["dfield"]." from ".$_REQUEST['ftable']. " where ".$_REQUEST['name']." = '".$_REQUEST['value']."'");
		}
		print "<?xml version='1.0' encoding='".APP_CHARSET."'?>\r\n<response>\r\n";
		$row = $db->fetch_array();
		print "\t<row num='".$db->num_rows()."' detail='".htmlspecialchars($row[0])."'/>\r\n";
		$db->close();
		print "</response>";
	}

	/**
	 * Emula la accion Fetch llamando a show
	 */
	public function fetch($id){

		$this->view = 'index';

		$db = db::raw_connect();

		if(!$this->query){
			return router::route_to("action: index");
		}

		$rows = $db->fetch_all($this->query);

		if(!isset($id)) {
			$id = 0;
		} else {
			$num = $id;
		}

		//Hubo resultados en el select?
		if(!count($rows)){
			Flash::notice("No se encontraron resultados en la b&uacute;squeda");
			foreach($this->form['components'] as $fkey => $rrow){
				unset($_REQUEST["fl_".$fkey]);
			}
			unset($_REQUEST['queryStatus']);
			return router::route_to('action: index');
		}

		if($id>=count($rows)){
			$num = count($rows)-1;
		}
		if($num<0){
			$num = 0;
		}
		if($id==='last') {
			$num = count($rows)-1;
		}


		/**
		 * Busca si existe un m&eacute;todo o un llamado variable al m&eacute;todo
		 * before_fetch, si este m&eacute;todo devuelve false termina la ejecuci�n
		 * de la acci&oacute;n
		 */
		if(method_exists($this, "before_fetch")){
			if($this->before_fetch()===false){
				return null;
			}
			if(Router::get_routed()){
				return null;
			}
		} else {
			if(isset($this->before_fetch)){
				if($this->{$this->before_fetch}()===false){
					return null;
				}
				if(Router::get_routed()){
					return null;
				}
			}
		}

		Flash::notice("Visualizando ".($num+1)." de ".count($rows)." registros");

		//especifica el registro que quiero mostrar
		$row = $rows[$num];

		//Mete en $row la fila en la que me paro el seek
		foreach($row as $key => $value){
			if(!is_numeric($key)){
				$_REQUEST['fl_'.$key] = $value;
			}
		}

		$_REQUEST['id'] = $num;

		/**
		 * Busca si existe un m&eacute;todo o un llamado variable al m&eacute;todo
		 * after_delete
		 */
		if(method_exists($this, "after_fetch")){
			$this->after_fetch();
			if(Router::get_routed()){
				return;
			}
		} else {
			if(isset($this->after_fetch)){
				$this->{$this->after_fetch}();
			}
			if(Router::get_routed()){
				return;
			}
		}

		return router::route_to('action: index');

	}

	/**
	 * Cambia la vista de browse a la vista index
	 *
	 * @return boolean
	 */
	public function back(){

		$this->view = 'index';
		return router::route_to('action: index');

	}

	/**
	 * Emula la acci&oacute;n Browse llamando a show
	 */
	public function browse(){

		$this->view = 'browse';
		return router::route_to('action: index');
	}

	/**
	* Es el metodo principal de StandarForm y es llamado implicitamente
	* para mostrar el formulario y su accion asociada.
	* La propiedad $this->source indica la tabla con la que se va a generar
	* el formulario
	* La funci�n buildForm es la encargada de crear el formulario
	* esta se encuentra en forms.functions.php
	*/
	public function index(){

		if($this->scaffold){
			if(isset($this->source)) {
				$this->form["source"] = $this->source;
			}
			foreach($this->ignore_list as $ignore){
				$this->form['components'][$ignore]['ignore'] = true;
			}
			Generator::build_form($this->form, true);
		} else {
			if(count($this->form)){
				if($this->source){
					$this->form["source"] = $this->source;
				}
				foreach($this->ignore_list as $ignore){
					$this->form['components'][$ignore]['ignore'] = true;
				}
				Generator::build_form($this->form);
			} else {
				throw new KumbiaException('
					Debe especificar las propiedades del formulario a crear en $this->form
					&oacute: coloque var $scaffold = true para generar din&aacute;micamente el formulario.');
				$this->reset_form();
			}
		}
	}

	/**
	 * Elimina los meta-datos del formulario
	 *
	 */
	public function reset_form(){
		print $appController = $_REQUEST['controller']."Controller";
		unset($_SESSION['KUMBIA_CONTROLLERS'][KUMBIA_PATH][$appController]);
		print_r($_SESSION['KUMBIA_CONTROLLERS'][KUMBIA_PATH]);
	}

	/**
	 * Guarda un nuevo valor para una relacion detalle del
	 * controlador actual
	 *
	 */
	public function _save_helper(){
		$this->set_response('view');
		$db = db::raw_connect();
		Generator::scaffold(&$this->form, $this->scaffold);
		$field = $this->form['components'][$this->request('name')];
		ActiveRecord::sql_item_sanizite($field['foreignTable']);
		ActiveRecord::sql_item_sanizite($field['detailField']);
		$db->query("insert into {$field['foreignTable']} ({$field['detailField']})
		values ('{$this->request('valor')}')");
	}

	/**
	 * Devuelve los valores actualizados de
	 *
	 */
	public function _get_detail(){

		$this->set_response('xml');
		$db = db::raw_connect();
		Generator::scaffold(&$this->form, $this->scaffold);

		$name = $this->request('name');
		$com = $this->form['components'][$this->request('name')];

		if(isset($com['extraTables'])){
			ActiveRecord::sql_item_sanizite($com['extraTables']);
			$com['extraTables']=",".$com['extraTables'];
		} else {
		    $com['extraTables'] = '';
		}

		ActiveRecord::sql_sanizite($com['orderBy']);

		if(!$com["orderBy"]){
			$ordb = $name;
		} else {
			$ordb = $com["orderBy"];
		}
		if(isset($com['whereCondition'])){
			$where = "where ".$com['whereCondition'];
		} else {
			$where = "";
		}

		ActiveRecord::sql_item_sanizite($name);
		ActiveRecord::sql_item_sanizite($com['detailField']);
		ActiveRecord::sql_item_sanizite($com['foreignTable']);

		if($com['column_relation']){
			$com['column_relation'] = str_replace(";", "", $com['column_relation']);
			$query = "select ".$com['foreignTable'].".".$com['column_relation']." as $name,
					".$com['detailField']." from
					".$com['foreignTable'].$com['extraTables']." $where order by $ordb";
			$db->query($query);
		} else {
			$query = "select ".$com['foreignTable'].".$name,
					  ".$com['detailField']." from ".$com['foreignTable'].$com['extraTables']." $where order by $ordb";
			$db->query($query);
		}
		$xml = new simpleXMLResponse();
		while($row = $db->fetch_array()){
			if($this->request('valor')==$row[1]){
				$xml->add_node(array("value" => $row[0], "text" => $row[1], "selected" => "1"));
			} else {
				$xml->add_node(array("value" => $row[0], "text" => $row[1], "selected" => "0"));
			}
		}
		$xml->out_response();
	}

	/**
	 * Indica que un campo tendr� un helper de ayuda
	 *
	 * @param string $field
	 * @param string $helper
	 */
	protected function use_helper($field,$helper=''){
		if(!$helper){
			$helper = $field;
		}
		$this->form['components'][$field."_id"]['use_helper'] = $helper;
	}

	/**
	 * Establece el Titulo del Formulario
	 *
	 * @param string $caption
	 */
	protected function set_form_caption($caption){
		$this->form['caption'] = $caption;
	}

	/**
	 * Indica que un campo ser� de tipo imagen
	 *
	 * @param string $what
	 */
	protected function set_type_image($what){
		$this->form['components'][$what]['type'] = 'image';
	}

	/**
	 * Indica que un campo ser� de tipo numerico
	 *
	 * @param string $what
	 */
	protected function set_type_numeric($what){
		$this->form['components'][$what]['type'] = 'text';
		$this->form['components'][$what]['valueType'] = 'numeric';
	}

	/**
	 * Indica que un campo ser� de tipo Time
	 *
	 * @param string $what
	 */
	protected function set_type_time($what){
		$this->form['components'][$what]['type'] = 'time';
	}

	/**
	 * Indica que un campo ser� de tipo fecha
	 *
	 * @param string $what
	 */
	protected function set_type_date($what){
		$this->form['components'][$what]['type'] = 'text';
		$this->form['components'][$what]['valueType'] = 'date';
	}

	/**
	 * Indica que un campo ser� de tipo password
	 *
	 * @param string $what
	 */
	protected function set_type_password($what){
		$this->form['components'][$what]['type'] = 'password';
	}

	/**
	 * Indica que un campo ser� de tipo textarea
	 *
	 * @param string $what
	 */
	protected function set_type_textarea($what){
		$this->form['components'][$what]['type'] = 'textarea';
	}

	/**
	 * Indica una lista de campos recibir�n entrada solo en may�sculas
	 *
	 */
	protected function set_text_upper(){
		if(func_num_args()){
			foreach(func_get_args() as $what){
				$this->form['components'][$what]['type'] = 'text';
				$this->form['components'][$what]['valueType'] = 'textUpper';
			}
		}
	}

	/**
	 * Crea un combo est�tico
	 *
	 * @param string $what
	 * @param string $arr
	 */
	protected function set_combo_static($what, $arr){
		$this->form['components'][$what]['type'] = 'combo';
		$this->form['components'][$what]['class'] = 'static';
		$this->form['components'][$what]['items'] = $arr;
	}

	/**
	 * Crea un combo Dinamico
	 *
	 * @param string $what
	 */
	protected function set_combo_dynamic($what){
		$opt = get_params(func_get_args());
		$opt['field'] = (isset($opt['field'])) ? $opt['field'] : $opt[0];
		$opt['relation'] = (isset($opt['relation'])) ? $opt['relation'] : $opt[1];
		$opt['detail_field'] = (isset($opt['detail_field'])) ? $opt['detail_field'] : $opt[2];
		$this->form['components'][$opt['field']]['type'] = 'combo';
		$this->form['components'][$opt['field']]['class'] = 'dynamic';
		$this->form['components'][$opt['field']]['foreignTable'] = $opt['relation'];
		$this->form['components'][$opt['field']]['detailField'] = $opt['detail_field'];
		if(isset($opt['conditions'])){
			$this->form['components'][$opt['field']]['whereCondition'] = $opt['conditions'];
		}
		if(isset($opt['column_relation'])){
			$this->form['components'][$opt['field']]['column_relation'] = $opt['column_relation'];
		}
	}

	/**
	 * Crea un Texto de Ayuda de Contexto
	 *
	 * @param string $what
	 */
	protected function set_help_context($what){
		$opt = get_params(func_get_args());
		$opt['field'] = $opt['field'] ? $opt['field'] : $opt[0];
		$opt['relation'] = $opt['relation'] ? $opt['relation'] : $opt[1];
		$opt['detail_field'] = $opt['detail_field'] ? $opt['detail_field'] : $opt[2];
		$this->form['components'][$opt['field']]['type'] = 'helpText';
		$this->form['components'][$opt['field']]['class'] = 'dynamic';
		$this->form['components'][$opt['field']]['foreignTable'] = $opt['relation'];
		$this->form['components'][$opt['field']]['detailField'] = $opt['detail_field'];
		if($opt['conditions']){
			$this->form['components'][$opt['field']]['whereCondition'] = $opt['conditions'];
		}
		if($opt['column_relation']){
			$this->form['components'][$opt['field']]['column_relation'] = $opt['column_relation'];
		}
		if($opt['column_relation']){
			$this->form['components'][$opt['field']]['column_relation'] = $opt['column_relation'];
		} else {
			$this->form['components'][$opt['field']]['column_relation'] = $opt['id'];
		}
		if($opt['message_error']){
			$this->form['components'][$opt['field']]['messageError'] = $opt['message_error'];
		} else {
			$this->form['components'][$opt['field']]['messageError'] = "NO EXISTE EL REGISTRO DIGITADO";
		}
	}

	/**
	 * Especifica que un campo es de tipo E-Mail
	 * @param $fields
	 */
	protected function set_type_email($fields){
		if(func_num_args()){
			foreach(func_get_args() as $field){
				$this->form['components'][$field]['type'] = 'text';
				$this->form['components'][$field]['valueType'] = "email";
			}
		}
	}

	/**
	 * Recibe una lista de campos que no van a ser incluidos en
	 * la generaci�n del formulario
	 */
	protected function ignore(){
		if(func_num_args()){
			foreach(func_get_args() as $what){
				$this->form['components'][$what]['ignore'] = true;
				if(!in_array($what, $this->ignore_list)){
					$this->ignore_list[] = $what;
				}
			}
		}
	}
  	/**
   	* Indica el tamaño maximo de los caracteres de un campo en la vista visualizar
   	*
   	*  @param array
   	*/
    protected function set_size_browse(){
        $opt = get_params(func_get_args());
        if(count($opt) > 0){
            foreach ($opt as $w => $k) {
            	$this->form['components'][$w]['attributes']['sizeBrowse'] = $k;
            }
        } else {
            throw new KumbiaException("No indico ning&uacute;n campo para aplicar este m&eacute;todo");
        }
    }
	/**
	 * Permite cambiar el tama�o (size) de un campo $what a $size
	 *
	 * @param string $what
	 * @param integer $size
	 */
	protected function set_size($what, $size){
		$this->form['components'][$what]['attributes']['size'] = $size;
	}

	/**
	 * Permite cambiar el tama�o m�ximo de caracteres que se puedan
	 * digitar en un campo texto
	 *
	 * @param unknown_type $what
	 * @param unknown_type $size
	 */
	protected function set_maxlength($what, $size){
		$this->form['components'][$what]['attributes']['maxlength'] = $size;
	}

	/**
	 * Hace que un campo aparezca en la pantalla de visualizaci&oacute;n
	 *
	 */
	protected function not_browse(){
		if(func_num_args()){
			foreach(func_get_args() as $what){
				$this->form['components'][$what]['notBrowse'] = true;
			}
		}
	}

	/**
	 * Hace que un campo no aparezca en el reporte PDF
	 *
	 * @param string $what
	 */
	protected function not_report($what){
		if(func_num_args()){
			foreach(func_get_args() as $what){
				$this->form['components'][$what]['notReport'] = true;
			}
		}
	}

	/**
	 * Cambia la imagen del Formulario. $im es una imagen en img/
	 *
	 * @param string $im
	 */
	protected function set_title_image($im){
		$this->form['titleImage'] = $im;
	}

	/**
	 * Cambia el numero de campos que aparezcan por fila
	 * cuando se genere el formulario
	 *
	 * @param unknown_type $number
	 */
	protected function fields_per_row($number){
		$this->form['fieldsPerRow'] = $number;
	}

	/**
	 * Inhabilita el formulario para insertar
	 *
	 */
	protected function unable_insert(){
		$this->form['unableInsert'] = true;
	}

	/**
	 * Inhabilita el formulario para borrar
	 *
	 */
	protected function unable_delete(){
		$this->form['unableDelete'] = true;
	}

	/**
	 * Inhabilita el formulario para actualizar
	 *
	 */
	protected function unable_update(){
		$this->form['unableUpdate'] = true;
	}

	/**
	 * Inhabilita el formulario para consultar
	 *
	 */
	protected function unable_query(){
		$this->form['unableQuery'] = true;
	}

	/**
	 * Inhabilita el formulario para visualizar
	 *
	 */
	protected function unable_browse(){
		$this->form['unableBrowse'] = true;
	}

	/**
	 * Inhabilita el formulario para generar reporte
	 *
	 */
	protected function unable_report(){
		$this->form['unableReport'] = true;
	}

	/**
	 * Indica que un campo ser� de tipo Hidden
	 *
	 */
	protected function set_hidden($what){
		if(func_num_args()){
			foreach(func_get_args() as $field){
				$this->form['components'][$field]['type'] = 'hidden';
			}
		}
	}

	/**
	 * Cambia el Texto Caption de un campo en especial
	 *
	 */
	protected function set_caption($what, $caption){
		$this->form['components'][$what]['caption'] = $caption;
	}

	/**
	 * Hace que un campo sea de solo lectura
	 *
	 * @param string $what
	 */
	protected function set_query_only($fields){
		if(func_num_args()){
			foreach(func_get_args() as $field){
				$this->form['components'][$field]['queryOnly'] = true;
			}
		}
	}

	/**
	 * Cambia el texto de los botones para los formularios
	 * estandar
	 * set_action_caption('insert', 'Agregar')
	 */
	protected function set_action_caption($action, $caption){
		$this->form['buttons'][$action] = $caption;
	}

	/**
	 * Asigna un atributo a un campo del formulario
	 * set_attribute('campo', 'rows', 'valor')
	 * @param $field
	 * @param $name
	 * @param $value
	 */
	protected function set_attribute($field, $name, $value){
		$this->form['components'][$field]['attributes'][$name] = $value;
	}


	/**
	 * Asigna un atributo a un campo del formulario
	 * set_attribute('campo', 'rows', 'valor')
	 * @param $field
	 * @param $event
	 * @param $value
	 */
	protected function set_event($field, $event, $value){
		$this->form['components'][$field]['attributes']["on".$event] = $value;
	}

	/**
	 * Ejecuta el inicializador para tomar los cambios sin reiniciar el navegador
	 *
	 */
	public function __wakeup(){
		if(method_exists($this, "initialize")){
			$this->initialize();
		}
		parent::__wakeup();
	}

}
