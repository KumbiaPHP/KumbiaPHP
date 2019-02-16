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
 * @subpackage Adapters
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Oracle Database Support.
 *
 * @category   Kumbia
 * @package    Db
 * @subpackage Adapters
 */
class DbOracle extends DbBase implements DbBaseInterface
{
    /**
     * Resource de la Conexlón a Oracle.
     *
     * @var resource
     */
    public $id_connection;
    /**
     * Último Resultado de una Query.
     *
     * @var resource
     */
    public $last_result_query;
    /**
     * Última sentencia SQL enviada a Oracle.
     *
     * @var string
     */
    protected $last_query;
    /**
     * Último error generado por Oracle.
     *
     * @var string
     */
    public $last_error;
    /**
     * Indica si los modelos usan autocommit.
     *
     * @var bool
     */
    private $autocommit = true;
    /**
     * Número de filas devueltas.
     *
     * @var bool
     */
    private $num_rows = false;

    /**
     * Resultado de Array Asociativo.
     */
    const DB_ASSOC = OCI_ASSOC;

    /**
     * Resultado de Array Asociativo y Numérico.
     */
    const DB_BOTH = OCI_BOTH;

    /**
     * Resultado de Array Numérico.
     */
    const DB_NUM = OCI_NUM;

    /**
     * Tipo de Dato Integer.
     */
    const TYPE_INTEGER = 'INTEGER';

    /**
     * Tipo de Dato Date.
     */
    const TYPE_DATE = 'DATE';

    /**
     * Tipo de Dato Varchar.
     */
    const TYPE_VARCHAR = 'VARCHAR2';

    /**
     * Tipo de Dato Decimal.
     */
    const TYPE_DECIMAL = 'DECIMAL';

    /**
     * Tipo de Dato Datetime.
     */
    const TYPE_DATETIME = 'DATETIME';

    /**
     * Tipo de Dato Char.
     */
    const TYPE_CHAR = 'CHAR';

    /**
     * Constructor de la Clase.
     *
     * @param array $config
     */
    public function __construct($config)
    {
        $this->connect($config);
    }

    /**
     * Hace una conexión a la base de datos de Oracle.
     *
     * @param array $config
     *
     * @return bool
     */
    public function connect(array $config)
    {
        if (!extension_loaded('oci8')) {
            throw new KumbiaException('Debe cargar la extensión de PHP llamada php_oci8');
        }

        if ($this->id_connection = oci_pconnect($config['username'], $config['password'], "{$config['host']}/{$config['name']}", $config['charset'])) {
            /*
             * Cambio el formato de fecha al estandar YYYY-MM-DD
             */
            $this->query("alter session set nls_date_format = 'YYYY-MM-DD'");

            return true;
        }
        throw new KumbiaException($this->error('Error al conectar a Oracle'));
    }

    /**
     * Efectúa operaciones SQL sobre la base de datos.
     *
     * @param string $sqlQuery
     *
     * @return resource|false
     */
    public function query($sqlQuery)
    {
        $this->debug($sqlQuery);
        if ($this->logger) {
            Logger::debug($sqlQuery);
        }

        $this->num_rows = false;
        $this->last_query = $sqlQuery;
        $resultQuery = oci_parse($this->id_connection, $sqlQuery);
        if (!$resultQuery) {
            throw new KumbiaException($this->error("Error al ejecutar <em>'$sqlQuery'</em>"));
        }
        $this->last_result_query = $resultQuery;
        $commit = $this->autocommit ? OCI_COMMIT_ON_SUCCESS : OCI_DEFAULT;
        if (!oci_execute($resultQuery, $commit)) {
            throw new KumbiaException($this->error("Error al ejecutar <em>'$sqlQuery'</em>"));
        }

        return $resultQuery;
    }

    /**
     * Cierra la Conexión al Motor de Base de datos.
     */
    public function close()
    {
        if ($this->id_connection) {
            return oci_close($this->id_connection);
        }
    }

    /**
     * Devuelve fila por fila el contenido de un select.
     *
     * @param resource $resultQuery
     * @param int      $opt
     *
     * @return array
     */
    public function fetch_array($resultQuery = null, $opt = OCI_BOTH)
    {
        if (!$resultQuery) {
            $resultQuery = $this->last_result_query;
            if (!$resultQuery) {
                return false;
            }
        }
        $result = oci_fetch_array($resultQuery, $opt);
        if (is_array($result)) {
            $result_to_lower = array();
            foreach ($result as $key => $value) {
                $result_to_lower[strtolower($key)] = $value;
            }

            return $result_to_lower;
        }

        return false;
    }

