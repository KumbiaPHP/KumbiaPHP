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
 * @subpackage Adapters
 * @copyright  Copyright (c) 2005 - 2016 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

/**
 * MySQL Database Support
 *
 * @category   Kumbia
 * @package    Db
 * @subpackage Adapters
 */
class DbMySQLOld extends DbBase implements DbBaseInterface
{

    /**
     * Resource de la Conexión a MySQL
     *
     * @var resource
     */
    public $id_connection;
    /**
     * Último Resultado de una Query
     *
     * @var resource
     */
    public $last_result_query;
    /**
     * Última sentencia SQL enviada a MySQL
     *
     * @var string
     */
    protected $last_query;
    /**
     * Último error generado por MySQL
     *
     * @var string
     */
    public $last_error;

    /**
     * Resultado de Array Asociativo
     *
     */
    const DB_ASSOC = MYSQL_ASSOC;

    /**
     * Resultado de Array Asociativo y Númerico
     *
     */
    const DB_BOTH = MYSQL_BOTH;

    /**
     * Resultado de Array Númerico
     *
     */
    const DB_NUM = MYSQL_NUM;

    /**
     * Tipo de Dato Integer
     *
     */
    const TYPE_INTEGER = 'INTEGER';

    /**
     * Tipo de Dato Date
     *
     */
    const TYPE_DATE = 'DATE';

    /**
     * Tipo de Dato Varchar
     *
     */
    const TYPE_VARCHAR = 'VARCHAR';

    /**
     * Tipo de Dato Decimal
     *
     */
    const TYPE_DECIMAL = 'DECIMAL';

    /**
     * Tipo de Dato Datetime
     *
     */
    const TYPE_DATETIME = 'DATETIME';

    /**
     * Tipo de Dato Char
     *
     */
    const TYPE_CHAR = 'CHAR';

    /**
     * Hace una conexión a la base de datos de MySQL
     *
     * @param array $config
     * @return bool
     */
    public function connect($config)
    {

        if (!extension_loaded('mysql')) {
            throw new KumbiaException('Debe cargar la extensión de PHP llamada php_mysql');
        }
        if (!isset($config['port']) || !$config['port']) $config['port'] = 3306;

        if ($this->id_connection = mysql_connect("{$config['host']}:{$config['port']}", $config['username'], $config['password'], true)) {
            if ($config['name'] !== '') {
                if (!mysql_select_db($config['name'], $this->id_connection)) {
                    throw new KumbiaException($this->error());
                }
            }
            //Selecciona charset
            if (isset($config['charset'])) mysql_query("SET NAMES {$config['charset']}");
            //mysql_set_charset($config['charset'],$this->id_connection); //Necesita mysql > 5.0.7
            return true;
        } else {
            throw new KumbiaException($this->error());
        }
    }

    /**
     * Efectua operaciones SQL sobre la base de datos
     *
     * @param string $sql_query
     * @return resource or false
     */
    public function query($sql_query)
    {
        $this->debug($sql_query);
        if ($this->logger) {
            Logger::debug($sql_query);
        }

        $this->last_query = $sql_query;
        if ($result_query = mysql_query($sql_query, $this->id_connection)) {
            $this->last_result_query = $result_query;
            return $result_query;
        } else {
            throw new KumbiaException($this->error(" al ejecutar <em>\"$sql_query\"</em>"));
        }
    }

    /**
     * Cierra la Conexión al Motor de Base de datos
     */
    public function close()
    {
        if ($this->id_connection) {
            return mysql_close();
        }
        return false;
    }

    /**
     * Devuelve fila por fila el contenido de un select
     *
     * @param resource $result_query
     * @param int $opt
     * @return array
     */
    public function fetch_array($result_query='', $opt=MYSQL_BOTH)
    {

        if (!$result_query) {
            $result_query = $this->last_result_query;
            if (!$result_query) {
                return false;
            }
        }
        return mysql_fetch_array($result_query, $opt);
    }

    /**
     * Constructor de la Clase
     *
     * @param array $config
     */
    public function __construct($config)
    {
        $this->connect($config);
    }

    /**
     * Devuelve el numero de filas de un select
     */
    public function num_rows($result_query='')
    {

        if (!$result_query) {
            $result_query = $this->last_result_query;
            if (!$result_query) {
                return false;
            }
        }
        if (($number_rows = mysql_num_rows($result_query)) !== false) {
            return $number_rows;
        } else {
            throw new KumbiaException($this->error());
        }
    }

    /**
     * Devuelve el nombre de un campo en el resultado de un select
     *
     * @param int $number
     * @param resource $result_query
     * @return string
     */
    public function field_name($number, $result_query='')
    {

        if (!$result_query) {
            $result_query = $this->last_result_query;
            if (!$result_query) {
                return false;
            }
        }
        if (($fieldName = mysql_field_name($result_query, $number)) !== false) {
            return $fieldName;
        } else {
            throw new KumbiaException($this->error());
        }
    }

    /**
     * Se Mueve al resultado indicado por $number en un select
     *
     * @param int $number
     * @param resource $result_query
     * @return boolean
     */
    public function data_seek($number, $result_query='')
    {
        if (!$result_query) {
            $result_query = $this->last_result_query;
            if (!$result_query) {
                return false;
            }
        }
        if (($success = mysql_data_seek($result_query, $number)) !== false) {
            return $success;
        } else {
            throw new KumbiaException($this->error());
        }
    }

    /**
     * Numero de Filas afectadas en un insert, update o delete
     *
     * @param resource $result_query
     * @return int
     */
    public function affected_rows($result_query='')
    {
        if (($numberRows = mysql_affected_rows()) !== false) {
            return $numberRows;
        } else {
            throw new KumbiaException($this->error());
        }
    }

