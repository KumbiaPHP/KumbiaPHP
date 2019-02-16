<?php
/**
 * KumbiaPHP web & app Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @category   Kumbia
 * @package    Db
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Interfaz para los adaptadores de bases de datos.
 *
 * Esta interface expone los metodos que se deben implementar en un driver
 * de KumbiaPHP
 *
 * @category   Kumbia
 */
interface DbBaseInterface
{
    /**
     * @return bool
     */
    public function connect(array $config);

    /**
     * Efectua operaciones SQL sobre la base de datos
     * Este método lo extienden los adapters.
     *
     * @param string $sql_query
     *
     * @return resource|false
     */
    public function query($sql);

    /**
     * Devuelve fila por fila el contenido de un select
     * Este método lo extienden los adapters.
     *
     * @param resource $resultQuery
     * @param int      $opt
     *
     * @return array
     */
    public function fetch_array($resultQuery = null, $opt = '');

    public function close();

    public function num_rows($resultQuery = null);

    public function field_name($number, $resultQuery = null);

    /**
     * @return bool
     */
    public function data_seek($number, $resultQuery = null);

    public function affected_rows($result_query = null);

    /**
     * @return string
     */
    public function error($err = '');

    public function no_error();

    public function in_query($sql);

    public function in_query_assoc($sql);

    public function in_query_num($sql);

    public function fetch_one($sql);

    public function fetch_all($sql);

    /**
     * @return bool
     */
    public function insert($table, array $values, $pk = '');

    /**
     * @param string $where_condition
     *
     * @return bool
     */
    public function update($table, array $fields, array $values, $where_condition = null);

    /**
     * @param string $where_condition
     */
    public function delete($table, $where_condition);

    /**
     * @return string
     */
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
