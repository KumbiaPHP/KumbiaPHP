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
 * SQLite Database Support.
 *
 * @category   Kumbia
 * @package    Db
 * @subpackage Adapters
 */
class DbSQLite extends DbBase implements DbBaseInterface
{
    /**
     * Resource de la Conexion a SQLite.
     *
     * @var resource
     */
    public $id_connection;
    /**
     * Ultimo Resultado de una Query.
     *
     * @var resource
     */
    public $last_result_query;
    /**
     * Última sentencia SQL enviada a SQLite.
     *
     * @var string
     */
    protected $last_query;
    /**
     * Ultimo error generado por SQLite.
     *
     * @var string
     */
    public $last_error;

    /**
     * Resultado de Array Asociativo.
     */
    const DB_ASSOC = SQLITE_ASSOC;

    /**
     * Resultado de Array Asociativo y Numerico.
     */
    const DB_BOTH = SQLITE_BOTH;

    /**
     * Resultado de Array Numérico.
     */
    const DB_NUM = SQLITE_NUM;

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
    const TYPE_VARCHAR = 'VARCHAR';

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
     * Hace una conexion a la base de datos de SQLite.
     *
     * @param array $config
     *
     * @return bool
     */
    public function connect(array $config)
    {
        if (!extension_loaded('sqlite')) {
            throw new KumbiaException('Debe cargar la extensión de PHP llamada sqlite');
        }
        if ($this->id_connection = sqlite_open(APP_PATH.'config/sql/'.$config['name'])) {
            return true;
        }
        throw new KumbiaException($this->error('No se puede conectar a la base de datos'));
    }

    /**
     * Efectua operaciones SQL sobre la base de datos.
     *
     * @param string $sqlQuery
     *
     * @return resource or false
     */
    public function query($sqlQuery)
    {
        $this->debug($sqlQuery);
        if ($this->logger) {
            Logger::debug($sqlQuery);
        }

        $this->last_query = $sqlQuery;
        if ($resultQuery = sqlite_query($this->id_connection, $sqlQuery)) {
            $this->last_result_query = $resultQuery;

            return $resultQuery;
        }
        throw new KumbiaException($this->error(" al ejecutar <em>'$sqlQuery'</em>"));
    }

    /**
     * Cierra la Conexión al Motor de Base de datos.
     */
    public function close()
    {
        if ($this->id_connection) {
            sqlite_close($this->id_connection);
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
    public function fetch_array($resultQuery = '', $opt = SQLITE_BOTH)
    {
        if (!$resultQuery) {
            $resultQuery = $this->last_result_query;
            if (!$resultQuery) {
                return false;
            }
        }

        return sqlite_fetch_array($resultQuery, $opt);
    }

    /**
     * Devuelve el número de filas de un select.
     */
    public function num_rows($resultQuery = '')
    {
        if (!$resultQuery) {
            $resultQuery = $this->last_result_query;
            if (!$resultQuery) {
                return false;
            }
        }
        if (($numberRows = sqlite_num_rows($resultQuery)) !== false) {
            return $numberRows;
        }
        throw new KumbiaException($this->error());
    }

    /**
     * Devuelve el nombre de un campo en el resultado de un select.
     *
     * @param int      $number
     * @param resource $resultQuery
     *
     * @return string
     */
    public function field_name($number, $resultQuery = '')
    {
        if (!$resultQuery) {
            $resultQuery = $this->last_result_query;
            if (!$resultQuery) {
                return false;
            }
        }
        if (($fieldName = sqlite_field_name($resultQuery, $number)) !== false) {
            return $fieldName;
        }
        throw new KumbiaException($this->error());
    }

    /**
     * Se Mueve al resultado indicado por $number en un select.
     *
     * @param int      $number
     * @param resource $resultQuery
     *
     * @return bool
     */
    public function data_seek($number, $resultQuery = '')
    {
        if (!$resultQuery) {
            $resultQuery = $this->last_result_query;
            if (!$resultQuery) {
                return false;
            }
        }
        if (($success = sqlite_rewind($resultQuery, $number)) !== false) {
            return $success;
        }
        throw new KumbiaException($this->error());
    }

    /**
     * Numero de Filas afectadas en un insert, update o delete.
     *
     * @param resource $resultQuery
     *
     * @return int
     */
    public function affected_rows($resultQuery = '')
    {
        if (!$resultQuery) {
            $resultQuery = $this->last_result_query;
            if (!$resultQuery) {
                return false;
            }
        }
        if (($numberRows = pg_affected_rows($resultQuery)) !== false) {
            return $numberRows;
        }
        throw new KumbiaException($this->error());
    }

    /**
     * Devuelve el error de SQLite.
     *
     * @return string
     */
    public function error($err = '')
    {
        if (!$this->id_connection) {
            $this->last_error = sqlite_last_error($this->id_connection) ? sqlite_last_error($this->id_connection).$err : "[Error Desconocido en SQLite \"$err\"]";
            if ($this->logger) {
                Logger::error($this->last_error);
            }

            return $this->last_error;
        }
        $this->last_error = 'SQLite error: '.sqlite_error_string(sqlite_last_error($this->id_connection));
        $this->last_error .= $err;
        if ($this->logger) {
            Logger::error($this->last_error);
        }

        return $this->last_error;
    }

    /**
     * Devuelve el no error de SQLite.
     *
     * @return int
     */
    public function no_error()
    {
        return 0; //Codigo de Error?
    }

    /**
     * Devuelve el ultimo id autonumerico generado en la BD.
     *
     * @return int
     */
    public function last_insert_id($table = '', $primary_key = '')
    {
        $last_id = $this->fetch_one("SELECT COUNT(*) FROM $table");

        return $last_id[0];
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
        $table = addslashes(strtolower($table));
        if (strpos($table, '.')) {
            list($schema, $table) = explode('.', $table);
        }
        $num = $this->fetch_one("SELECT COUNT(*) FROM sqlite_master WHERE name = '$table'");

        return $num[0];
    }

    /**
     * Devuelve un LIMIT valido para un SELECT del RBDM.
     *
     * @param string $sql consulta sql
     *
     * @return string
     */
    public function limit($sql)
    {
        $params = Util::getParams(func_get_args());

        if (isset($params['limit']) && is_numeric($params['limit'])) {
            $sql .= " LIMIT $params[limit]";
        }

        if (isset($params['offset']) && is_numeric($params['offset'])) {
            $sql .= " OFFSET $params[offset]";
        }

        return $sql;
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
     * @param string $table
     * @param array  $definition
     *
     * @return bool|null
     */
    public function create_table($table, $definition, $index = array())
    {
    }

    /**
     * Listar las tablas en la base de datos.
     *
     * @return array
     */
    public function list_tables()
    {
        return $this->fetch_all("SELECT name FROM sqlite_master WHERE type='table' ".
                'UNION ALL SELECT name FROM sqlite_temp_master '.
                "WHERE type='table' ORDER BY name");
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
        $fields = array();
        $results = $this->fetch_all("PRAGMA table_info($table)");
        //var_dump($results); die();
        foreach ($results as $field) {
            $fields[] = array(
                'Field' => $field['name'],
                'Type' => $field['type'],
                'Null' => $field['notnull'] == '0' ? 'YES' : 'NO',
                'Key' => $field['pk'] == 1 ? 'PRI' : '',
            );
        }

        return $fields;
    }

    /**
     * Devuelve la ultima sentencia sql ejecutada por el Adaptador.
     *
     * @return string
     */
    public function last_sql_query()
    {
        return $this->last_query;
    }
}
