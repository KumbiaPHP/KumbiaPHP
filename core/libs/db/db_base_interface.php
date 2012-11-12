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
 * @category   Kumbia
 * @package    Db 
 * @copyright  Copyright (c) 2005-2012 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

/**
 * Interfaz para los adaptadores de bases de datos
 *
 * Esta interface expone los metodos que se deben implementar en un driver
 * de KumbiaPHP
 *
 * @category   Kumbia
 * @package    Db 
 */
interface DbBaseInterface
{
    public function connect($config);

    public function query($sql);

    public function fetch_array($resultQuery = '', $opt = '');

    public function close();

    public function num_rows($resultQuery = '');

    public function field_name($number, $resultQuery = '');

    public function data_seek($number, $resultQuery = '');

    public function affected_rows($result_query = '');

    public function error($err = '');

    public function no_error();

    public function in_query($sql);

    public function in_query_assoc($sql);

    public function in_query_num($sql);

    public function fetch_one($sql);

    public function fetch_all($sql);

    public function insert($table, $values, $pk = '');

    public function update($table, $fields, $values, $where_condition = null);

    public function delete($table, $where_condition);

    public function limit($sql);

    public function begin();

    public function rollback();

    public function commit();

    public function list_tables();

    public function describe_table($table, $schema = '');

    public function last_insert_id($table = '', $primary_key = '');

    public function create_table($table, $definition, $index = array());

    public function drop_table($table, $if_exists = false);

    public function table_exists($table, $schema = '');
}