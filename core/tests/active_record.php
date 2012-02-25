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
 * usando include "test/active_record.php"
 *
 * @category Kumbia
 * @package Test
 * @subpackage ActiveRecord
 * @copyright  Copyright (c) 2005-2012 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

	$config = Config::read('databases');

	$db = db::raw_connect();

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
		$value2 = $db->create_table("kumbia_test", array(
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
			),
			"email" => array(
				"type" => db::TYPE_VARCHAR,
				"size" => 70
			),
			"numero" => array(
				"type" => db::TYPE_INTEGER,
			)
		));
		if($value2===false){
			throw new DbException("No se pudo crear la tabla de prueba (2)");
		}
		if(!$db->table_exists("kumbia_test")){
			throw new DbException("No se pudo comprobar la existencia de la tabla de prueba (3)");
		}

		//Crear modelo dinamicamente
		eval("class KumbiaTest extends ActiveRecord {

			function __construct(){
				\$this->validates_numericality_of('numero');
				\$this->validates_presence_of('numero');
				\$this->validates_email_in('email');
				\$this->validates_date_in('fecha');
				\$this->validates_uniqueness_of('texto');
			}

		} ");
		unset($_SESSION['KUMBIA_META_DATA'][$_SESSION['KUMBIA_PATH']]["kumbia_test"]);
		$model = new KumbiaTest();
		if(!is_subclass_of($model, "ActiveRecord")){
			throw new DbException("No se pudo crear el modelo de prueba (3)");
		}

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
	$test_name = "INSERTAR DATOS DE PRUEBA EN EL MODELO";
	$init_time = $start_benchmark = microtime(true);
	try {
		$model->debug = true;
		for($i=1;$i<=20;$i++){
			$model->texto = "Texto ".$i;
			$model->fecha = "2007-02-".sprintf("%02d", rand(1, 10));
			$model->email = "kumbia@com";
			$model->numero = rand(0, 5);
			$model->create();
		}
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
	$test_name = "ACTUALIZAR DATOS DE PRUEBA EN EL MODELO";
	$start_benchmark = microtime(true);
	try {
		for($i=1;$i<=20;$i+=5){
			$model = $model->find($i);
			if($model){
				$model->numero = "100";
				$model->update();
			} else {
				throw new DbException("No Devolvio el objeto para id = $i");
			}
		}
		$model->update_all("email = 'hello@com'");
		$model->update_all("texto = 'otro texto'", "id <= 10");
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
	$test_name = "CONSULTAR DATOS DE PRUEBA EN EL MODELO";
	$start_benchmark = microtime(true);
	try {
		$model = new KumbiaTest();
		$model->debug = true;
		$model->find();
		if($model->count!=20){
			throw new DbException("No devolvio el numero correcto de registros en la tabla (1)");
		}
		$model->find_first(11);
		if($model->numero!=100){
			throw new DbException("No devolvio el registro correcto para id = 11 (2)");
		}
		$otro_model = $model->find_first(11);
		if($otro_model->numero!=100){
			throw new DbException("No devolvio el registro correcto para id = 11 (3)");
		}
		$model->find("numero = 100");
		if($model->count!=4){
			throw new DbException("No devolvio el numero correcto de registros en la tabla (4)");
		}
		$results = $model->find("numero = 100", "order: id desc");
		if($results[0]->id!=16){
			throw new DbException("No devolvio el registro correcto al ordenar (5)");
		}
		if(count($results)!=4){
			throw new DbException("No devolvio el numero de registros correcto al ordenar (6)");
		}
		$results = $model->find("conditions: numero = 100", "limit: 1", "order: id asc");
		if(count($results)!=1){
			throw new DbException("No devolvio el registro correcto cuando se uso limit y ordenamiento (7)");
		}
		if($results[0]->id!=1){
			throw new DbException("No devolvio el registro correcto cuando se uso limit y ordenamiento {$results[0]->id} (8)");
		}
		$min = $model->minimum("id", "conditions: numero = 100");
		if($min!=1){
			throw new DbException("No devolvio el minimum correcto (9)");
		}
		$max = $model->maximum("id", "conditions: numero = 100");
		if($max!=16){
			throw new DbException("No devolvio el maximum correcto (10)");
		}
		$sum = $model->sum("id", "conditions: numero = 100");
		if($sum!=34){
			throw new DbException("No devolvio el sum correcto (11)");
		}
		$avg = $model->average("id", "conditions: numero = 100");
		if($avg!=8.5){
			throw new DbException("No devolvio el avg correcto (12)");
		}
		$model->find_first("numero = 100");
		if($model->id!=1){
			throw new DbException("find_first con condicion fallo (13)");
		}
		$model->find_first(15);
		if($model->id!=15){
			throw new DbException("find_first a llave primaria (14)");
		}
		$model2 = $model->find_first("id > 10");
		if($model2->id!=11){
			throw new DbException("find_first a condicion (15)");
		}
		if($model->count()!=20){
			throw new DbException("count sin parametros (16)");
		}
		if($model->count("numero = 100")!=4){
			throw new DbException("count con parametros (17)");
		}
		if(count($model->distinct("id", "conditions: numero = 100"))!=4){
			throw new DbException("fallo distinct (18)");
		}
		$rows = $model->find_all_by_sql("SELECT * FROM kumbia_test WHERE id > 11 AND id < 14 ORDER BY 1");
		if($rows[0]->id!=12){
			throw new DbException("fallo find_all_by_sql (19)");
		}
		$row = $model->find_by_sql("SELECT * FROM kumbia_test WHERE id > 11 AND id < 13 ORDER BY 1");
		if($row->id!=12){
			throw new DbException("fallo find_by_sql (20)");
		}
		if(count($model->find_all_by_numero(100))!=4){
			throw new DbException("fallo find_all_by_numero (21)");
		}
		$model->find_by_id(16);
		if($model->id!=16){
			throw new DbException("fallo find_by_id (22)");
		}
		$num = $model->count_by_numero(100);
		if($model->id!=16){
			throw new DbException("fallo find_by_id (22)");
		}
	}
	catch(Exception $e){
		$test = false;
		print "<div style='background:#FFBBBB;border:1px solid red'>";
		print "Test '$test_name' (FALL&Oacute;) con mensaje: ({$e->getMessage()})";
		print "</div>";
		return;
	}
	if($test){
		$end_benckmark = microtime(true) - $start_benchmark;
		print "<div style='background:#CCFF99;border:1px solid green'>";
		print "Test '$test_name' (OK) con tiempo: ({$end_benckmark})";
		print "</div>";
	}

	$test = true;
	$test_name = "ELIMINAR REGISTROS DE PRUEBA EN EL MODELO";
	$start_benchmark = microtime(true);
	try {
		$model->delete(18);
		$model->delete_all("id < 10");
		$model->delete_all();
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


	print "<div style='background:#CCFF99;border:1px solid green'>";
	print "<strong>Tiempo total de los Test ".(microtime(true) - $init_time)."</strong>";
	print "</div>";

?>
