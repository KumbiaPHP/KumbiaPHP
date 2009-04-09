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
 * @package Generator
 * @copyright Copyright (c) 2005-2007 Andres Felipe Gutierrez (andresfelipe at vagoogle.net)
 * @copyright Copyright (c) 2008-2008 Emilio Rafael Silveira Tovar (emilio.rst at gmail.com)
 * @license http://www.kumbia.org/license.txt GNU/GPL
 */

/**
 * Obtiene los metadatos y construye el esquema para crear un formulario StandarForm
 *
 * @category Kumbia
 * @package Generator
 * @abstract
 * @copyright Copyright (c) 2005-2007 Andres Felipe Gutierrez (andresfelipe at vagoogle.net)
 * @license http://www.kumbia.org/license.txt GNU/GPL
 */
abstract class Generator {

	/**
	 * Salida string al formulario
	 *
	 * @var string
	 */
	static public $outForm = "";

	/**
	 * Obtiene el indice de un campo en la lista de campos
	 *
	 * @param string $field
	 * @param array $form
	 * @return integer
	 */
	static function get_index($field, $form){
		$n = 0;
		foreach($form['components'] as $name => $comp){
			if($name==$field) {
				return $n;
			}
			$n++;
		}
		return 0;
	}

	/**
	 * Obtiene el tipo de explorador usado por el cliente
	 * de la aplicaci�n
	 *
	 * @return string
	 */
	static function get_browser(){
		if(strpos($_SERVER['HTTP_USER_AGENT'], "Firefox")){
			return "firefox";
		} else {
			return "msie";
		}
	}

	/**
	 * Genera una salida que es cacheada para luego hacer que
	 * salga toda junta
	 *
	 * @param mixed $val
	 */
	static function forms_print($val){
		self::$outForm.=$val;
	}

	/**
	 * Imprime la salida que estaba cacheada utilizando self::forms_print
	 *
	 */
	static function build_form_out(){
		print self::$outForm;
		self::$outForm = "";
	}

	/**
	 * Vuelca la informaci�n de la tabla para construir el array
	 * interno que luego sirve para construir el formulario
	 *
	 * @param array $form
	 * @return boolean
	 */
	static function dump_field_information($form){
		$controller_name = Router::get_controller();
		$form['force'] = eval("return ".ucfirst(camelize($controller_name))."Controller::\$force;");
		if(!isset($_SESSION['dumps'][KUMBIA_PATH])){
			$_SESSION['dumps'][KUMBIA_PATH] = array();
		}
		if(!isset($_SESSION['dumps'][KUMBIA_PATH][$form['source'].$form['type']])){
			$_SESSION['dumps'][KUMBIA_PATH][$form['source'].$form['type']] = null;
		}
		if($_SESSION['dumps'][KUMBIA_PATH][$form['source'].$form['type']]&&!$form['force']){

			$form = unserialize($_SESSION['dumps'][KUMBIA_PATH][$form['source'].$form['type']]);
			return true;
		}
		
		$modelName = camelize($form['source']);
		$config = Config::read('environment.ini');
		$mode = kumbia::$models[$modelName]->get_mode();
		$config = $config->$mode;
		
		$db = db::raw_connect("mode: $mode");
		$fields = $db->describe_table($form['source']);
		if(!$fields) {
			Flash::kumbia_error("No existe la tabla {$form['source']} en la base de datos {$config->name}");
			return false;
		}
		$cp = $form;
		$form = array();
		$n = 0;
		if(!isset($form['components'])) {
			$form['components'] = array();
		}
		foreach($fields as $field){
			array_insert($form['components'], $n, array(), $field['Field']);
			if($field['Type']=='date'){
				if(!isset($form['components'][$field['Field']]['valueType'])){
					$form['components'][$field['Field']]['valueType'] = "date";
				}
			}
			if($field['Field']=='id'){
				$form['components'][$field['Field']]['auto_numeric'] = true;
				if($cp['type']=='grid'){
					$form['components'][$field['Field']]['type'] = "auto";
				}
			}
			if($field['Field']=='email'){
				if(!isset($form['components'][$field['Field']]['valueType'])){
					$form['components'][$field['Field']]['valueType'] = "email";
				}
			}
			if($field['Key']=='PRI'){
				if(!isset($form['components'][$field['Field']]['primary'])){
					$form['components'][$field['Field']]['primary'] = true;
				}
			}
			if($field['Null']=='NO'){
				if(!isset($form['components'][$field['Field']]['notNull'])){
					$form['components'][$field['Field']]['notNull'] = true;
				}
			}
			if(strpos(" ".$field['Type'], "int")||strpos(" ".$field['Type'], "decimal")){
				if(!isset($form['components'][$field['Field']]['valueType'])){
					$form['components'][$field['Field']]['valueType'] = "numeric";
				}
			}
			if(strpos(" ".$field['Type'], "varchar") || $field['Type']=='text'){
				if(!isset($form['components'][$field['Field']]['valueType'])){
					$form['components'][$field['Field']]['valueType'] = "text";
				}
			}
			if($field['Type']=='text'){
				$form['components'][$field['Field']]['type'] = 'textarea';
			}
			if($field['Field']=='email'){
				if(!isset($form['components'][$field['Field']]['valueType'])){
					$form['components'][$field['Field']]['valueType'] = "email";
				}
			}
			if(ereg("[a-z_0-9A-Z]+_id$", $field['Field'])){
				$table = substr($field['Field'], 0, strpos($field['Field'], "_id"));
				ActiveRecord::sql_item_sanizite($table);
				$dq = $db->describe_table($table);
				if($dq){
					$y = 0;
					$p = 0;
					foreach($dq as $rowq){
						if($rowq['Field']=='id'){
							$p = 1;
						}
						if(
						($rowq['Field']=='detalle')||
						($rowq['Field']=='nombre')||
						($rowq['Field']=='descripcion')||
						($rowq['Field']=='name')
						){
							$detail = $rowq['Field'];
						}
					}
					if($p&&isset($detail)&&!isset($form['components'][$field['Field']]['type'])){
						$form['components'][$field['Field']]['type'] = 'combo';
						$form['components'][$field['Field']]['class'] = 'dynamic';
						$form['components'][$field['Field']]['foreignTable'] = $table;
						if(!isset($form['components'][$field['Field']]['detailField'])){
							$form['components'][$field['Field']]['detailField'] = $detail;
						}
						$form['components'][$field['Field']]['orderBy'] = "2";
						$form['components'][$field['Field']]['column_relation'] = "id";
						$form['components'][$field['Field']]['caption'] =
						ucwords(str_replace("_", " ", str_replace("_id", "", $field['Field'])));
					}
				}
			} else {
				if($x = strpos(" ".$field['Type'], "(")){
					$l = substr($field['Type'], $x);
					$l = substr($l, 0, strpos($l, ")"));
					if(!isset($form['components'][$field['Field']]['attributes']['size'])){
						$form['components'][$field['Field']]['attributes']['size'] = (int) $l;
					}
					if(!isset($form['components'][$field['Field']]['attributes']['maxlength'])){
						$form['components'][$field['Field']]['attributes']['maxlength'] = (int) $l;
					}
				}
			}
			if(!isset($form['components'][$field['Field']]['type'])){
				$form['components'][$field['Field']]['type'] = "text";
			}
			$n++;
		}

		if(!count($cp['components'])) {
			unset($cp['components']);
		}

		$form = array_merge_overwrite($form, $cp);
		foreach($form['components'] as $key => $value){
			if(isset($value['ignore'])) {
				if($value['ignore']){
					unset($form['components'][$key]);
				}
			}
		}
		$_SESSION['dumps'][$form['source'].$form['type']] = serialize($form);
		return true;
	}

