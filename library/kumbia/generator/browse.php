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
 * Permite generar la vista de Visualizar en StandardForm
 *
 * @category Kumbia
 * @package Generator
 * @copyright Copyright (c) 2005-2007 Andres Felipe Gutierrez (andresfelipe at vagoogle.net)
 * @license http://www.kumbia.org/license.txt GNU/GPL
 */
class Browse {

	/**
	 * Escribe una URL para la consulta del Registro
	 *
	 * @return string
	 */
	static function writeLocation(){
		$controller = Router::get_controller();
		$app = Router::get_active_app();
		if($app){
			$ret = KUMBIA_PATH.$app."/".$controller."/";
		} else {
			$ret = KUMBIA_PATH.$controller."/";
		}
		$first = true;
		foreach($_GET as $name => $getVar){
			if($name!='url'&&$name!='controller'&&$name!='action'&&$name!='typeOrd'&&$name!='orderBy'&&$name!='limBrowse'&&$name!='numBrowse'&&substr($name, 0, 1)!='/'){
				if($first){
					$ret.="$name=$getVar";
					$first = false;
				} else {
					$ret.="&amp;$name=$getVar";
				}
			}
		}
		return $ret;
	}

	/**
	 * Escribe una URL para el ordenador por columna
	 *
	 * @param string $field
	 * @param string $source
	 * @return string
	 */
	static function doBrowseLocation($field, $source){
		$ret = "";
		$first = true;
		$oBy = false;
		foreach($_GET as $name => $getVar){
			if($name!='orderBy'&&$name!='url'&&$name!='controller'&&$name!='action'){
				if($first){
					$ret.="$name=$getVar";
					$first = false;
				} else {
					$ret.="&amp;$name=$getVar";
				}
			}
		}
		$ret.="&amp;orderBy=$source.$field";
		return $ret;
	}

	/**
	 * Escribe una URL para el ordenador Descendente o Ascendente
	 *
	 * @param string $field
	 * @param string $source
	 * @return string
	 */
	static function doTypeBrowseLocation($field, $source){

		$ret = "";
		$first = true;
		$oBy = false;
		$tOr = false;
		foreach($_GET as $name => $getVar){
			if($name!='orderBy'&&$name!='typeOrd'&&$name!='url'&&$name!='controller'&&$name!='action')
			if($first){
				$ret.="$name=$getVar";
				$first = false;
			} else {
				$ret.="&amp;$name=$getVar";
			}
		}
		$ret.="&amp;orderBy=$source.$field";
		if(!isset($_GET['typeOrd'])){
			$_GET['typeOrd'] = "";
		}
		if($_GET['typeOrd']=='desc'){
			$ret.="&amp;typeOrd=asc";
			$img = "f_up";
		} else {
			$ret.="&amp;typeOrd=desc";
			$img = "f_down";
		}
		return "<a href='$ret'><img src='".KUMBIA_PATH."img/$img.gif' style='border:none; margin: 0 0 0 0;' alt=''></a>";
	}

