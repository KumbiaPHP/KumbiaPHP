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
 * @subpackage PDO Adapters
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */
/**
 * @see DbPdo Padre de Drivers Pdo
 */
require_once CORE_PATH.'libs/db/adapters/pdo.php';

/**
 * PDO SQLite Database Support.
 *
 * @category   Kumbia
 */
class DbPdoSQLite extends DbPDO
{
    /**
     * Nombre de RBDM.
     */
    protected $db_rbdm = 'sqlite';

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
     * Hace una conexión a la base de datos.
     *
     * @param array $config
     *
     * @return bool
     */
    public function connect(array $config)
    {
        if (!extension_loaded('pdo')) {
            throw new KumbiaException('Debe cargar la extensión de PHP llamada php_pdo');
        }
        try {
            $this->pdo = new PDO($config['type'].':'.APP_PATH.$config['dsn'],null, null,
                array(PDO::ATTR_PERSISTENT => true));

            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
            $this->pdo->setAttribute(PDO::ATTR_CURSOR, PDO::CURSOR_FWDONLY);

            $this->initialize();

            return true;
        } catch (PDOException $e) {
            throw new KumbiaException($this->error($e->getMessage()));
        }
    }

    /**
     * Ejecuta acciones de incializacion del driver.
     */
    public function initialize()
    {
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
        $table = strtolower($table);
        $num = $this->fetch_one("SELECT COUNT(*) FROM sqlite_master WHERE name = '$table'");

        return $num[0];
    }

    /**
     * Crea una tabla utilizando SQL nativo del RDBM.
     *
     * TODO:
     * - Falta que el paraámetro index funcione. Este debe listar indices compuestos multipes y unicos
     * - Agregar el tipo de tabla que debe usarse (MySQL)
     * - Soporte para campos autonuméricos
     * - Soporte para llaves foráneas
     *
     * @param string $table
     * @param array  $definition
     *
     * @return bool
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
            if (isset($field_def['index'])) {
                if ($field_def['index']) {
                    $index[] = "INDEX($field)";
                }
            }
            if (isset($field_def['unique_index'])) {
                if ($field_def['unique_index']) {
                    $index[] = "UNIQUE($field)";
                }
            }
            if (isset($field_def['primary'])) {
                if ($field_def['primary']) {
                    $primary[] = "$field";
                }
            }
            if (isset($field_def['auto'])) {
                if ($field_def['auto']) {
                    $not_null = '';
                }
            }
            if (isset($field_def['extra'])) {
                $extra = $field_def['extra'];
            } else {
                $extra = '';
            }
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

        return $this->exec($create_sql);
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
        $table = $schema ? "$table.$schema" : $table;
        $results = $this->fetch_all("PRAGMA table_info($table)");

        $fields = [];
        foreach ($results as $field) {
            $fields[] = array(
                'Field' => $field['name'],
                'Type' => $field['type'],
                'Null' => $field['notnull'] == 99 ? 'YES' : 'NO',
                'Default' => $field['dflt_value'],
                'Key' => $field['pk'] == 1 ? 'PRI' : '',
            );
        }

        return $fields;
    }
}
