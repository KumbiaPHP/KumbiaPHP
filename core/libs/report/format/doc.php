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
 * @deprecated Antiguo generar de reportes heredado (legacy). Completamente obsoleto
 * @copyright  Copyright (c) 2005-2012 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */
function doc($result, $sumArray, $title, $weighArray, $headerArray){

	$config = Config::read('config');
	$active_app = Router::get_application();
	$file = md5(uniqid());

	$content = "
<html>
 <head>
   <title>REPORTE DE ".strtoupper($title)."</title>
 </head>
 <body bgcolor='white'>
 <div style='font-size:20px;font-family:Verdana;color:#000000'>".strtoupper($config->$active_app->name)."</div>\n
 <div style='font-size:18px;font-family:Verdana;color:#000000'>REPORTE DE ".strtoupper($title)."</div>\n
 <div style='font-size:18px;font-family:Verdana;color:#000000'>".date("Y-m-d")."</div>\n
 <br/>
 <table cellspacing='0' border=1 style='border:1px solid #969696'>
 ";
	$content.= "<tr bgcolor='#F2F2F2'>\n";
	for($i=0;$i<=count($headerArray)-1;$i++){
		$content.= "<th style='font-family:Verdana;font-size:12px'>".$headerArray[$i]."</th>\n";
	}
	$content.= "</tr>\n";

	$l = 5;
	foreach($result as $row){
		$content.= "<tr bgcolor='white'>\n";
		for($i=0;$i<=count($row)-1;$i++){
			if(is_numeric($row[$i])){
				$content.= "<td style='font-family:Verdana;font-size:12px' align='center'>{$row[$i]}</td>";
			} else {
				$content.= "<td style='font-family:Verdana;font-size:12px'>{$row[$i]}&nbsp;</td>";
			}
		}
		$content.= "</tr>\n";
		$l++;
	}

	file_put_contents("public/temp/$file.doc", $content);
	if(isset($raw_output)){
		print "<script type='text/javascript'> window.open('".KUMBIA_PATH."temp/".$file.".doc', null);  </script>";
	} else {
		Generator::forms_print("<script type='text/javascript'> window.open('".KUMBIA_PATH."temp/".$file.".doc', null);  </script>");
	}

}

?>
