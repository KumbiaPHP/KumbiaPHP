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
 * @deprecated Antiguo generador de reportes (legacy)
 * @copyright  Copyright (c) 2005-2012 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

/**
 * @see FPDF
 */
include_once LIBRARY_PATH.'fpdf/fpdf.php';

class PDF extends FPDF
{
	//Cabecera de página
	function Header()
	{
		$this->Ln(10);
	}

	//Pie de página
	function Footer()
	{

		$config = Config::read('config');
		$active_app = Router::get_application();
		//Posición: a 1,5 cm del final
		$this->SetY(-21);
		//Arial italic 8
		$this->SetFont('Arial', '', 7);

		//Posición: a 1,5 cm del final
		$this->SetY(-18);
		//Arial italic 8
		$this->SetFont('Arial','',7);
		//Número de página
		$this->Cell(0,10, $config->$active_app->name,0,0,'C');

		//Posición: a 1,5 cm del final
		$this->SetY(-10);
		//Arial italic 8
		$this->SetFont('Arial','',8);
		//Número de página
		$this->Cell(0,10,'-- '.$this->PageNo().' --',0,0,'C');

	}

}

/**
 * Genera un reporte en PDF
 *
 * @param array $result
 * @param array $sumArray
 * @param string $title
 * @param array $weightArray
 * @param array $headerArray
 */
function pdf($result, $sumArray, $title, $weightArray, $headerArray){

	$config = Config::read('config');
	$active_app = Router::get_application();

	//Orientación
	if($sumArray>200) {
		$orientation = 'L';
	} else {
		$orientation = 'P';
	}

	$numRows = 140;
	//Tipo de Papel
	if($sumArray>250) {
		$paper = 'legal';
	} else {
		$paper = 'letter';
	}

	if($paper=='letter'&&$orientation=='P'){
		$widthPage = 220;
		$numRows = 42;
	}

	if($paper=='legal'&&$orientation=='L'){
		$widthPage = 355;
		$numRows = 30;
	}

	if($paper=='letter'&&$orientation=='L'){
		$widthPage = 270;
		$numRows = 30;
	}

	//Crear Documento PDF
	$pdf = new PDF($orientation, 'mm', $paper);

	$pdf->Open();
	$pdf->AddPage();

	//Nombre del Listado
	$pdf->SetFillColor(255, 255, 255);
	$pdf->AddFont('Verdana','','verdana.php');
	$pdf->SetFont('Verdana','', 14);
	$pdf->SetY(20);
	$pdf->SetX(0);


	$pdf->Ln();

	if($config->$active_app->name){
		$pdf->MultiCell(0, 6, strtoupper($config->$active_app->name), 0, "C", 0);
	}
	$pdf->MultiCell(0, 6, "REPORTE DE ".strtoupper($title), 0, "C", 0);
	$pdf->SetFont('Verdana','', 12);
	if(isset($_SESSION['fecsis'])){
		$pdf->MultiCell(0, 6, "FECHA ".date("Y-m-d"), 0, "C", 0);
	}
	$pdf->Ln();

	//Colores, ancho de línea y fuente en negrita
	$pdf->SetFillColor(0xF2,0xF2, 0xF2);
	$pdf->SetTextColor(0);
	$pdf->SetDrawColor(0,0,0);
	$pdf->SetLineWidth(.2);
	$pdf->SetFont('Arial', 'B', 10);

	if($weightArray[0]<11){
		$weightArray[0] = 11;
	}

	//Parametros del Reporte
	$pos = floor(($widthPage/2)-($sumArray/2));
	$pdf->SetX($pos);
	for($i=0;$i<=count($headerArray)-1;$i++){
		$pdf->Cell($weightArray[$i],7,$headerArray[$i], 1, 0, 'C', 1);
	}
	$pdf->Ln();

	//Restauración de colores y fuentes
	$pdf->SetFillColor(224, 235, 255);
	$pdf->SetTextColor(0);
	$pdf->SetFont('Arial','B', 7);

	//print_r($weightArray);

	//Buscamos y listamos
	$n = 1;
	$p = 1;
	$t = 0;
	foreach($result as $row){
		//$pdf->Cell(Ancho, Alto, contenido, ?, ?, Align)
		if($n>$numRows||($p==1&&($n>$numRows-3))){
			$pdf->AddPage($orientation);
			$pdf->SetY(30);
			$pdf->SetX($pos);
			$pdf->SetFillColor(0xF2,0xF2, 0xF2);
			$pdf->SetTextColor(0);
			$pdf->SetDrawColor(0,0,0);
			$pdf->SetLineWidth(.2);
			$pdf->SetFont('Arial', 'B', 10);
			for($i=0;$i<count($headerArray);$i++){
				$pdf->Cell($weightArray[$i], 7, $headerArray[$i], 1, 0, 'C', 1);
			}
			$pdf->Ln();
			$pdf->SetFillColor(224, 235, 255);
			$pdf->SetTextColor(0);
			$pdf->SetFont('Arial', 'B', 7);
			$n = 1;
			$p++;
		}
		$pdf->SetX($pos);
		for($i=0;$i<=count($row)-1;$i++){
			if(is_numeric($row[$i])){
				$pdf->Cell($weightArray[$i], 5, trim($row[$i]),'LRTB', 0, 'C');
			} else {
				$pdf->Cell($weightArray[$i], 5, trim($row[$i]),'LRTB', 0, 'L');
			}
		}
		$n++;
		$t++;
		$pdf->Ln();

	}

	$pdf->SetX($pos);
	$pdf->SetFont('Arial', 'B', 7);
	$pdf->SetFillColor(0xF2,0xF2, 0xF2);
	$pdf->Cell($weightArray[0], 5, "TOTAL",'LRTB', 0, 'R');
	$pdf->Cell($weightArray[1], 5, $t,'LRTB', 0, 'L');

	/*print "<div style='background: url(img/bg2.jpg) #F2f2f2;border:1px solid #c0c0c0'>
	<table><td><img src='img/information.gif' width='64' height='64'/></td><td>";
	print "Papel: $paper<br>";
	print "Orientación: $orientation<br>";
	print "Ancho Página: $widthPage mm<br>";
	print "Número Páginas: $p<br>";
	print "</td></table></div><br>";*/

	$file = md5(uniqid());
	$pdf->Output(CORE_PATH.'public/temp/'.$file .".pdf", 'F');
	if(isset($raw_output)){
		print "<script type='text/javascript'> window.open('".KUMBIA_PATH."temp/".$file.".pdf', null); </script>";
	} else {
		Generator::forms_print("<script type='text/javascript'> window.open('".KUMBIA_PATH."temp/".$file.".pdf', null); </script>");
	}

}
?>
