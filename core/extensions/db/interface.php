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
 * @package Db
 * @copyright Copyright (c) 2005-2007 Andres Felipe Gutierrez (andresfelipe at vagoogle.net)
 * @copyright Copyright (C) 2006-2007 Giancarlo Corzo Vigil (www.antartec.com)
 * @copyright Copyright (c) 2007-2007 Emilio Rafael Silveira Tovar (emilio.rst at gmail.com)
 * @license http://www.kumbia.org/license.txt GNU/GPL
 */

/**
 * Esta interface expone los metodos que se deben implementar en un driver
 * de Kumbia
 *
 * @category Kumbia
 * @package Db
 * @copyright Copyright (c) 2007-2007 Andres Felipe Gutierrez (andresfelipe at vagoogle.net)
 * @copyright Copyright (C) 2006-2007 Giancarlo Corzo Vigil (www.antartec.com)
 * @license http://www.kumbia.org/license.txt GNU/GPL
 * @access public
 */
interface DbBaseInterface {
	public function connect($config);
	public function query($sql);
	public function fetch_array($resultQuery='', $opt='');
	public function close();
	public function num_rows($resultQuery='');
	public function field_name($number, $resultQuery='');
	public function data_seek($number, $resultQuery='');
	public function affected_rows($result_query='');
	public function error($err='');
	public function no_error();
	public function in_query($sql, $type=db::DB_BOTH);
	public function in_query_assoc($sql);
	public function in_query_num($sql);
	public function fetch_one($sql);
	public function fetch_all($sql);
	public function insert($table, $values, $pk='');
	public function update($table, $fields, $values, $where_condition=null);
	public function delete($table, $where_condition);
	public function limit($sql);
	public function begin();
	public function rollback();
	public function commit();
	public function list_tables();
	public function describe_table($table, $schema='');
	public function last_insert_id($table='', $primary_key='');
	public function create_table($table, $definition, $index=array());
	public function drop_table($table, $if_exists=false);
	public function table_exists($table, $schema='');

}