	/**
	 * Genera informaci�n importante para la construcci�n del formulario
	 *
	 * @param mixed $form
	 * @param boolean $scaffold
	 * @return boolean
	 */
	static function scaffold($form, $scaffold = false){

		if(!is_array($form)){
			$form = array();
		}


		$controller = Dispatcher::get_controller();
		$controller_name = Router::get_controller();

		if(isset($form['source'])) {
			if(!$form['source']) {
				$controller->source = $controller_name;
				$form['source'] = $controller_name;
			}
		} else {
			if($controller->source){
				$form['source'] = $controller->source;
			} else {
				$controller->source = $controller_name;
				$form['source'] = $controller_name;
			}
		}
		ActiveRecord::sql_item_sanizite($form['source']);
		if(isset($form['caption'])) {
			if(!$form['caption']) {
				$form['caption'] = ucwords(str_replace("_", " ", $controller_name));
			}
		} else {
			$form['caption'] = ucwords(str_replace("_", " ", $controller_name));
		}

		if(isset($form['type'])) {
			if(!$form['type']) {
				$form['type'] = 'standard';
			}
		}
		else $form['type'] = 'standard';

		//Dump Data Field Information if no components are loaded
		if(!isset($form['components']))	{
			$form['components'] = null;
		}
		if(!isset($form['scaffold'])) {
			$form['scaffold'] = false;
		}
		if((!$form['components'])||$form['scaffold']||$scaffold){
			if(!self::dump_field_information(&$form)){
				return false;
			}
			if($form['type']=='master-detail'){
				self::dump_field_information(&$form['detail']);
				$form['detail']['dataFilter'] = "{$form['detail']['source']}.{$form['source']}_id = '@id'";
				foreach($form["detail"]['components'] as $k => $f){
					if($k=='id'){
						$form["detail"]['components'][$k]['type'] = "auto";
						$form["detail"]['components'][$k]['caption'] = "";
						$f['caption'] = "";
						$f['type'] = "auto";
					}
					if($k==$form['source']."_id"){
						$form["detail"]['components'][$k]['type'] = "hidden";
						$form["detail"]['components'][$k]['caption'] = "";
						$form["detail"]['components'][$k]['attributes']['value'] = $_POST["fl_id"];
						$f['caption'] = "";
						$f['type'] = "hidden";
					}
					if(!isset($f["caption"])) {
						if($f['type']!='auto'&&$f['type']!='hidden'){
							$form["detail"]['components'][$k]['caption'] = ucwords(str_replace("_", " ", $k));
						}
					}
				}
			}
		}

		if(!$form['components']){
			Flash::kumbia_error("No se pudo cargar la informaci�n de la relaci�n '{$form['source']}'</span><br>Verifique que la entidad exista
		en la base de datos actual � que los par�metros se�n correctos");
			return;
		}

		//Creating Captions
		foreach($form['components'] as $k => $f){
			if(!isset($f["caption"])) {
				if($f['type']!='auto'&&$f['type']!='hidden'){
					$form['components'][$k]['caption'] = ucwords(str_replace("_", " ", $k));
				}
			}
		}

	}

	/**
	 * BuildForm is the main function that builds all the forms
	 *
	 * @param array $form
	 * @param boolean $scaffold
	 * @return boolean
	 */
	static function build_form($form, $scaffold=false){

		require_once CORE_PATH.'library/kumbia/generator/components.php';

		$controller_name = Router::get_controller();
		$action_name = Router::get_action();

		//self::$outForm = "";

		Generator::scaffold(&$form, $scaffold);

		if(!$form['components']) return false;

		//Loading The JavaScript Functions
		self::forms_print("<script type='text/javascript' src='".KUMBIA_PATH."javascript/kumbia/load.js'></script>\r\n");

		if($form['type']=='standard'){
			self::forms_print("<script type='text/javascript' src='".KUMBIA_PATH."javascript/kumbia/load.standard.js'></script>\r\n");
		}
		self::forms_print("<script  type='text/javascript' src='".KUMBIA_PATH."javascript/kumbia/utilities.calendarDateInput.js'></script>\r\n");
		self::forms_print("<script  type='text/javascript' src='".KUMBIA_PATH."javascript/kumbia/email.js'></script>\r\n");

		if(file_exists("public/javascript/$controller_name.js")){
			self::forms_print("<script type='text/javascript' src='".KUMBIA_PATH."javascript/{$_REQUEST["controller"]}.js'></script>\r\n");
		}

		if(file_exists("public/css/$controller_name.css")){
			self::forms_print("<link rel='stylesheet' href='".KUMBIA_PATH."css/$controller_name.css' type='text/css'/>\n");
		}

		self::forms_print("<div class='$controller_name'>
		<form method='post' name='fl' action='' onsubmit='return false'>");
		if(!isset($form["notShowTitle"])){
			if(isset($form['titleImage'])){
				if(isset($form['titleHelp'])){
					self::forms_print("<table><td><img src='".KUMBIA_PATH."img/{$form['titleImage']}' border=0></td>
				<td><h1 class='".$form['titleStyle']."' title='{$form['titleHelp']}'
				style='cursor:help'>&nbsp;<u>".$form["caption"]."</u></h1>
				</td></table>\r\n");
				} else {
					self::forms_print("<table><td><img src='".KUMBIA_PATH."img/{$form['titleImage']}' border=0></td>
				<td><h1 class='".isset($form['titleStyle'])."'>&nbsp;".$form["caption"]."</h1>
				</td></table>\r\n");
				}
			} else {
				if(!isset($form['titleStyle'])) {
					self::forms_print("<h1>&nbsp;".$form["caption"]."</h1>\r\n");
				} else {
					self::forms_print("<h1 class='".$form['titleStyle']."'>&nbsp;".$form["caption"]."</h1>\r\n");
				}
			}
		}
		self::forms_print("<input type='hidden' name='aaction' value='".$controller_name."' />\r\n");
		self::forms_print("<input type='hidden' id='kb_path' name='kb_path' value='".KUMBIA_PATH."' />\r\n");
		if(isset($_REQUEST['value'])){
			self::forms_print("<input type='hidden' name='vvalue' name='vvalue' value='".$_REQUEST['value']."' />\r\n");
		}
		self::forms_print("<input type='hidden' id='errStatus' name='errStatus' value='0' />\r\n");
		self::forms_print("<input type='hidden' id='winHelper' name='winHelper' value='0' />\r\n");
		if($action_name=='validation'){
			self::forms_print("<input type='hidden' id='validation' name='validation' value='1' />\r\n");
		} else {
			self::forms_print("<input type='hidden' id='validation' name='validation' value='0' />\r\n");
		}

		//Standard Forms
		if($form['type']=='standard'){
			include_once CORE_PATH.'library/kumbia/generator/standard.build.php';
			Standard_Generator::build_form_standard($form);
		}

		self::forms_print("</div>");

		self::build_form_out();
	}

	/**
	 * Obtener el siguiente autonumerico
	 *
	 * @param db $db
	 * @param string $table
	 * @param string $field
	 * @return string
	 */
	static function get_max_auto($db, $table, $field){
		ActiveRecord::sql_item_sanizite($table);
		ActiveRecord::sql_item_sanizite($field);
		$db->query("select max($field)+1 from $table");
		$row = $db->fetch_array();
		if(!$row[0]) $row[0] = 1;
		return $row[0];
	}

}