	/**
	 * Genera el Browse de StandardForm
	 *
	 * @param array $form
	 */
	static function formsBrowse($form){

		$modelName = camelize($form['source']);
		$config = Config::read("environment.ini");
		$mode = kumbia::$models[$modelName]->get_mode();
		$config = $config->$mode;

		Generator::forms_print("&nbsp;</center><br><table cellspacing=0 align='center' cellpadding=5>
	<tr bgcolor='#D0D0D0' style='border-top:1px solid #FFFFFF'>");

		$controller = Router::get_controller();
		$app = Router::get_active_app();
		if($app){
			$app_controller = $app."/".$controller;
		} else {
			$app_controller = $controller;
		}

		$browseSelect = "select ";
		$browseFrom = " from ".$form['source'];
		$browseWhere = " Where 1 = 1";
		$broseLike = "";
		$source = $form['source'];
		$nalias = 1;
		$first = false;
		foreach($form['components'] as $name => $component){
			if(!isset($component['notBrowse'])){
				$component['notBrowse'] = false;
			}
			if(!isset($component['browseCaption'])){
				$component['browseCaption'] = "";
			}
			if(!isset($component['class'])){
				$component['class'] = "";
			}
			if(($component['type']!='hidden')&&(!$component['notBrowse'])){
				if($component['browseCaption']){
					Generator::forms_print("<td align='center' valign='bottom' class='browseHead'>
                <table><tr><td align='center'><a href='".self::doBrowseLocation($name, $form['source'])."'>".$component['browseCaption']."</a></td>
                <td>".self::doTypeBrowseLocation($name, $form['source'])."</td></tr></table></td>\r\n");
				} else {
					Generator::forms_print("<td align='center' valign='bottom'>
                <table><tr><td align='center'><a href='".self::doBrowseLocation($name, $form['source'])."'>".$component['caption']."</a></td>
                <td>".self::doTypeBrowseLocation($name, $form['source'])."</td></tr></table></td>\r\n");
				}
			}
			if(($component['type']=='combo')&&($component['class']=='dynamic')){
				if(!isset($component['notPrepare'])||$component['notPrepare']){
					if($first) {
						$browseSelect.=",";
					} else $first = true;

					if(strpos(" ".$browseFrom, $component['foreignTable'])){
						$alias = "t".$nalias;
						$nalias++;
						$browseFrom.=",".$component['foreignTable']." ".$alias;
					} else {
						$browseFrom.=",".$component['foreignTable'];
						$alias = "";
					}
					if(strpos($component['detailField'], "(")){
						$browseSelect.=$component['detailField']." as $name, $source.$name as pk_$name";
						$browseLike.=" or {$component['detailField']} like '%{$_GET['q']}%'";
					} else {
						if(!$alias){
							$browseSelect.=$component['foreignTable'].".".$component['detailField']." as $name, $source.$name as pk_$name";
						} else {
							$browseSelect.=$alias.".".$component['detailField']." as $name, $source.$name as pk_$name";
						}
					}
					if(isset($component["extraTables"])&&$component["extraTables"]){
						$browseFrom.=",".$component["extraTables"];
					}
					if($component['column_relation']){
						if($alias){
							$browseWhere.=" and ".$alias.".".$component['column_relation']." = ".$form['source'].".".$name;
						} else {
							$browseWhere.=" and ".$component['foreignTable'].".".$component['column_relation']." = ".$form['source'].".".$name;
						}
					} else {
						$browseWhere.=" and ".$component['foreignTable'].".".$name." = ".$form['source'].".".$name;
					}
					if(isset($component["whereCondition"])&&$component['whereCondition']){
						$browseWhere.=" and ".$component['whereCondition'];
					}
				}
			} else {
				if(($component['class']=='static')&&($component['type']=='combo')){
					if($first) {
						$browseSelect.=",";
					} else {
						$first = true;
					}
					if($config->type=='pgsql'){
						$browseSelect.="case ";
					}
					if($config->type=='mysql'){
						for($i=0;$i<=count($component['items'])-2;$i++){
							$browseSelect.="if(".$form['source'].".".$name."='".$component['items'][$i][0]."', '".$component['items'][$i][1]."', ";
						}
					}
					if($config->type=='pgsql'){
						for($i=0;$i<=count($component['items'])-1;$i++){
							$browseSelect.=" when ".$form['source'].".".$name."='".$component['items'][$i][0]."' THEN '".$component['items'][$i][1]."' ";
						}
					}
					if($config->type=='mysql'){
						$browseSelect.="'".$component['items'][$i][1]."')";
						for($j=0;$j<=$i-2;$j++) {
							$browseSelect.=")";
						}
					}
					if($config->type=='pgsql'){
						$browseSelect.=" end ";
					}
					$browseSelect.=" as $name";
				} else {
						if($first) {
							$browseSelect.=",";
						} else {
							$first = true;
						}
						$browseSelect.=$form['source'].".$name";
				}
			}
		}
		$brw = $browseWhere;
		if(!isset($_REQUEST['typeOrd'])) {
			$_REQUEST['typeOrd'] = "asc";
		}
		if(!isset($_REQUEST['orderBy'])){
			ActiveRecord::sql_item_sanizite($_REQUEST['typeOrd']);
			$browseSelect.= $browseFrom.$browseWhere." Order By 1 ".$_REQUEST['typeOrd'];
		} else {
			ActiveRecord::sql_item_sanizite($_REQUEST['typeOrd']);
			ActiveRecord::sql_sanizite($_REQUEST['orderBy']);
			$browseSelect.= $browseFrom.$browseWhere." Order By ".$_REQUEST['orderBy']." ".$_REQUEST['typeOrd'];
		}

		if(!isset($_REQUEST['limBrowse'])){
			$_REQUEST['limBrowse'] = 0;
		} else {
			$_REQUEST['limBrowse'] = intval($_REQUEST['limBrowse']);
		}

		if(!isset($_REQUEST['numBrowse'])){
			$_REQUEST['numBrowse'] = 10;
		} else {
			$_REQUEST['numBrowse'] = intval($_REQUEST['numBrowse']);
		}

		if(isset($_REQUEST['limBrowse'])&&isset($_REQUEST['numBrowse'])){
			if($config->type=='mysql'){
				$browseSelect.=" limit {$_REQUEST['limBrowse']},{$_REQUEST['numBrowse']}";
			}
			if($config->type=='pgsql'){
				$browseSelect.=" offset {$_REQUEST['limBrowse']} limit {$_REQUEST['numBrowse']}";
			}
		}
		Generator::forms_print("<td align='center'colspan='2' valign='bottom'><table><tr><td align='center'>Acciones</td></tr></table></td>");

		if($db = db::raw_connect("mode: $mode")){
			$q = $db->query($browseSelect);
			if($q===false) {
				Flash::error($db->error());
				return;
			}
			$color1 = "browse_primary";
			$hoverColor1 = "browse_primary_active";
			$color2 = "browse_secondary";
			$hoverColor2 = "browse_secondary_active";

			$color = $color1;
			$hoverColor = $hoverColor1;

			if($db->num_rows($q)){
				$nTr = 0;
				$queryBrowse = "select count(*) $browseFrom $brw";
				$qq = $db->query($queryBrowse);
				$num = $db->fetch_array($qq);
				$num = $num[0];
				while($row = $db->fetch_array($q)){
					Generator::forms_print("</tr>\r\n<tr id='nTr$nTr' class='$color'>");
					foreach ($form['components'] as $name => $component) {
						if(!isset($component['notBrowse'])){
							$component['notBrowse'] = false;
						}
						if(!isset($component['format'])){
							$component['format'] = false;
						}
						if(($component['type']!='hidden')&&(!$component['notBrowse'])){
							if($component['format']=='money'){
								$row[$name] = "\$&nbsp;".number_format($row[$name], 0, '.', ',');
							}
							if($component['type']!='image'){
							    $cadena;
								Generator::forms_print("<td align='center' style='border-left:1px solid #D1D1D1'
                            onmouseover='$(\"nTr$nTr\").className=\"$hoverColor\"'
                            onmouseout='$(\"nTr$nTr\").className=\"$color\"'>");//.$row[$name]."</td>");
                            if(isset($component['attributes']['sizeBrowse'])){
                                $cadena = self::cut_string($row[$name], $component['attributes']['sizeBrowse']);
                            } else {
                                $cadena = $row[$name];
                            }
                                Generator::forms_print($cadena."</td>");
							} else {
								Generator::forms_print("<td align='center' style='border-left:1px solid #D1D1D1'
                            onmouseover='$(\"nTr$nTr\").className=\"$hoverColor\"'
                            onmouseout='$(\"nTr$nTr\").className=\"$color\"'
                            ><img src='".KUMBIA_PATH."img/".urldecode($row[$name])."' style='border:1px solid black;width:128;height:128' alt=''></td>");
							}
						}
					}
					$nTr++;
					$pk=self::doPrimaryKey($form, $row);
					if(!$form['unableUpdate']){
						Generator::forms_print("<td style='border-left:1px solid #D1D1D1'><img src='".KUMBIA_PATH."img/edit.gif' title='Editar este Registro' style='cursor:pointer' onclick='window.location=\"".KUMBIA_PATH.$app_controller."/query/&amp;$pk\"' alt=''/></td>");
					}
					if(!$form['unableDelete']){
						Generator::forms_print("<td style='border-left:1px solid #D1D1D1'><img src='".KUMBIA_PATH."img/delete.gif' title='Borrar este Registro' style='cursor:pointer' onclick='if(confirm(\"Seguro desea borrar este Registro?\")) window.location=\"".KUMBIA_PATH.$app_controller."/delete/&amp;$pk\"' alt=''/></td>");
					}
					if($color==$color2) $color = $color1; else $color = $color2;
					if($hoverColor==$hoverColor2) $hoverColor = $hoverColor1; else $hoverColor = $hoverColor2;
				}
				$m = $_REQUEST['limBrowse'] ? $_REQUEST['limBrowse']: 1;
				if(isset($_GET['orderBy'])&&$_GET['orderBy']) {
					$oBy = "&amp;orderBy=".$_GET['orderBy'];
				} else {
					$oBy = "";
				}
				Generator::forms_print("</tr><tr><td bgcolor='#D0D0D0'
			 style='border-left:1px solid #D1D1D1;border-right:1px solid #D1D1D1' align='center'>
			<table>
			 <tr>
			  <td>
			   $m&nbsp;de&nbsp;$num:
			  </td>
			  <td><a href='".KUMBIA_PATH.$app_controller."/browse/&amp;limBrowse=0&amp;numBrowse=10$oBy'
			  title='Ir al Principio'
			  ><img border='0' width=6 height=9 src='".KUMBIA_PATH."img/first.gif' alt=''/></a></td>
			  <td><a
			  href='".KUMBIA_PATH.$app_controller."/browse/&amp;limBrowse=".($_REQUEST['limBrowse']-10<0 ? $_REQUEST['limBrowse'] : $_REQUEST['limBrowse']-10)."&amp;numBrowse=".($_REQUEST['numBrowse'])."$oBy'
			  title='Ir al Anterior'
			  ><img border=0 width=5 height=9 src='".KUMBIA_PATH."img/prev.gif' alt=''/></a></td>
			  <td>
			  <a href='".KUMBIA_PATH.$app_controller."/browse/&amp;limBrowse=".($_REQUEST['limBrowse']+10>$num ? $num-10<0 ? 0 : $num-10 : $_REQUEST['limBrowse']+10)."&amp;numBrowse=".($_REQUEST['numBrowse'])."$oBy'
			  title='Ir al Siguiente $num'
			  ><img border=0 width=5 height=9 src='".KUMBIA_PATH."img/next.gif' alt=''/></a></td>
			  <td>
			  <a href='".KUMBIA_PATH.$app_controller."/browse/&amp;limBrowse=".($num-10<0 ? 0 : $num - 10)."&amp;numBrowse=".($_REQUEST['numBrowse'])."$oBy'
			  title='Ir al Ultimo'
			  ><img border=0 width=6 height=9
			  src='".KUMBIA_PATH."img/last.gif' alt=''/></a></td>
			 </tr>
			</table>
			</td></tr>");
				Generator::forms_print("</table>");
			} else {
				Generator::forms_print("</table>");
				Generator::forms_print("<center><br><br>No Hay Registros Para Visualizar</center>");
			}
			Generator::forms_print("</form>");
			Generator::forms_print("\r\n<br><center><input type='button' class='controlButton'
		value='Volver' onclick='window.location = \"".self::writeLocation()."back"."\"'></center>");
		}
	}

	/**
	 * Crea una referencia de la llave primaria para acceder al registro
	 *
	 * @param array $form
	 * @param array $row
	 * @return string
	 */
	static function doPrimaryKey($form, $row){
		$str = "";
		foreach($form['components']as $name => $com){
			if(isset($com['primary'])&&$com['primary']){
				if(isset($row["pk_".$name])&&$row["pk_".$name]){
					$str.="fl_$name=".$row["pk_".$name]."&amp;";
				} else {
					$str.="fl_$name=".$row[$name]."&amp;";
				}
			}
		}
		return $str;
	}
	
	/**
    * Devuelve el texdto recortado
    *
    * @param string $string
    * @param int $charlimit
    * @return string
    */   
   static function cut_string($string, $charlimit){
      if($string == '' OR $string == 'null'){
         return $string;
      }
      if(substr($string,$charlimit-1,1) != ' '){
         $string = substr($string,'0',$charlimit);
         $array = explode(' ',$string);
         $new_string = implode(' ',$array);
         return $new_string.' ...';
      }else{
         return substr($string,'0',$charlimit-1).' ...';
      }
   }
}