    /**
     * Devuelve el error de MySQL
     *
     * @return string
     */
    public function error($err='')
    {
        if (!$this->id_connection) {
            $this->last_error = mysql_error() ? mysql_error() : "[Error Desconocido en MySQL: $err]";
            if ($this->logger) {
                Logger::error($this->last_error);
            }
            return $this->last_error;
        }
        $this->last_error = mysql_error() ? mysql_error() : "[Error Desconocido en MySQL: $err]";
        $this->last_error.= $err;
        if ($this->logger) {
            Logger::error($this->last_error);
        }
        return $this->last_error;
    }

    /**
     * Devuelve el no error de MySQL
     *
     * @return int
     */
    public function no_error()
    {
        return mysql_errno();
    }

    /**
     * Devuelve el ultimo id autonumerico generado en la BD
     *
     * @return int
     */
    public function last_insert_id($table='', $primary_key='')
    {
        return mysql_insert_id($this->id_connection);
    }

    /**
     * Verifica si una tabla existe o no
     *
     * @param string $table
     * @return boolean
     */
    public function table_exists($table, $schema='')
    {
        $table = addslashes("$table");
        if ($schema == '') {
            $num = $this->fetch_one("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = '$table'");
        } else {
            $schema = addslashes("$schema");
            $num = $this->fetch_one("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = '$table' AND TABLE_SCHEMA = '$schema'");
        }
        return $num[0];
    }

    /**
     * Devuelve un LIMIT valido para un SELECT del RBDM
     *
     * @param string $sql consulta sql
     * @return string
     */
    public function limit($sql)
    {
        $params = Util::getParams(func_get_args());
        $sql_new = $sql;

        if (isset($params['limit']) && is_numeric($params['limit'])) {
            $sql_new.=" LIMIT $params[limit]";
        }

        if (isset($params['offset']) && is_numeric($params['offset'])) {
            $sql_new.=" OFFSET $params[offset]";
        }

        return $sql_new;
    }

    /**
     * Borra una tabla de la base de datos
     *
     * @param string $table
     * @return boolean
     */
    public function drop_table($table, $if_exists=true)
    {
        if ($if_exists) {
            return $this->query("DROP TABLE IF EXISTS $table");
        } else {
            return $this->query("DROP TABLE $table");
        }
    }

    /**
     * Crea una tabla utilizando SQL nativo del RDBM
     *
     * TODO:
     * - Falta que el parametro index funcione. Este debe listar indices compuestos multipes y unicos
     * - Agregar el tipo de tabla que debe usarse (MySQL)
     * - Soporte para campos autonumericos
     * - Soporte para llaves foraneas
     *
     * @param string $table
     * @param array $definition
     * @return boolean
     */
    public function create_table($table, $definition, $index=array())
    {
        $create_sql = "CREATE TABLE $table (";
        if (!is_array($definition)) {
            throw new KumbiaException("Definición invalida para crear la tabla '$table'");
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
                $not_null = "";
            }
            if (isset($field_def['size'])) {
                $size = $field_def['size'] ? '(' . $field_def['size'] . ')' : '';
            } else {
                $size = "";
            }
            if (isset($field_def['index'])) {
                if ($field_def['index']) {
                    $index[] = "INDEX(`$field`)";
                }
            }
            if (isset($field_def['unique_index'])) {
                if ($field_def['unique_index']) {
                    $index[] = "UNIQUE(`$field`)";
                }
            }
            if (isset($field_def['primary'])) {
                if ($field_def['primary']) {
                    $primary[] = "`$field`";
                }
            }
            if (isset($field_def['auto'])) {
                if ($field_def['auto']) {
                    $field_def['extra'] = isset($field_def['extra']) ? $field_def['extra'] . " AUTO_INCREMENT" : "AUTO_INCREMENT";
                }
            }
            if (isset($field_def['extra'])) {
                $extra = $field_def['extra'];
            } else {
                $extra = "";
            }
            $create_lines[] = "`$field` " . $field_def['type'] . $size . ' ' . $not_null . ' ' . $extra;
        }
        $create_sql.= join(',', $create_lines);
        $last_lines = array();
        if (count($primary)) {
            $last_lines[] = 'PRIMARY KEY(' . join(",", $primary) . ')';
        }
        if (count($index)) {
            $last_lines[] = join(',', $index);
        }
        if (count($unique_index)) {
            $last_lines[] = join(',', $unique_index);
        }
        if (count($last_lines)) {
            $create_sql.= ',' . join(',', $last_lines) . ')';
        }
        return $this->query($create_sql);
    }

    /**
     * Listar las tablas en la base de datos
     *
     * @return array
     */
    public function list_tables()
    {
        return $this->fetch_all("SHOW TABLES");
    }

    /**
     * Listar los campos de una tabla
     *
     * @param string $table
     * @return array
     */
    public function describe_table($table, $schema='')
    {
        if ($schema == '') {
            return $this->fetch_all("DESCRIBE `$table`");
        } else {
            return $this->fetch_all("DESCRIBE `$schema`.`$table`");
        }
    }

    /**
     * Devuelve fila por fila el contenido de un select
     *
     * @param resource $result_query
     * @param string $class clase de objeto
     * @return object
     */
    public function fetch_object($result_query=null, $class='stdClass')
    {
        if (!$result_query) {
            $result_query = $this->last_result_query;
        }
        return mysql_fetch_object($result_query, $class);
    }

    /**
     * Devuelve la ultima sentencia sql ejecutada por el Adaptador
     *
     * @return string
     */
    public function last_sql_query()
    {
        return $this->last_query;
    }

}
