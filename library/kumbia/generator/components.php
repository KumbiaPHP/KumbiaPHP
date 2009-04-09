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
 * @license http://www.kumbia.org/license.txt GNU/GPL
 */

/**
 * Crea los componentes utilizados en los formularios Standard y Maestro-Detalle
 *
 * @category Kumbia
 * @package Generator
 * @abstract
 * @copyright Copyright (c) 2005-2007 Andres Felipe Gutierrez (andresfelipe at vagoogle.net)
 * @license http://www.kumbia.org/license.txt GNU/GPL
 */
abstract class Component {

	/**
	 * Crea un componente tipo TextArea, estos componentes se crean automaticamente
 	 * cuando un campo es de tipo TEXT o mediante el metodo $this->set_type_textarea
 	 *
 	 * @param string $com
  	 * @param string $name
 	 */
	static function build_text_area($com, $name){
		Generator::forms_print("<label for='flid_$name'><strong>".$com['caption']." :</strong></label></td><td>");
		Generator::forms_print("<textarea name='fl_$name' id='flid_$name' disabled='disabled' ");
		if(!isset($com['attributes']['rows'])) $com['attributes']['rows'] = 15;
		if(!isset($com['attributes']['cols'])) $com['attributes']['cols'] = 40;
		if($com['attributes']){
			foreach($com["attributes"] as $nitem => $item){
				Generator::forms_print(" $nitem='$item' ");
			}
		}
		Generator::forms_print(">");
		if(isset($_REQUEST['fl_'.$name])){
			Generator::forms_print(stripslashes($_REQUEST['fl_'.$name]));
		}
		if(!isset($com['extraText'])) {
	         $com['extraText'] = "";
		}
		Generator::forms_print("</textarea>&nbsp;<span id='det_$name'>".$com['extraText']."</span>\r\n");

	}


