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
 * Generador de Reportes
 *
 * @category Kumbia
 * @package Report
 * @deprecated Antiguo generador de reportes (legacy)
 * @copyright  Copyright (c) 2005-2012 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */
require_once LIBRARY_PATH.'excel/main.php';
/**
 * Genera un reporte en Excel
 *
 * @param array $result
 * @param array $sumArray
 * @param string $title
 * @param array $weightArray
 * @param array $headerArray
 */
function xls($result, $sumArray, $title, $weightArray, $headerArray){

	error_reporting(0);

	$file = md5(uniqid());
	$config = Config::read('config');
	$active_app = Router::get_application();

	$workbook = new Spreadsheet_Excel_Writer("public/temp/$file.xls");
	$worksheet =& $workbook->addWorksheet();

	$titulo_verdana  =& $workbook->addFormat(array('fontfamily' => 'Verdana',
	'size' => 20));
	$titulo_verdana2 =& $workbook->addFormat(array('fontfamily' => 'Verdana',
	'size' => 18));

	$workbook->setCustomColor(12, 0xF2, 0xF2, 0xF2);

	$column_title =& $workbook->addFormat(array('fontfamily' => 'Verdana',
	'size' => 12,
	'fgcolor' => 12,
	'border' => 1,
	'bordercolor' => 'black',
	"halign" => 'center'
	));

	$column =& $workbook->addFormat(array(	'fontfamily' => 'Verdana',
	'size' => 11,
	'border' => 1,
	'bordercolor' => 'black',
	));

	$column_centered =& $workbook->addFormat(array(	'fontfamily' => 'Verdana',
	'size' => 11,
	'border' => 1,
	'bordercolor' => 'black',
	"halign" => 'center'
	));

	$worksheet->write(0, 0, strtoupper($config->$active_app->name), $titulo_verdana);
	$worksheet->write(1, 0, "REPORTE DE ".strtoupper($title), $titulo_verdana2);
	$worksheet->write(2, 0, "FECHA ".date("Y-m-d"), $titulo_verdana2);

	for($i=0;$i<=count($headerArray)-1;$i++){
		$worksheet->setColumn($i, $i, $weightArray[$i]);
		$worksheet->write(4, $i, $headerArray[$i], $column_title);
	}

	$l = 5;
	foreach($result as $row){
		for($i=0;$i<=count($row)-1;$i++){
			if(!is_numeric($row[$i])){
				$worksheet->writeString($l, $i, $row[$i], $column);
			} else {
				$worksheet->writeString($l, $i, $row[$i], $column_centered);
			}
		}
		$l++;
	}

	$workbook->close();

	error_reporting(E_ALL ^ E_STRICT);

	if(isset($raw_output)){
		print "<script type='text/javascript'> window.open('".KUMBIA_PATH."temp/".$file.".xls', null);  </script>";
	} else {
		Generator::forms_print("<script type='text/javascript'> window.open('".KUMBIA_PATH."temp/".$file.".xls', null);  </script>");
	}

}


?>
