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
 * Este archivo debe ser incluido desde un controlador
 * usando include "test/adapters.php"
 *
 * @category Kumbia
 * @package Test
 * @subpackage Adapters
 * @copyright  Copyright (c) 2005-2012 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

	$config = Config::read('databases');

	$user = "root";
	$password = "hea101";
	if(isset($config->database->pdo)){
		$dsn = $config->database->dsn;
		$db = new Db($dsn, $user, $password);
	} else {
		$host = "localhost";
		$dbname = "test";
		$db = new Db($host, $user, $password, $dbname);
	}


	$db->debug = true;
	$total_time = 0;

	$test = true;
	$test_name = "CREAR Y BORRAR UNA TABLA";
	$init_time = $start_benchmark = microtime(true);
	try {
		$value1 = $db->drop_table("kumbia_test");
		if(!$value1){
			throw new DbException("No se pudo crear la tabla de prueba (1)");
		}
		$value2 = $db->create_table("test.kumbia_test", array(
			"id" => array(
				"type" => db::TYPE_INTEGER,
				"not_null" => true,
				"primary" => true,
				"auto" => true
			),
			"texto" => array(
				"type" => db::TYPE_VARCHAR,
				"not_null" => true,
				"size" => 40
			),
			"fecha" => array(
				"type" => db::TYPE_DATE,
			)
		));
		if($value2===false){
			throw new DbException("No se pudo crear la tabla de prueba (2)");
		}
		if(!$db->table_exists("kumbia_test")){
			throw new DbException("No se pudo comprobar la existencia de la tabla de prueba (3)");
		}
	}
	catch(Exception $e){
		$test = false;
		print "<div style='background:#FFBBBB;border:1px solid red'>";
		print "Primer Test '$test_name' (FALL&Oacute;) con mensaje: ({$e->getMessage()})";
		print "</div>";
	}
	if($test){
		$end_benckmark = microtime(true) - $start_benchmark;
		print "<div style='background:#CCFF99;border:1px solid green'>";
		print "Primer Test '$test_name' (OK) con tiempo: ({$end_benckmark})";
		print "</div>";
	}

	$test = true;
	$test_name = "INSERTAR DATOS EN UNA TABLA DE PRUEBA";
	$start_benchmark = microtime(true);
	try {
		$value1 = $db->insert("kumbia_test", array("4", "'Hello'", "'2005-04-04'"));
		if(!$value1){
			throw new DbException("No se puede insertar en la tabla de prueba (1)");
		}
		$value2 = $db->insert("kumbia_test", array("2", "'Hello'", "'2005-02-04'"), array("id", "texto", "fecha"));
		if(!$value2){
			throw new DbException("No se puede insertar en la tabla de prueba (2)");
		}
		$value3 = $db->insert("kumbia_test", array("'Hello'", "'2005-02-04'"), array("texto", "fecha"));
		if(!$value3){
			throw new DbException("No se puede insertar en la tabla de prueba (3)");
		}
		if($db->affected_rows()!=1){
			throw new DbException("No se puede insertar en la tabla de prueba (4)");
		}
		Flash::notice($db->last_insert_id("kumbia_test", "id"));
	}
	catch(Exception $e){
		$test = false;
		print "<div style='background:#FFBBBB;border:1px solid red'>";
		print "Test '$test_name' (FALL&Oacute;) con mensaje: ({$e->getMessage()})";
		print "</div>";
	}
	if($test){
		$end_benckmark = microtime(true) - $start_benchmark;
		print "<div style='background:#CCFF99;border:1px solid green'>";
		print "Test '$test_name' (OK) con tiempo: ({$end_benckmark})";
		print "</div>";
	}

	$test = true;
	$test_name = "ACTUALIZAR DATOS EN UNA TABLA DE PRUEBA";
	$start_benchmark = microtime(true);
	try {
		$value1 = $db->update("kumbia_test", array("texto"), array("'Esto es un Texto'"));
		if(!$value1){
			throw new DbException("No se puede actualizar en la tabla de prueba (1)");
		}
		$value2 = $db->update("kumbia_test", array("texto", "fecha"), array("'Esto es otro Texto'", "'2007-02-02'"), "id = 1");
		if($value2===false){
			throw new DbException("No se puede actualizar en la tabla de prueba (2)");
		}
	}
	catch(Exception $e){
		print "<div style='background:#FFBBBB;border:1px solid red'>";
		print "Test '$test_name' (FALL&Oacute;) con mensaje: ({$e->getMessage()})";
		print "</div>";
	}
	if($test){
		$end_benckmark = microtime(true) - $start_benchmark;
		print "<div style='background:#CCFF99;border:1px solid green'>";
		print "Test '$test_name' (OK) con tiempo: ({$end_benckmark})";
		print "</div>";
	}

	$test = true;
	$test_name = "BORRAR DATOS EN UNA TABLA DE PRUEBA";
	$start_benchmark = microtime(true);
	try {
		$value1 = $db->delete("kumbia_test", "id = 4");
		if(!$value1){
			throw new DbException("No se puede borrar en la tabla de prueba (1)");
		}
	}
	catch(Exception $e){
		print "<div style='background:#FFBBBB;border:1px solid red'>";
		print "Test '$test_name' (FALL&Oacute;) con mensaje: ({$e->getMessage()})";
		print "</div>";
	}
	if($test){
		$end_benckmark = microtime(true) - $start_benchmark;
		print "<div style='background:#CCFF99;border:1px solid green'>";
		print "Test '$test_name' (OK) con tiempo: ({$end_benckmark})";
		print "</div>";
	}

	$test = true;
	$test_name = "CONSULTAR DATOS EN UNA TABLA DE PRUEBA";
	$start_benchmark = microtime(true);
	try {
		$value1 = $db->query("SELECT * FROM kumbia_test ORDER BY id");
		if(!$value1){
			throw new DbException("No se puede consultar en la tabla de prueba (1)");
		}
		while($row = $db->fetch_array()){
			if($row['id']!=0 && $row['id']!=1 && $row['id']!=2 && $row['id']!=4 && $row['id']!=5){
				throw new DbException("No se puede consultar en la tabla de prueba {$row['id']} (2)");
			}
		}
		if(!isset($config->database->pdo)){
			if($db->num_rows()!=2){
				throw new DbException("No se puede consultar en la tabla de prueba (3)");
			}
		}
		$value2 = $db->fetch_one("SELECT * FROM kumbia_test {$db->limit(1)}");
		if(!is_array($value2)){
			throw new DbException("No se puede consultar en la tabla de prueba (4)");
		}
		$value3 = $db->fetch_all("SELECT * FROM kumbia_test");
		if(count($value3)!=2){
			throw new DbException("No se puede consultar en la tabla de prueba (5)");
		}
		$value4 = $db->in_query_assoc("SELECT * FROM kumbia_test {$db->limit(1)}");
		if(count($value4[0])!=3){
			throw new DbException("No se puede consultar en la tabla de prueba (6)");
		}
		$value5 = $db->in_query_num("SELECT * FROM kumbia_test {$db->limit(1)}");
		if(count($value5[0])!=3){
			throw new DbException("No se puede consultar en la tabla de prueba (7)");
		}
		$value6 = $db->in_query("SELECT * FROM kumbia_test {$db->limit(1)}");
		if(count($value6[0])!=6){
			throw new DbException("No se puede consultar en la tabla de prueba (8)");
		}
		if(!isset($config->database->pdo)){
			$value7 = $db->data_seek(1, $value1);
			if(!$value7){
				throw new DbException("No se puede consultar en la tabla de prueba (9)");
			}
		}
		$value8 = $db->fetch_array($value1);
		if($value8['id']!=5 && $value8['id']!=2 && $value8['id']!=0){
			throw new DbException("No se puede consultar en la tabla de prueba {$value8['id']} (10)");
		}
	}
	catch(Exception $e){
		print "<div style='background:#FFBBBB;border:1px solid red'>";
		print "Test '$test_name' (FALL&Oacute;) con mensaje: ({$e->getMessage()})";
		print "</div>";
	}
	if($test){
		$end_benckmark = microtime(true) - $start_benchmark;
		print "<div style='background:#CCFF99;border:1px solid green'>";
		print "Test '$test_name' (OK) con tiempo: ({$end_benckmark})";
		print "</div>";
	}

	$test = true;
	$test_name = "COMPROBAR NOMBRE DE CAMPO";
	$start_benchmark = microtime(true);
	try {
		$value1 = $db->query("SELECT * FROM kumbia_test");
		if(!$value1){
			throw new DbException("No se comprobar nombre del campo (1)");
		}
		$value2 = $db->field_name(1);
		if($value2!='texto'){
			throw new DbException("No se comprobar nombre del campo (2)");
		}
	}
	catch(Exception $e){
		print "<div style='background:#FFBBBB;border:1px solid red'>";
		print "Test '$test_name' (FALL&Oacute;) con mensaje: ({$e->getMessage()})";
		print "</div>";
	}
	if($test){
		$end_benckmark = microtime(true) - $start_benchmark;
		print "<div style='background:#CCFF99;border:1px solid green'>";
		print "Test '$test_name' (OK) con tiempo: ({$end_benckmark})";
		print "</div>";
	}

	$test = true;
	$test_name = "INSTRUCCIONES TRANSACCIONALES";
	$start_benchmark = microtime(true);
	try {
		$db->begin();
		$db->query("delete from kumbia_test");
		$db->rollback();
		$db->begin();
		$db->query("delete from kumbia_test");
		$db->commit();
	}
	catch(Exception $e){
		print "<div style='background:#FFBBBB;border:1px solid red'>";
		print "Test '$test_name' (FALL&Oacute;) con mensaje: ({$e->getMessage()})";
		print "</div>";
	}
	if($test){
		$end_benckmark = microtime(true) - $start_benchmark;
		print "<div style='background:#CCFF99;border:1px solid green'>";
		print "Test '$test_name' (OK) con tiempo: ({$end_benckmark})";
		print "</div>";
	}

	$test = true;
	$test_name = "CONTAR TABLAS Y DESCRIBIR TABLA TEST";
	$start_benchmark = microtime(true);
	try {
		Flash::notice("HAY ".count($db->list_tables())." TABLA(S) EN LA BASE DE DATOS");
		print_r($db->describe_table("kumbia_test"));
	}
	catch(Exception $e){
		print "<div style='background:#FFBBBB;border:1px solid red'>";
		print "Test '$test_name' (FALL&Oacute;) con mensaje: ({$e->getMessage()})";
		print "</div>";
	}
	if($test){
		$end_benckmark = microtime(true) - $start_benchmark;
		print "<div style='background:#CCFF99;border:1px solid green'>";
		print "Test '$test_name' (OK) con tiempo: ({$end_benckmark})";
		print "</div>";
	}

	$test = true;
	$test_name = "CERRAR LA CONEXION A LA BASE DE DATOS";
	$start_benchmark = microtime(true);
	try {
		$value1 = $db->close();
		if(!$value1){
			throw new DbException("No se puede cerrar la conexion (1)");
		}
	}
	catch(Exception $e){
		print "<div style='background:#FFBBBB;border:1px solid red'>";
		print "Test '$test_name' (FALL&Oacute;) con mensaje: ({$e->getMessage()})";
		print "</div>";
	}
	if($test){
		$end_benckmark = microtime(true) - $start_benchmark;
		print "<div style='background:#CCFF99;border:1px solid green'>";
		print "Test '$test_name' (OK) con tiempo: ({$end_benckmark})";
		print "</div>";
	}


	print "<div style='background:#CCFF99;border:1px solid green'>";
	print "<strong>Tiempo total de los Test ".(microtime(true) - $init_time)."</strong>";
	print "</div>";


?>