	/**
 	 * Crea un componente tipo Texto, estos componentes son los componentes
 	 * por defecto de los formularios
 	 *
 	 * @param string $com
 	 * @param string $name
 	 */
	static function build_text_component($com, $name, $form){
		$config = Config::read('config.ini');
		$active_app = Router::get_application();
		$dbdate = $config->$active_app->dbdate;
		if(isset($com['not_label'])){
			if(!$com['not_label']){
				Generator::forms_print("<label for='flid_$name'><strong>".$com['caption']." :</strong></label></td><td>");
			}
		} else {
			Generator::forms_print("<label for='flid_$name'><strong>".$com['caption']." :</strong></label></td><td>");
		}
		if(!isset($com['valueType'])) $com['valueType'] = "";
		if($com['valueType']!='date'&&$com['valueType']!='email'){
			Generator::forms_print("<input type='text' name='fl_$name' id='flid_$name' disabled='disabled' ");
			if(isset($_REQUEST['fl_'.$name])){
				Generator::forms_print("value = '".$_REQUEST['fl_'.$name]."'");
			}
			if(isset($com['attributes'])&&$com['attributes']){
				foreach($com["attributes"] as $nitem => $item){
					Generator::forms_print(" $nitem='$item' ");
				}
			}

			//Validaciones JavaScript Dominio de Valores >7 � <15
			$validation = "";

			//Numerico
			if(isset($com['valueType'])){
				if($com['valueType']=="numeric"){
					Generator::forms_print("onkeydown='valNumeric(event)'");
				} else Generator::forms_print("onkeydown='nextField(event, \"$name\")'");

				//Texto en Mayusculas
				if($com['valueType']=="textUpper"){
					Generator::forms_print("onblur='keyUpper2(this)'");
				}

				if($com['valueType']=="onlyText"){
					Generator::forms_print(" onkeydown='validaText(event)' ");
				}


				if($com['valueType']=="onlyTextUpper"){
					Generator::forms_print(" onkeydown='validaText(event)' ");
					Generator::forms_print(" onblur='keyUpper2(this)'");
				}

				///Validar la Fecha
				if($com['valueType']=="date"){
					Generator::forms_print(" onkeydown='valDate()'");
					Generator::forms_print(" onblur='checkDate(this)'");
				}
			} else {
				$com['valueType'] = null;
			}

			//Validacion de Formatos
			if(isset($com['format'])){
				Generator::forms_print(" onkeypress=\"formatNumber(this, '{$com['format']}')\"");
			}
			if(!isset($com['extraText'])) {
				$com['extraText'] = "";
			}
			Generator::forms_print(" />&nbsp;<span id='det_$name'>".$com['extraText']."</span>\r\n");
		} else {
			if($com['valueType']=="date"){
				$valueDate = "";
				if(isset($_REQUEST['fl_'.$name])){
					$valueDate = "'".$_REQUEST['fl_'.$name]."'";
				} else {
					if(isset($com['value'])){
						$valueDate = "'".$com['value']."'";
					} else {
						$valueDate = "''";
					}
				}
				if($valueDate=="''"){
					$valueDate = "null";
				}
				if($valueDate=="null"){
					Generator::forms_print("\n<script type='text/javascript'> var vdateId = 'flid_$name';
					DateInput('fl_$name', false, '".$dbdate."')</script>");
				} else {
					Generator::forms_print("\n<script type='text/javascript'> var vdateId = 'flid_$name';
					DateInput('fl_$name', false, '".$dbdate."', $valueDate)</script>");
				}
				//Generator::forms_print("<script>DateInput('fl_$name', false, '".$GLOBALS['dbDate']."', $valueDate, 'flid_$name')</script>");
			} else {
				if(isset($_REQUEST["fl_$name"])&&$_REQUEST["fl_$name"]) {
					$p1 = substr($_REQUEST["fl_$name"], 0, strpos($_REQUEST["fl_$name"], "@"));
					$p2 = substr($_REQUEST["fl_$name"], strpos($_REQUEST["fl_$name"], "@")+1);
				} else {
					$_REQUEST["fl_$name"] = "";
					$p1 = "";
					$p2 = "";
				}
				Generator::forms_print("<input type='hidden' value='{$_REQUEST["fl_$name"]}' name='fl_$name' id='flid_$name' />");
				Generator::forms_print("<span><input type='text' size='15' disabled='disabled' id='$name"."_email1'
				onblur='saveEmail(\"$name\")' onkeydown='validaEmail(event)' value='$p1'/>
				<strong>@</strong><input type='text' size=15 disabled='disabled' id='$name"."_email2'
				onblur='saveEmail(\"$name\")' onkeydown='validaEmail(event)' value='$p2'/></span>");
			}
		}
	}

	function build_userdefined_component($com, $name, $form){

	}

	function build_email_component(){

	}

	/**
	 * Construye un HelpContext Componente
	 *
	 * @param array $com
	 * @param string $name
	 * @param array $form
	 */
	static function build_help_context($com, $name, $form){
		print $name;
		Generator::forms_print("<label for='flid_$name'><strong>".$com['caption']." : </strong></label></td><td valign='top'>");
		Generator::forms_print("<input type='text' name='fl_$name' id='flid_$name' disabled='disabled' ");
		if(isset($_REQUEST['fl_'.$name])){
			Generator::forms_print("value = '".$_REQUEST['fl_'.$name]."'");
		}
		if($_REQUEST['fl_'.$name]!==""&&$_REQUEST['fl_'.$name]!==null){
			$db = db::raw_connect();
			ActiveRecord::sql_item_sanizite($name);
			ActiveRecord::sql_item_sanizite($com["foreignTable"]);
			ActiveRecord::sql_sanizite($com["detailField"]);
			if(!$com["column_relation"]){
				ActiveRecord::sql_item_sanizite($com["column_relation"]);
				$db->query("select {$com['detailField']} from {$com['foreignTable']}
		  		where $name = '{$_REQUEST['fl_'.$name]}'");
			} else {
				$db->query("select {$com['detailField']} from {$com['foreignTable']}
		  		where {$com["column_relation"]} = '{$_REQUEST['fl_'.$name]}'");
			}
			$val = $db->fetch_array();
			$val = $val[0];
		}
		if(!$val){
			if($_REQUEST['fl_'.$name."_det"]){
				$val = $_REQUEST['fl_'.$name."_det"];
			}
		}

		if(count($com["attributes"])){
			foreach($com["attributes"] as $nitem => $item){
				Generator::forms_print(" $nitem='$item' ");
			}
		}

		//Validaci�n contra el Motor de Base de Datos
		$validation.="checkValueIn(\"$name\", \"".$com['foreignTable']."\", \"".$com['messageError']."\", \"".$com['detailField']."\", \"{$com['useHelper']}\", \"{$com['column_relation']}\"); ";

		//Validaciones otras
		//Numerico
		if($com['valueType']=="numeric"){
			Generator::forms_print("onkeydown='valNumeric(event)'");
		} else Generator::forms_print("onkeydown='nextField(event, \"$name\")'");

		//Texto en Mayusculas
		if($com['valueType']=="textUpper"){
			$validation.=";keyUpper2()";
		}
		Generator::forms_print(" onblur='$validation'");
		Generator::forms_print(" />&nbsp;<input type='text' id='flid_$name"."_det'
		style='border:1px solid #808080;background:#E1E1E1;font-size:11px'
		value='$val' size='55' onblur='keyUpper2(this)'>\r\n");
		$md5 = md5(rand(0, 100));
		Generator::forms_print("<span id='indicator$md5' style='display: none'>
        <img src='".KUMBIA_PATH."img/spinner.gif' alt='' /></span>
		<div id='{$name}_choices' class='autocomplete'></div>
		<script>
		// <![CDATA[
		new Ajax.Autocompleter(\"flid_{$name}_det\",
		 \"{$name}_choices\", \"".KUMBIA_PATH."{$_REQUEST["controller"]}/__autocomplete?f={$com['foreignTable']}&d={$com['detailField']}&n=$name&c={$com["column_relation"]}\",
		 { paramName: \"id\", minChars: 2, indicator: 'indicator$md5', afterUpdateElement : function(obj, li){ $(\"flid_$name\").value = li.id } });
		// ]]>
		</script>");
	}

	/**
 	 * Crea los componentes tipo Combo cuando son creados dinamicamente
 	 * y Estaticamente, en formularios Standard y Master-Detail
 	 *
 	 * @param array $com
 	 * @param string $name
 	 */
	static function build_standard_combo($com, $name){
		Generator::forms_print("<label for='flid_$name'><strong>".$com['caption']." : </strong></label></td><td><select name='fl_$name' id='flid_$name' disabled='disabled' ");
		if(isset($com["attributes"])){
			if(is_array($com["attributes"])){
				foreach($com["attributes"] as $nitem => $item) {
					if($nitem!='maxlength'&&$nitem!='size'){
						Generator::forms_print(" $nitem='$item' ");
					}
				}
			}
		}
		$validation = "";
		if(isset($com['dynamicFilter'])){
			if($com['dynamicFilter']){
				$validation.="; getDetailValues(\"".$com['dynamicFilter']['field']."\", \"".$com['dynamicFilter']['foreignTable']."\", \"".$com['dynamicFilter']['detailField']."\", \"";
				$com['dynamicFilter']['whereCondition'] = urlencode($com['dynamicFilter']['whereCondition']);
				$com['dynamicFilter']['whereCondition'] = str_replace("%40", "@", $com['dynamicFilter']['whereCondition']);
				if(strpos($com['dynamicFilter']['whereCondition'], '@')){
					ereg("[\@][A-Za-z0-9]+", $com['dynamicFilter']['whereCondition'], $regs);
					foreach($regs as $reg){
						$com['dynamicFilter']['whereCondition'] = str_replace($reg, "\"+document.getElementById(\"flid_".str_replace("@", "", $reg)."\").value+\"", $com['dynamicFilter']['whereCondition']);
					}
				}
				$validation.=$com['dynamicFilter']['whereCondition']."\", \"".$com['dynamicFilter']['relfield']."\")";
			}
		}
		Generator::forms_print(" onkeydown='nextField(event, \"$name\")' ");
		if($validation){
			Generator::forms_print(" onchange='$validation'>\r\n");
		} else {
			Generator::forms_print(">\r\n");
		}
		if(!isset($com['noDefault'])||!$com['noDefault']){
			Generator::forms_print("<option value='@'>Seleccione ...</option>\n");
		}
		if($com['class']=='dynamic'){
			$db = db::raw_connect();
			if(isset($com['extraTables'])){
				if($com['extraTables']){
					ActiveRecord::sql_sanizite($com["extraTables"]);
					$com['extraTables']=",".$com['extraTables'];
				}
			}
			if(isset($com["detail_field"])){
				ActiveRecord::sql_sanizite($com["detail_field"]);
			} 
			if(isset($com["orderBy"])){
			    ActiveRecord::sql_sanizite($com["orderBy"]);
			    if(!$com["orderBy"]) $ordb = $name; else $ordb = $com["orderBy"];
			} else {
			    $ordb = $name;
			}
			
			ActiveRecord::sql_item_sanizite($com["foreignTable"]);
			$where = "";
			if(isset($com['whereCondition'])){
				if($com['whereCondition']) {
					$where = "where ".$com['whereCondition'];
				} else {
					$where = "";
				}
			}
			if($com['column_relation']){
				ActiveRecord::sql_sanizite($com["column_relation"]);
				if(isset($com['extraTables'])){
					$query = "select ".$com['foreignTable'].".".$com['column_relation']." as $name,
						".$com['detailField']." from
						".$com['foreignTable'].$com['extraTables']." $where order by $ordb";
				} else {
					$query = "select ".$com['foreignTable'].".".$com['column_relation']." as $name,
						".$com['detailField']." from
						".$com['foreignTable']." $where order by $ordb";
				}
				$db->query($query);
			}else {
				$query = "select ".$com['foreignTable'].".$name,
					  ".$com['detailField']." from ".$com['foreignTable'].$com['extraTables']." $where order by $ordb";
				$db->query($query);
			}
			while($row = $db->fetch_array()){
				if(!isset($_REQUEST["fl_".$name])){
					$_REQUEST["fl_".$name] = "";
				}
				$row[0] = htmlentities((string)$row[0], ENT_QUOTES, APP_CHARSET);
				$row[1] = htmlentities((string)$row[1], ENT_QUOTES, APP_CHARSET);
				if($_REQUEST["fl_".$name]==$row[0]){
					Generator::forms_print("<option value='".$row[0]."' selected='selected'>".$row[1]."</option>\r\n");
				} else {
					Generator::forms_print("<option value='".$row[0]."'>".$row[1]."</option>\r\n");
				}
			}
		}
		if($com['class']=='static'){
			if(!isset($_REQUEST["fl_".$name])){
				$_REQUEST["fl_".$name] = "";
			}
			foreach($com['items'] as $it){
				if($_REQUEST["fl_".$name]==$it[0]){
					Generator::forms_print("<option value='".$it[0]."' selected='selected'>".$it[1]."</option>\r\n");
				} else {
					Generator::forms_print("<option value='".$it[0]."'>".$it[1]."</option>\r\n");
				}
			}
		}
		Generator::forms_print("</select>\r\n");
		if(!isset($com['use_helper'])){
			$com['use_helper'] = false;
		}
		if($com['use_helper']&&$com['class']=='dynamic'){
			if($com['column_relation'])
			$op = $com['column_relation'];
			else $op = $name;
			Generator::forms_print("<input type='text' style='display:none' id='{$name}_helper' />
			<a href='#helper_$name' name='#helper_$name' onclick='show_helper(\"$name\")' id='helper_new_{$name}'>Nuevo</a>
			<a href='#helper_$name' name='#helper_$name' style='display:none' onclick='save_helper(\"$name\")' id='helper_save_{$name}'>Guardar</a>
			<a href='#helper_$name' name='#helper_$name' style='display:none;font-size:12px;color:red' onclick='cancel_helper(\"$name\")' id='helper_cancel_{$name}'>Cancelar</a>
			<img src='".KUMBIA_PATH."img/spinner.gif' style='display:none' alt='' id='{$name}_spinner' />");

		}

	}

	/**
 	 * Crea los componentes para campos que son Password
 	 *
 	 * @param array $com
 	 * @param string $name
  	 */
	static function build_standard_password($com, $name){
		Generator::forms_print("<strong>".$com['caption']." : </strong></td><td id='tp' valign='top'><input type='password' name='fl_$name' id='flid_$name' disabled='disabled' ");
		if(isset($_REQUEST['fl_'.$name]) && $_REQUEST['fl_'.$name]){
			Generator::forms_print("value = '".$_REQUEST['fl_'.$name]."'");
		}
		if(isset($com['attributes'])){
			foreach($com["attributes"] as $nitem => $item){
				Generator::forms_print(" $nitem='$item' ");
			}
		}
		Generator::forms_print(" onfocus='showConfirmPassword(this)' onblur='nextValidatePassword(this)'");
		Generator::forms_print(" />\r\n");
		Generator::forms_print("<br />
		<div id='div_fl_$name' style='display:none'>
		Reescribir Password:<br />
		<input type='password' name='confirm_fl_$name' id='confirm_flid_$name'");
		if(isset($_REQUEST['fl_'.$name])){
			Generator::forms_print("value = '".$_REQUEST['fl_'.$name]."'");
		}
		if(isset($com['attributes'])){
			foreach($com["attributes"] as $nitem => $item){
				Generator::forms_print(" $nitem='$item' ");
			}
		}
		Generator::forms_print(" onblur='validatePassword(this, \"flid_$name\")' />\r\n</div>");
	}

	/**
 	 * Crea los componentes para campos que son im�genes
 	 *
 	 * @param array $com
 	 * @param string $name
 	 */
	static function build_standard_image($com, $name){
		Generator::forms_print("<label for='flid_$name'><strong>".$com['caption']." : </strong></label></td><td valign='top'>");
		Generator::forms_print("<table><tr><td>");
		if(!isset($_REQUEST['fl_'.$name])){
			$_REQUEST['fl_'.$name] = 'spacer.gif';
		} else	{
			if($_REQUEST['fl_'.$name]=='@'){
				$_REQUEST['fl_'.$name] = 'spacer.gif';
			}
		}
		Generator::forms_print("<img src='".KUMBIA_PATH."img/".urldecode($_REQUEST['fl_'.$name])."'
	    	alt='' id='im_$name' style='border:1px solid black;width:128;height:128px' />");
		Generator::forms_print("</td><td>
		 <select name='fl_$name' id='flid_$name' disabled='disabled'
		 onchange='
		 if(document.getElementById(\"im_$name\")){
		 	document.getElementById(\"im_$name\").src = \$Kumbia.path + \"img/\"+ this.options[this.selectedIndex].value
		 }'>
		 <option value='@'>Seleccione...</option>\n");
		foreach(scandir('public/img/upload/') as $file){
			if($file!='index.html'&&$file!='.'&&$file!='..'
			&&$file!='Thumbs.db'&&$file!='desktop.ini'&&$file!='CVS'){
				$nfile = str_replace('.gif', '', $file);
				$nfile = str_replace('.jpg', '', $nfile);
				$nfile = str_replace('.png', '', $nfile);
				$nfile = str_replace('.bmp', '', $nfile);
				$nfile = str_replace('_', ' ', $nfile);
				$nfile = ucfirst($nfile);
				$nfile = htmlentities($nfile);
				$file = htmlentities($file);
				if(urldecode("upload/$file")==urldecode($_REQUEST['fl_'.$name])){
					Generator::forms_print("<option selected='selected' value='upload/$file'
					style='background: #EAEAEA'>$nfile</option>\n");
				} else {
					Generator::forms_print("<option
					  value='upload/$file'>$nfile</option>\n");
				}
			}
		}
		Generator::forms_print("</select> ");
		Generator::forms_print("
		<input type='file' name='fl_{$name}_up' style='display:none' id='flid_{$name}_up' disabled='disabled' ");
		if(isset($com["attributes"])){
			foreach($com["attributes"] as $nitem => $item){
				if($nitem!='size'){
					Generator::forms_print(" $nitem='$item' ");
				}
			}
		}
		Generator::forms_print("
		onblur='if(document.getElementById(\"im_$name\")){
		 	document.getElementById(\"im_$name\").src = \"file://\"+$(\"flid_{$name}_up\").value
		}'
		/> <a name='a_$name' href='#a_$name' id='a_$name' onclick='show_upload_image(\"$name\")'>Subir Imagen</a>");
		Generator::forms_print("</td></tr></table>");
	}

	/**
	 * Crea un componente de Tiempo
	 *
	 * @param array $com
	 * @param string $name
	 * @param array $form
	 */
	static function build_time_component($com, $name, $form){
		$arr = array();
		Generator::forms_print("<label for='flid_$name'><strong>".$com['caption']." :</strong></label></td><td>\n");
		Generator::forms_print("<select name='time{$name}_hour' id='time{$name}_hour'
		onchange='document.getElementById(\"flid_$name\").value = document.getElementById(\"time{$name}_hour\").options[document.getElementById(\"time{$name}_hour\").selectedIndex].value+\":\"+document.getElementById(\"time{$name}_minutes\").options[document.getElementById(\"time{$name}_minutes\").selectedIndex].value' disabled='disabled'>\n");
		for($i=0;$i<=23;$i++){
			Generator::forms_print("<option value='".sprintf("%02s", $i)."' selected='selected'>".sprintf("%02s", $i)."</option>\n");
		}
		Generator::forms_print("</select>:");
		Generator::forms_print("<select name='time{$name}_minutes' id='time{$name}_minutes'
		onchange='document.getElementById(\"flid_$name\").value = document.getElementById(\"time{$name}_hour\").options[document.getElementById(\"time{$name}_hour\").selectedIndex].value+\":\"+document.getElementById(\"time{$name}_minutes\").options[document.getElementById(\"time{$name}_minutes\").selectedIndex].value' disabled='disabled'>\n");
		for($i=0;$i<=59;$i++){
			Generator::forms_print("<option value='".sprintf("%02s", $i)."' selected='selected'>".sprintf("%02s", $i)."</option>\n");
		}
		Generator::forms_print("</select>");
		Generator::forms_print("<input type='hidden' name='fl_$name' id='flid_$name' value='00:00' />");
	}

}
