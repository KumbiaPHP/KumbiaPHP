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
 * Generador de Reportes
 *
 * @category Kumbia
 * @package Report
 *
 * @deprecated Antiguo generador de reportes
 *
 * @copyright  Copyright (c) 2005-2012 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */
class Report {

	static function generate($form){

		$config = Config::read();

		$weightArray = array();
		$headerArray = array();
		$selectedFields = "";
		$tables = "";
		$whereCondition = "";
		$maxCondition = "";
		$n = 0;
		$db = db::raw_connect();

		if(isset($form['dataFilter'])&&$form['dataFilter']){
			if(strpos($form['dataFilter'], '@')){
				ereg("[\@][A-Za-z0-9_]+", $form['dataFilter'], $regs);
				foreach($regs as $reg){
					$form['dataFilter'] = str_replace($reg, $_REQUEST["fl_".str_replace("@", "", $reg)], $form['dataFilter']);
				}
			}
		}
		if($form['type']=='standard'){
			if(isset($form['joinTables'])&&$form['joinTables']) {
				$tables = $form['joinTables'];
			}
			if(isset($form['joinConditions'])&&$form['joinConditions']) {
				$whereCondition = " ".$form['joinConditions'];
			}
			foreach($form['components'] as $name => $com){
				if(!isset($com['attributes']['value'])){
					$com['attributes']['value'] = "";
				}
				if($_REQUEST['fl_'.$name]==$com['attributes']['value']){
					$_REQUEST['fl_'.$name] = "";
				}
				if(trim($_REQUEST["fl_".$name])&&$_REQUEST["fl_".$name]!='@'){
					if($form['components'][$name]['valueType']=='date'){
						$whereCondition.=" and ".$form['source'].".$name = '".$_REQUEST["fl_".$name]."'";
					} else {
						if($form['components'][$name]['valueType']=='numeric'){
							$whereCondition.=" and ".$form['source'].".$name = '".$_REQUEST["fl_".$name]."'";
						} else {
							if($form['components'][$name]['type']=='hidden'){
								$whereCondition.=" and ".$form['source'].".$name = '".$_REQUEST["fl_".$name]."'";
							} else {
								if($com['type']=='check'){
									if($_REQUEST["fl_".$name]==$form['components'][$name]['checkedValue'])
									$whereCondition.=" and ".$form['source'].".$name = '".$_REQUEST["fl_".$name]."'";
								} else {
									if($com['type']=='time'){
										if($_REQUEST["fl_".$name]!='00:00'){
											$whereCondition.=" and {$form['source']}.$name = '".$_REQUEST["fl_".$name]."'";
										}
									} else {
										if($com['primary']||$com['type']=='combo'){
											$whereCondition.=" and ".$form['source'].".$name = '".$_REQUEST["fl_".$name]."'";
										} else {
											$whereCondition.=" and ".$form['source'].".$name like '%".$_REQUEST["fl_".$name]."%'";
										}
									}
								}
							}
						}
					}
				}
			}
		}

		//Modificaciones para seleccion de la ordenacion del report, si esta acabado en _id, quiere decir foreignkey
		//Cojeremos el texto sin el id, tendremos la tabla
		ActiveRecord::sql_item_sanizite($_REQUEST['reportTypeField']);
		if (substr($_REQUEST['reportTypeField'],strlen($_REQUEST['reportTypeField']) -3,strlen($_REQUEST['reportTypeField'])) == "_id"){
			$OrderFields = substr($_REQUEST['reportTypeField'],0,strlen($_REQUEST['reportTypeField'])-3);
		}else{
			$OrderFields =$_REQUEST['reportTypeField'];
		}
		$maxCondition = $whereCondition;
		$n = 0;
		foreach($form['components'] as $name => $com){
			if(!isset($com['notReport'])){
				$com['notReport'] = false;
			}
			if(!isset($com['class'])){
				$com['class'] = false;
			}
			if(!$com['notReport']){
				if(isset($com['caption'])&&$com['caption']){
					$headerArray[$n] = str_replace("&oacute;", "ó", $com['caption']);
					$headerArray[$n] = str_replace("&aacute;", "á", $headerArray[$n]);
					$headerArray[$n] = str_replace("&eacute;", "é", $headerArray[$n]);
					$headerArray[$n] = str_replace("&iacute;", "í", $headerArray[$n]);
					$headerArray[$n] = str_replace("&uacute;", "ú", $headerArray[$n]);
					$headerArray[$n] = str_replace("<br/>", " ", $headerArray[$n]);
				} else {
					$com['caption'] = "";
				}
				if($com['type']=='combo'&&$com['class']=='dynamic'){
					if(isset($com['extraTables'])&&$com['extraTables']){
						$tables.="{$com['extraTables']},";
					}
					if(isset($com['whereConditionOnQuery'])&&$com['whereConditionOnQuery']){
						$whereCondition.=" and {$com['whereConditionOnQuery']}";
					}
					if(strpos(" ".$com['detailField'], "concat(")){
						$selectedFields.=$com['detailField'].",";
					} else {
						$selectedFields.=$com['foreignTable'].".".$com['detailField'].",";
						//Comparamos la Tabla foranea que tenemos, y cuando sea igual, suponiendo no hay
						//mas de una clave foranea por tabla, sabremos a que tabla pertenece
						if ($com['foreignTable'] == $OrderFields){
							$OrderFields = $com['foreignTable'].".".$com['detailField'];
						}
					}
					$tables.=$com['foreignTable'].",";
					if($com['column_relation']){
						$whereCondition.=" and ".$com['foreignTable'].".".$com['column_relation']." = ".$form['source'].".".$name;
					} else {
						$whereCondition.=" and ".$com['foreignTable'].".".$name." = ".$form['source'].".".$name;
					}
					$weightArray[$n] = strlen($headerArray[$n])+2;
					$n++;
				} else {
					if($com['type']!='hidden'){
						if($com['class']=='static' && isset($com['type']) && $com['type']=='combo'){
							$weightArray[$n] = strlen($headerArray[$n])+2;
							if($config->type=='pgsql'){
								$selectedFields.="case ";
							}
							if($config->type=='mysql'){
								for($i=0;$i<=count($com['items'])-2;$i++){
									$selectedFields.="if(".$form['source'].".".$name."='".$com['items'][$i][0]."', '".$com['items'][$i][1]."', ";
									if($weightArray[$n]<strlen($com['items'][$i][1])) {
										$weightArray[$n] = strlen($com['items'][$i][1])+1;
									}
								}
							}

							if($config->type=='pgsql'){
								for($i=0;$i<=count($com['items'])-1;$i++){
									$selectedFields.=" when ".$form['source'].".".$name."='".$com['items'][$i][0]."' THEN '".$com['items'][$i][1]."' ";
									if($weightArray[$n]<strlen($com['items'][$i][1])) {
										$weightArray[$n] = strlen($com['items'][$i][1])+1;
									}
								}
							}


							$n++;
							if($config->type=='mysql'){
								$selectedFields.="'".$com['items'][$i][1]."')";
								for($j=0;$j<=$i-2;$j++) {
									$selectedFields.=")";
								}
							}
							if($config->type=='pgsql'){
								$selectedFields.=" end ";
							}
							$selectedFields.=",";
						} else {
							$selectedFields.=$form['source'].".".$name.",";
							//Aqui seguro que no es foranea, entonces tenemos que poner la tabla principal 							//
							//antes para evitar repeticiones
							if ($name == $OrderFields){
								$OrderFields = $form['source'].".".$OrderFields;
							}
							$weightArray[$n] = strlen($headerArray[$n])+2;
							$n++;
						}
					}
				}
			}
		}
		$tables.=$form['source'];
		$selectedFields = substr($selectedFields, 0, strlen($selectedFields)-1);

		if(isset($form['dataRequisite'])&&$form['dataRequisite']){
			$whereCondition.=" and {$form['dataFilter']}";
		}

		//Modificacion del order
		if($OrderFields){
			$OrderCondition = "Order By ".$OrderFields;
		} else {
			$OrderCondition = "";
		}

		$query = "select $selectedFields from $tables where 1 = 1 ".$whereCondition. " " .$OrderCondition;

		$q = $db->query($query);
		if(!is_bool($q)){
			if(!$db->num_rows($q)){
				Flash::notice("No hay informaci&oacute;n para listar");
				return;
			}
		} else {
			Flash::error($db->error());
			return;
		}

		$result = array();
		$n = 0;
		while($row = $db->fetch_array($q, db::DB_NUM)){
			$result[$n++] = $row;
		}

		foreach($result as $row){
			for($i=0;$i<=count($row)-1;$i++){
				if($weightArray[$i]<strlen(trim($row[$i]))){
					$weightArray[$i] = strlen(trim($row[$i]));
				}
			}
		}

		for($i=0;$i<=count($weightArray)-1;$i++){
			$weightArray[$i]*= 1.8;
		}

		$sumArray = array_sum($weightArray);

		if(!$_REQUEST['reportType']){
			$_REQUEST['reportType'] = 'pdf';
		}

		if($_REQUEST['reportType']!='html'){
			$title = str_replace("&oacute;", "ó", $form['caption']);
			$title = str_replace("&aacute;", "á", $title);
			$title = str_replace("&eacute;", "é", $title);
			$title = str_replace("&iacute;", "í", $title);
			$title = str_replace("&uacute;", "ú", $title);
		} else {
			$title = $form['caption'];
		}

		switch($_REQUEST['reportType']){
			case 'pdf':
				require_once CORE_PATH . 'extensions/report/format/pdf.php';
				pdf($result, $sumArray, $title, $weightArray, $headerArray);
			break;
			case 'xls':
				require_once CORE_PATH . 'extensions/report/format/xls.php';
				xls($result, $sumArray, $title, $weightArray, $headerArray);
			break;
			case 'html':
				require_once CORE_PATH . 'extensions/report/format/htm.php';
				htm($result, $sumArray, $title, $weightArray, $headerArray);
			break;
			case 'doc':
				require_once CORE_PATH . 'extensions/report/format/doc.php';
				doc($result, $sumArray, $title, $weightArray, $headerArray);
			break;
			default:
				require_once CORE_PATH . 'extensions/report/format/pdf.php';
				pdf($result, $sumArray, $title, $weightArray, $headerArray);
			break;
		}

	}
}

?>