    /**
     * Devuelve el número de filas de un select.
     */
    public function num_rows($resultQuery = null)
    {
        if (!$resultQuery) {
            $resultQuery = $this->last_result_query;
            if (!$resultQuery) {
                throw new KumbiaException($this->error('Resource invalido para db::num_rows'));
            }
        }

        // El Adaptador cachea la ultima llamada a num_rows por razones de performance

        /* if($resultQuery==$this->last_result_query){
          if($this->num_rows!==false){
          return $this->num_rows;
          }
          } */
        $commit = $this->autocommit ? OCI_COMMIT_ON_SUCCESS : OCI_DEFAULT;
        if (!oci_execute($resultQuery, $commit)) {
            throw new KumbiaException($this->error("Error al ejecutar <em>'{$this->lastQuery}'</em>"));
        }
        $tmp = array();
        $this->num_rows = oci_fetch_all($resultQuery, $tmp);
        //unset($tmp);
        oci_execute($resultQuery, $commit);

        return $this->num_rows;
    }

    /**
     * Devuelve el nombre de un campo en el resultado de un select.
     *
     * @param int      $number
     * @param resource $resultQuery
     *
     * @return string
     */
    public function field_name($number, $resultQuery = null)
    {
        if (!$resultQuery) {
            $resultQuery = $this->last_result_query;
            if (!$resultQuery) {
                throw new KumbiaException($this->error('Resource invalido para db::field_name'));
            }
        }

        if (($fieldName = oci_field_name($resultQuery, $number + 1)) !== false) {
            return strtolower($fieldName);
        }
        throw new KumbiaException($this->error('No se pudo conseguir el nombre de campo'));
    }

