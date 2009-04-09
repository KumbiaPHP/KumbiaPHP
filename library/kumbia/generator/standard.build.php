<?php
/**
 * Kumbia PHP Framework
 * PHP version 5
 * LICENSE
 *
 * This source file is subject to the GNU/GPL that is bundled
 * with this package in the file docs/LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.kumbiaphp.com
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kumbia@kumbiaphp.com so we can send you a copy immediately.
 *
 * @category  Kumbia
 * @package   Generator
 * @author    Andres Felipe Gutierrez <andresfelipe@vagoogle.net>
 * @copyright Copyright (c) 2005-2007 Andres Felipe Gutierrez (andresfelipe at vagoogle.net)
 * @copyright Copyright (C) 2007-2007 Julian Cortes (jucorant at gmail.com)
 * @copyright Copyright (C) 2007-2008 Deivinson Jose Tejeda Brito (deivinsontejeda at gmail.com)
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt GNU/GPL
 */
/**
 * Clase que genera los formularios StandardForm
 * 
 */
abstract class Standard_Generator {
    /**
     * Crea el formulario Standard
     *
     * @param array $form
     */
    static function build_form_standard($form) {
        if (!isset($_REQUEST['value'])) {
            $_REQUEST['value'] = "";
        }
        if (!isset($_REQUEST['option'])) {
            $_REQUEST['option'] = "";
        }
        if (!isset($_REQUEST['queryStatus'])) {
            $_REQUEST['queryStatus'] = false;
        }
        if (!isset($_REQUEST['oldsubaction'])) {
            $_REQUEST['oldsubaction'] = "";
        }
        if (!isset($form['unableInsert'])) {
            $form['unableInsert'] = false;
        }
        if (!isset($form['unableQuery'])) {
            $form['unableQuery'] = false;
        }
        if (!isset($form['unableUpdate'])) {
            $form['unableUpdate'] = false;
        }
        if (!isset($form['unableDelete'])) {
            $form['unableDelete'] = false;
        }
        if (!isset($form['unableBrowse'])) {
            $form['unableBrowse'] = false;
        }
        if (!isset($form['unableReport'])) {
            $form['unableReport'] = false;
        }
        if (!isset($form['fieldsPerRow'])) {
            $form['fieldsPerRow'] = 1;
        }
        if (!isset($form['show_not_nulls'])) {
            $form['show_not_nulls'] = false;
        }
        if (!isset($form['msj_not_null'])) {
            $form['msj_not_null'] = "* Campos Requeridos!";
        }
        if (!isset($form['buttons'])) {
            $form['buttons'] = array();
            $form['buttons']["insert"] = "Adicionar";
            $form['buttons']["query"] = "Consultar";
            $form['buttons']["browse"] = "Visualizar";
            $form['buttons']["report"] = "Reporte";
            
        } else {
        	if (!isset($form['buttons']["insert"])) {
        		$form["buttons"]["insert"] = "Adicionar";
        	}
        	if (!isset($form['buttons']["query"])) {
        		$form["buttons"]["query"] = "Consultar";
        	}
        	if (!isset($form['buttons']["browse"])) {
        		$form["buttons"]["browse"] = "Visualizar";
        	}
        	if (!isset($form['buttons']["report"])) {
        		$form["buttons"]["report"] = "Reporte";
        	}
        }
        $controller = Dispatcher::get_controller();
        $controller_name = Router::get_controller();
        if (!array_key_exists('dataRequisite', $form)) {
            $form['dataRequisite'] = 1;
        }
        if (!$form['dataRequisite']) {
            Generator::forms_print("<font style='font-size:11px'><div align='center'><i><b>No hay datos en consulta</b></i></div></font>");
        } else {
            Generator::forms_print("<center>");
            if ($_REQUEST['oldsubaction'] == 'Modificar') {
                $_REQUEST['queryStatus'] = true;
            }
            if ($controller->view != 'browse') {
                if (!$_REQUEST['queryStatus']) {
                    if (!$form['unableInsert']) {
                        $caption = $form['buttons']['insert'];
                        Generator::forms_print("<input type='button' class='controlButton' id='adiciona' value='$caption' lang='Adicionar' onclick='enable_insert(this)'>&nbsp;");
                    }
                    if (!$form['unableQuery']) {
                        $caption = $form['buttons']['query'];
                        Generator::forms_print("<input type='button' class='controlButton' id='consulta' value='$caption' lang='Consultar' onclick='enable_query(this)'>&nbsp;\r\n");
                    }
                    $ds = "disabled='disabled'";
                } else {
                    $query_string = get_kumbia_url("$controller_name/fetch/");
                    Generator::forms_print("<input type='button' id='primero' class='controlButton' onclick='window.location=\"{$query_string}0/&amp;queryStatus=1\"' value='Primero'>&nbsp;");
                    Generator::forms_print("<input type='button' id='anterior' class='controlButton' onclick='window.location=\"{$query_string}" . ($_REQUEST['id'] - 1) . "/&amp;queryStatus=1\"' value='Anterior'>&nbsp;");
                    Generator::forms_print("<input type='button' id='siguiente' class='controlButton' onclick='window.location=\"{$query_string}" . ($_REQUEST['id'] + 1) . "/&amp;queryStatus=1\"' value='Siguiente'>&nbsp;");
                    Generator::forms_print("<input type='button' id='ultimo' class='controlButton' onclick='window.location=\"{$query_string}last/&amp;queryStatus=1\"' value='Ultimo'>&nbsp;");
                    $ds = "";
                }
                //El Boton de Actualizar
                if ($_REQUEST['queryStatus']) {
                    if (!$form['unableUpdate']) {
                        if (isset($form['buttons']['update'])) {
                            $caption = $form['buttons']['update'] ? $form['buttons']['update'] : "Modificar";
                        } else {
                            $caption = "Modificar";
                        }
                        if (isset($form['updateCondition'])) {
                            if (strpos($form['updateCondition'], '@')) {
                                ereg("[\@][A-Za-z0-9_]+", $form['updateCondition'], $regs);
                                foreach($regs as $reg) {
                                    $form['updateCondition'] = str_replace($reg, $_REQUEST["fl_" . str_replace("@", "", $reg) ], $form['updateCondition']);
                                }
                            }
                            $form['updateCondition'] = " \$val = (" . $form['updateCondition'] . ");";
                            eval($form['updateCondition']);
                            if ($val) {
                                Generator::forms_print("<input type='button' class='controlButton' id='modifica' value='$caption' lang='Modificar' $ds onclick=\"enable_update(this)\">&nbsp;");
                            }
                        } else {
                            Generator::forms_print("<input type='button' class='controlButton' id='modifica' value='$caption' lang='Modificar' $ds onclick=\"enable_update(this)\">&nbsp;");
                        }
                    }
                    //El de Borrar
                    if (!$form['unableDelete']) {
                        if (isset($form['buttons']['delete'])) {
                            $caption = $form['buttons']['delete'] ? $form['buttons']['delete'] : "Borrar";
                        } else {
                            $caption = "Borrar";
                        }
                        Generator::forms_print("<input type='button' class='controlButton' id='borra' value='$caption' lang='Borrar' $ds onclick=\"enable_delete()\">\r\n&nbsp;");
                    }
                }
                if (!$_REQUEST['queryStatus']) {
                    if (!$form['unableBrowse']) {
                        $caption = $form['buttons']['browse'];
                        Generator::forms_print("<input type='button' class='controlButton' id='visualiza' value='$caption' lang='Visualizar' onclick='enable_browse(this, \"$controller_name\")'>&nbsp;\r\n");
                    }
                }
                //Boton de Reporte
                if (!$_REQUEST['queryStatus']) {
                    if (!$form['unableReport']) {
                        $caption = $form['buttons']['report'];
                        Generator::forms_print("<input type='button' class='controlButton' id='reporte' value='$caption' lang='Reporte' onclick='enable_report(this)'>&nbsp;\r\n");
                    }
                } else {
                    Generator::forms_print("<br /><br />\n<input type='button' class='controlButton' id='volver' onclick='window.location=\"" . get_kumbia_url("$controller_name/back") . "\"' value='Atr&aacute;s'>&nbsp;\r\n");
                }
                Generator::forms_print("</center><br />\r\n");
                Generator::forms_print("<table align='center'><tr>\r\n");
                $n = 1;
                //La parte de los Componentes
                Generator::forms_print("<td align='right' valign='top'>\r\n");
                foreach($form['components'] as $name => $com) {
                    switch ($com['type']) {
                        case 'text':
                            Component::build_text_component($com, $name, $form);
                        break;
                        case 'combo':
                            Component::build_standard_combo($com, $name);
                        break;
                        case 'helpText':
                            Component::build_help_context($com, $name, $form);
                        break;
                        case 'userDefined':
                            Component::build_userdefined_component($com, $name, $form);
                        break;
                        case 'time':
                            Component::build_time_component($com, $name, $form);
                        break;
                        case 'password':
                            Component::build_standard_password($com, $name);
                        break;
                        case 'textarea':
                            Component::build_text_area($com, $name);
                        break;
                        case 'image':
                            Component::build_standard_image($com, $name);
                        break;
                            //Este es el Check Chulito
                            
                        case 'check':
                            if ($com['first']) {
                                Generator::forms_print("<b>" . $com['groupcaption'] . "</b></td><td><table cellpadding=0>");
                            }
                            Generator::forms_print("<tr><td>\r\n<input type='checkbox' disabled name='fl_$name' id='flid_$name' style='border:1px solid #FFFFFF'");
                            if ($_REQUEST['fl_' . $name] == $com['checkedValue']) {
                                Generator::forms_print(" checked='checked'  ");
                            }
                            if ($com["attributes"]) {
                                foreach($com["attributes"] as $nitem => $item) {
                                    Generator::forms_print(" $nitem='$item' ");
                                }
                            }
                            Generator::forms_print(">\r\n</td><td>" . $com['caption'] . "</td></tr>");
                            if ($com["last"]) Generator::forms_print("</table>");
                            break;
                            //Textarea
                            
                        case 'textarea':
                            Generator::forms_print("<b>" . $com['caption'] . " :</br></td><td><textarea disabled='disabled' name='fl_$name' id='flid_$name' ");
                            foreach($com['attributes'] as $natt => $vatt) {
                                Generator::forms_print("$natt='$vatt' ");
                            }
                            Generator::forms_print(">" . $_REQUEST['fl_' . $name] . "</textarea>");
                            break;
                            //Oculto
                            
                        case 'hidden':
                            if (!isset($_REQUEST['fl_' . $name])) {
                                $_REQUEST['fl_' . $name] = "";
                            }
                            Generator::forms_print("<input type='hidden' name='fl_$name' id='flid_$name' value='" . (isset($com['value']) ? $com['value'] : $_REQUEST['fl_' . $name]) . "'/>\r\n");
                            break;
                        }
                        if ($form['show_not_nulls']) {
                            if ($com['type'] != 'hidden') {
                                if (isset($com['notNull']) && $com['valueType'] != 'date') {
                                    Generator::forms_print("*\n");
                                }
                            }
                        }
                        if ($com['type'] != 'hidden') {
                            Generator::forms_print("</td>");
                            if ($com['type'] == 'check') {
                                if ($com['last']) {
                                    if (!($n % $form['fieldsPerRow'])) {
                                        Generator::forms_print("</tr><tr>\r\n");
                                    }
                                    $n++;
                                    Generator::forms_print("<td align='right' valign='top'>");
                                }
                            } else {
                                if (!($n % $form['fieldsPerRow'])) {
                                    Generator::forms_print("</tr><tr>\r\n");
                                }
                                $n++;
                                Generator::forms_print("<td align='right' valign='top'>");
                            }
                        }
                }
                if ($form['show_not_nulls']) {
                    Generator::forms_print("</td></tr><tr><td colspan='2' align='center'><i class='requerido'>" . $form['msj_not_null'] . "</i></tr></td>");
                }
                Generator::forms_print("<br /></td></tr><tr>
				<td colspan='2' align='center'>
				<div id='reportOptions' style='display:none' class='report_options'>
				<table>
				<tr>
				<td align='right'>
				<b>Formato Reporte:</b>
					<select name='reportType' id='reportType'>
						<option value='pdf'>PDF</option>
						<option value='xls'>EXCEL</option>
						<option value='doc'>WORD</option>
						<option value='html'>HTML</option>
					</select>
				</td>
				<td align='center'>
				<b>Ordenar Por:</b>
					<select name='reportTypeField' id='reportTypeField'>");
                reset($form['components']);
                for ($i = 0;$i <= count($form['components']) - 1;$i++) {
                    if (!isset($form['components'][key($form['components']) ]['notReport'])) {
                        $form['components'][key($form['components']) ]['notReport'] = false;
                    }
                    if (!$form['components'][key($form['components']) ]['notReport']) {
                        if (isset($form['components'][key($form['components']) ]['caption'])) {
                            Generator::forms_print("<option value ='" . key($form['components']) . "'>" . $form['components'][key($form['components']) ]['caption'] . "</option>");
                        }
                    }
                    next($form['components']);
                }
                Generator::forms_print("</select>
				</td>
				</tr>
				</table>
				</div>
				<br />
				</td>
				</tr>");
                Generator::forms_print("</table><br />\r\n");
            } else {
                /**
                 * @see Browse
                 */
                require_once CORE_PATH.'library/kumbia/generator/browse.php';
                Browse::formsBrowse($form);
            }
            //Todos los Labels
            Generator::forms_print("<script type='text/javascript'>\nvar Labels = {");
            $aLabels = "";
            foreach($form['components'] as $key => $com) {
                if (isset($com['caption'])) {
                    $aLabels.= $key . ": '" . $com['caption'] . "',";
                } else {
                    $aLabels.= $key . ": '$key',";
                }
            }
            $aLabels = substr($aLabels, 0, strlen($aLabels) - 1);
            Generator::forms_print("$aLabels};\r\n");
            //Todos los campos
            Generator::forms_print("var Fields = [");
            reset($form['components']);
            for ($i = 0;$i <= count($form['components']) - 1;$i++) {
                Generator::forms_print("'" . key($form['components']) . "'");
                if ($i != (count($form['components']) - 1)) Generator::forms_print(",");
                next($form['components']);
            }
            Generator::forms_print("];\r\n");
            //Campos que no pueden ser nulos
            Generator::forms_print("var NotNullFields = [");
            reset($form['components']);
            $NotNullFields = "";
            for ($i = 0;$i <= count($form['components']) - 1;$i++) {
                if (!isset($form['components'][key($form['components']) ]['notNull'])) {
                    $form['components'][key($form['components']) ]['notNull'] = false;
                }
                if (!isset($form['components'][key($form['components']) ]['primary'])) {
                    $form['components'][key($form['components']) ]['primary'] = false;
                }
                if ($form['components'][key($form['components']) ]['notNull'] || $form['components'][key($form['components']) ]['primary']) {
                    $NotNullFields.= "'" . key($form['components']) . "',";
                }
                next($form['components']);
            }
            $NotNullFields = substr($NotNullFields, 0, strlen($NotNullFields) - 1);
            Generator::forms_print("$NotNullFields];\r\n");
            Generator::forms_print("var DateFields = [");
            $dFields = "";
            foreach($form['components'] as $key => $value) {
                if (isset($value['valueType'])) {
                    if ($value['valueType'] == 'date') $dFields.= "'" . $key . "',";
                }
            }
            $dFields = substr($dFields, 0, strlen($dFields) - 1);
            Generator::forms_print("$dFields];\r\n");
            //Campos que no son llave
            Generator::forms_print("var UFields = [");
            $uFields = "";
            foreach($form['components'] as $key => $value) {
                if (!$value['primary']) {
                    $uFields.= "'" . $key . "',";
                }
            }
            $uFields = substr($uFields, 0, strlen($uFields) - 1);
            Generator::forms_print("$uFields];\r\n");
            //Campos E-Mail
            Generator::forms_print("var emailFields = [");
            $uFields = "";
            foreach($form['components'] as $key => $value) {
                if (isset($value['valueType'])) {
                    if ($value['valueType'] == 'email') {
                        $uFields.= "'" . $key . "',";
                    }
                }
            }
            $uFields = substr($uFields, 0, strlen($uFields) - 1);
            Generator::forms_print("$uFields];\r\n");
            //Campos Time
            Generator::forms_print("var timeFields = [");
            $uFields = "";
            foreach($form['components'] as $key => $value) {
                if ($value['type'] == 'time') {
                    $uFields.= "'" . $key . "',";
                }
            }
            $uFields = substr($uFields, 0, strlen($uFields) - 1);
            Generator::forms_print("$uFields];\r\n");
            //Campos Time
            Generator::forms_print("var imageFields = [");
            $uFields = "";
            foreach($form['components'] as $key => $value) {
                if ($value['type'] == 'image') {
                    $uFields.= "'" . $key . "',";
                }
            }
            $uFields = substr($uFields, 0, strlen($uFields) - 1);
            Generator::forms_print("$uFields];\r\n");
            //Campos que son llave
            Generator::forms_print("var PFields = [");
            $pFields = "";
            foreach($form['components'] as $key => $value) {
                if ($value['primary']) {
                    $pFields.= "'" . $key . "',";
                }
            }
            $pFields = substr($pFields, 0, strlen($pFields) - 1);
            Generator::forms_print("$pFields];\r\n");
            //Campos que son Auto Numericos
            Generator::forms_print("var AutoFields = [");
            $aFields = "";
            foreach($form['components'] as $key => $value) {
                if (isset($value['auto_numeric'])) {
                    if ($value['auto_numeric']) {
                        $aFields.= "'" . $key . "',";
                    }
                }
            }
            $aFields = substr($aFields, 0, strlen($aFields) - 1);
            Generator::forms_print("$aFields];\r\n");
            Generator::forms_print("var queryOnlyFields = [");
            $rFields = "";
            foreach($form['components'] as $key => $value) {
                if (!isset($value['valueType'])) {
                    $value['valueType'] = "";
                }
                if (!isset($value['queryOnly'])) {
                    $value['queryOnly'] = false;
                }
                if ($value['valueType'] != 'date') {
                    if ($value['queryOnly']) {
                        $rFields.= "'" . $key . "',";
                    }
                }
            }
            $rFields = substr($rFields, 0, strlen($rFields) - 1);
            Generator::forms_print("$rFields];\r\n");
            Generator::forms_print("var queryOnlyDateFields = [");
            $rdFields = "";
            foreach($form['components'] as $key => $value) {
                if (!isset($value['valueType'])) $value['valueType'] = "";
                if (!isset($value['queryOnly'])) $value['queryOnly'] = false;
                if ($value['valueType'] == 'date') {
                    if ($value['queryOnly']) {
                        $rdFields.= "'" . $key . "',";
                    }
                }
            }
            $rdFields = substr($rdFields, 0, strlen($rdFields) - 1);
            Generator::forms_print("$rdFields];\r\n");
            Generator::forms_print("var AddFields = [");
            $aFields = "";
            foreach($form['components'] as $key => $value) {
                if (!isset($value['auto_numeric'])) {
                    $value['auto_numeric'] = false;
                }
                if (!isset($value['attributes']['value'])) {
                    $value['attributes']['value'] = false;
                }
                if ((!$value['auto_numeric']) && (!$value['attributes']['value'])) {
                    $aFields.= "'" . $key . "',";
                }
            }
            $aFields = substr($aFields, 0, strlen($aFields) - 1);
            Generator::forms_print("$aFields];\r\n");
            Generator::forms_print("var AutoValuesFields = [");
            $aFields = "";
            foreach($form['components'] as $key => $value) {
                if (!isset($value['auto_numeric'])) {
                    $value['auto_numeric'] = false;
                }
                if ($value['auto_numeric']) {
                    $aFields.= "'" . $key . "',";
                }
            }
            $aFields = substr($aFields, 0, strlen($aFields) - 1);
            Generator::forms_print("$aFields];\r\n");
            Generator::forms_print("var AutoValuesFFields = [");
            $aFields = "";
            if (!isset($db)) {
                $db = db::raw_connect();
            }
            foreach($form['components'] as $key => $value) {
                if (!isset($value['auto_numeric'])) $value['auto_numeric'] = false;
                if ($value['auto_numeric']) {
                    ActiveRecord::sql_item_sanizite($key);
                    ActiveRecord::sql_item_sanizite($form['source']);
                    $q = $db->query("select max($key)+1 from " . $form['source']);
                    $row = $db->fetch_array($q);
                    $aFields.= "'" . ($row[0] ? $row[0] : 1) . "',";
                }
            }
            $aFields = substr($aFields, 0, strlen($aFields) - 1);
            Generator::forms_print("$aFields];\r\n");
            if (!isset($_REQUEST['param'])) {
                $_REQUEST['param'] = "";
            }
            Generator::forms_print("\nnew Event.observe(window, \"load\", function(){\n");
            if ($controller->keep_action) {
                Generator::forms_print("\tkeep_action('" . $controller->keep_action . "');\n");
            }
            Generator::forms_print("\tregister_form_events()\n})\n</script>\n");
            if ($controller->view != 'browse') {
                Generator::forms_print("<center><input type='button' class='controlButton' id='aceptar' value='Aceptar' disabled='disabled' onclick='form_accept()' />&nbsp;");
                Generator::forms_print("<input type='button' class='controlButton' id='cancelar' value='Cancelar' disabled='disabled' onclick='cancel_form()' />&nbsp;</center>");
                Generator::forms_print("<input type='hidden' id='actAction' value='' />\n
				</form>
                <form id='saveDataForm' method='post' action='' style='display:none' enctype=\"multipart/form-data\"></form>");
            }
        }
    }
}