    /**
     * Se mueve al resultado indicado por $number en un select.
     *
     * @param int      $number
     * @param resource $resultQuery
     *
     * @return bool
     */
    public function data_seek($number, $resultQuery = null)
    {
        if (!$resultQuery) {
            $resultQuery = $this->last_result_query;
            if (!$resultQuery) {
                throw new KumbiaException($this->error('Resource invalido para db::data_seek'));
            }
        }
        $commit = $this->autocommit ? OCI_COMMIT_ON_SUCCESS : OCI_DEFAULT;
        if (!oci_execute($resultQuery, $commit)) {
            throw new KumbiaException($this->error("Error al ejecutar <em>'{$this->lastQuery}'</em>"));
        }
        if ($number) {
            for ($i = 0; $i <= $number - 1; ++$i) {
                if (!oci_fetch_row($resultQuery)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Número de Filas afectadas en un insert, update o delete.
     *
     * @param resource $resultQuery
     *
     * @return int
     */
    public function affected_rows($resultQuery = null)
    {
        if (!$resultQuery) {
            $resultQuery = $this->last_result_query;
            if (!$resultQuery) {
                return false;
            }
        }
        if (($numberRows = oci_num_rows($resultQuery)) !== false) {
            return $numberRows;
        }
        throw new KumbiaException($this->error('Resource invalido para db::affected_rows'));
    }

    /**
     * Devuelve el error de Oracle.
     *
     * @return string
     */
    public function error($err = '')
    {
        if (!$this->id_connection) {
            $error = oci_error() ?: '[Error desconocido en Oracle (sin conexión)]';
            if (is_array($error)) {
                return $error['message']." > $err ";
            }

            return $error." > $err ";
        }
        $error = oci_error($this->id_connection);
        if ($error) {
            return $error['message']." > $err ";
        }

        return $err;
    }

    /**
     * Devuelve el no error de Oracle.
     *
     * @return int
     */
    public function no_error()
    {
        if (!$this->id_connection) {
            $error = oci_error() ?: 0;
            if (is_array($error)) {
                return $error['code'];
            }

            return $error;
        }
        $error = oci_error($this->id_connection);

        return $error['code'];
    }

    /**
     * Devuelve un LIMIT válido para un SELECT del RBDM.
     *
     * @param string $sql
     *
     * @return string
     */
    public function limit($sql)
    {
        $number = 0;
        $params = Util::getParams(func_get_args());
        if (isset($params['limit'])) {
            $number = $params['limit'];
        }
        if (!is_numeric($number) || $number < 0) {
            return $sql;
        }
        if (preg_match("/ORDER[\t\n\r ]+BY/i", $sql)) {
            if (stripos($sql, 'WHERE')) {
                return preg_replace("/ORDER[\t\n\r ]+BY/i", "AND ROWNUM <= $number ORDER BY", $sql);
            }

            return preg_replace("/ORDER[\t\n\r ]+BY/i", "WHERE ROWNUM <= $number ORDER BY", $sql);
        }
        if (stripos($sql, 'WHERE')) {
            return "$sql AND ROWNUM <= $number";
        }

        return "$sql WHERE ROWNUM <= $number";
    }

    /**
     * Borra una tabla de la base de datos.
     *
     * @param string $table
     *
     * @return bool
     */
    public function drop_table($table, $if_exists = true)
    {
        if ($if_exists) {
            if ($this->table_exists($table)) {
                return $this->query("DROP TABLE $table");
            }

            return true;
        }

        return $this->query("DROP TABLE $table");
    }

    /**
     * Crea una tabla utilizando SQL nativo del RDBM.
     *
     * TODO:
     * - Falta que el parámetro index funcione. Este debe listar indices compuestos múltipes y únicos
     * - Agregar el tipo de tabla que debe usarse (Oracle)
     * - Soporte para campos autonumericos
     * - Soporte para llaves foraneas
     *
     * @param string $table
     * @param array  $definition
     *
     * @return resource
     */
    public function create_table($table, $definition, $index = array())
    {
        $create_sql = "CREATE TABLE $table (";
        if (!is_array($definition)) {
            throw new KumbiaException("Definición inválida para crear la tabla '$table'");
        }
        $create_lines = array();
        $index = array();
        $unique_index = array();
        $primary = array();
        //$not_null = "";
        //$size = "";
        foreach ($definition as $field => $field_def) {
            if (isset($field_def['not_null'])) {
                $not_null = $field_def['not_null'] ? 'NOT NULL' : '';
            } else {
                $not_null = '';
            }
            if (isset($field_def['size'])) {
                $size = $field_def['size'] ? '('.$field_def['size'].')' : '';
            } else {
                $size = '';
            }
            if (isset($field_def['index']) && $field_def['index']) {
                $index[] = "INDEX($field)";
            }
            if (isset($field_def['unique_index']) && $field_def['unique_index']) {
                $index[] = "UNIQUE($field)";
            }
            if (isset($field_def['primary']) && $field_def['primary']) {
                $primary[] = "$field";
            }
            if (isset($field_def['auto']) && $field_def['auto']) {
                $this->query("CREATE SEQUENCE {$table}_{$field}_seq START WITH 1");
            }
            $extra = isset($field_def['extra']) ? $field_def['extra'] : '';
            $create_lines[] = "$field ".$field_def['type'].$size.' '.$not_null.' '.$extra;
        }
        $create_sql .= join(',', $create_lines);
        $last_lines = array();
        if (count($primary)) {
            $last_lines[] = 'PRIMARY KEY('.join(',', $primary).')';
        }
        if (count($index)) {
            $last_lines[] = join(',', $index);
        }
        if (count($unique_index)) {
            $last_lines[] = join(',', $unique_index);
        }
        if (count($last_lines)) {
            $create_sql .= ','.join(',', $last_lines).')';
        }

        return $this->query($create_sql);
    }

    /**
     * Listado de Tablas.
     *
     * @return bool
     */
    public function list_tables()
    {
        return $this->fetch_all('SELECT table_name FROM all_tables');
    }

    /**
     * Devuelve el último id autonumérico generado en la BD.
     *
     * @return int
     */
    public function last_insert_id($table = '', $primary_key = '')
    {
        if (!$this->id_connection) {
            return false;
        }
        /*
         * Oracle No soporta columnas autonuméricas
         */
        if ($table && $primary_key) {
            $sequence = $table.'_'.$primary_key.'_seq';
            $value = $this->fetch_one("SELECT $sequence.CURRVAL FROM dual");

            return $value[0];
        }

        return false;
    }

    /**
     * Verifica si una tabla existe o no.
     *
     * @param string $table
     *
     * @return bool
     */
    public function table_exists($table, $schema = '')
    {
        $num = $this->fetch_one("SELECT COUNT(*) FROM ALL_TABLES WHERE TABLE_NAME = '".strtoupper($table)."'");

        return $num[0];
    }

    /**
     * Listar los campos de una tabla.
     *
     * @param string $table
     *
     * @return array
     */
    public function describe_table($table, $schema = '')
    {
        /**
         * Soporta schemas?
         */
        $describe = $this->fetch_all("SELECT LOWER(ALL_TAB_COLUMNS.COLUMN_NAME) AS FIELD,
                                        LOWER(ALL_TAB_COLUMNS.DATA_TYPE) AS TYPE,
                                        ALL_TAB_COLUMNS.DATA_LENGTH AS LENGTH, (
                                        SELECT COUNT(*)
                                        FROM ALL_CONS_COLUMNS
                                        WHERE TABLE_NAME = '".strtoupper($table)."' AND ALL_CONS_COLUMNS.COLUMN_NAME = ALL_TAB_COLUMNS.COLUMN_NAME AND ALL_CONS_COLUMNS.POSITION IS NOT NULL) AS KEY, ALL_TAB_COLUMNS.NULLABLE AS ISNULL FROM ALL_TAB_COLUMNS
                                        WHERE ALL_TAB_COLUMNS.TABLE_NAME = '".strtoupper($table)."'");
        $final_describe = [];
        foreach ($describe as $field) {
            $final_describe[] = array(
                'Field' => $field['field'],
                'Type' => $field['type'],
                'Length' => $field['length'],
                'Null' => $field['isnull'] === 'Y' ? 'YES' : 'NO',
                'Key' => $field['key'] == 1 ? 'PRI' : '',
            );
        }

        return $final_describe;
    }

    /**
     * Inicia una transacción si es posible.
     */
    public function begin()
    {
        $this->autocommit = false;
    }

    /**
     * Inicia una transacción si es posible.
     */
    public function commit()
    {
        $this->autocommit = true;

        return oci_commit($this->id_connection);
    }

    /**
     * Revierte una transacción.
     */
    public function rollback()
    {
        return oci_rollback($this->id_connection);
    }

    /**
     * Devuelve la última sentencia sql ejecutada por el Adaptador.
     *
     * @return string
     */
    public function last_sql_query()
    {
        return $this->last_query;
    }
}
