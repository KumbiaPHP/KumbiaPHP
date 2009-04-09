<?php
/**
 * Kumbia PHP Framework
 * PHP version 5
 * LICENSE
 *
 * This source file is subject to the GNU/GPL that is bundled
 * with this package in the file docs/LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.kumbiaphp.com/
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to kumbia@kumbiaphp.com so we can send you a copy immediately.
 *
 * @category   Kumbia
 * @package    Db
 * @subpackage Adapters
 * @author     Andres Felipe Gutierrez <andresfelipe@vagoogle.net>
 * @copyright  2008-2008 Emilio Rafael Silveira Tovar <emilio.rst at gmail.com>
 * @copyright  2007-2009 Deivinson Jose Tejeda Brito <deivinsontejeda at gmail.com>
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt GNU/GPL
 * @version    SVN:$id
 * @see        Object
 */

/**
 * PostgreSQL Database Support
 *
 * La base de datos PostgreSQL es un producto Open Source y disponible sin costo.
 * Postgres, desarrollado originalmente en el Deportamento de Ciencias de
 * Computación de UC Berkeley, fue pionero en muchos de los conceptos de
 * objetos y relacionales que ahora están apareciendo en algunas bases de
 * datos comerciales. Provee soporte para lenguajes SQL92/SQL99, transacciones,
 * integridad referencial, procedimientos almacenados y extensibilidad de tipos.
 * PostgreSQL es un descendiente de código abierto original de Berkeley.
 *
 * Estas funciones le permiten acceder a servidores de bases de datos PostgreSQL.
 * Puede encontrar más información sobre PostgreSQL en http://www.postgresql.org.
 * La documentación de PostgreSQL puede encontrarse en http://www.postgresql.org/docs.
 *
 * @category   Kumbia
 * @package    Db
 * @subpackage Adapters
 * @author     Andres Felipe Gutierrez <andresfelipe@vagoogle.net>
 * @copyright  2008-2008 Emilio Rafael Silveira Tovar <emilio.rst at gmail.com>
 * @copyright  2007-2009 Deivinson Jose Tejeda Brito <deivinsontejeda at gmail.com>
 * @license    http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt GNU/GPL
 * @version    SVN:$id
 * @see        Object
 */
class DbPgSQL extends DbBase implements DbBaseInterface {

	/**
	 * Resource de la Conexion a PostgreSQL
	 *
	 * @var resource
	 */
	public $id_connection;

	/**
	 * Ultimo Resultado de una Query
	 *
	 * @var resource
	 */
	public $last_result_query;

	/**
	 * Ultima sentencia SQL enviada a PostgreSQL
	 *
	 * @var string
	 */
	private $last_query;

	/**
	 * Ultimo error generado por PostgreSQL
	 *
	 * @var string
	 */
	public $last_error;

	/**
	 * Resultado de Array Asociativo
	 *
	 */
	const DB_ASSOC = PGSQL_ASSOC;

	/**
	 * Resultado de Array Asociativo y Numerico
	 *
	 */
	const DB_BOTH = PGSQL_BOTH;

	/**
	 * Resultado de Array Numerico
	 *
	 */
	const DB_NUM = PGSQL_NUM;


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
	 * Hace una conexion a la base de datos de PostgreSQL
	 *
	 * @param array $config
	 * @return resource_connection
	 */
	public function connect($config){

		if(!extension_loaded('pgsql')){
			throw new KumbiaException('Debe cargar la extensión de PHP llamada php_pgsql');
			return false;
		}
		
		if(!isset($config['port']) || !$config['port']) {
			$config['port'] = 5432;
		}

		if($this->id_connection = pg_connect("host={$config['host']} user={$config['username']} password={$config['password']} dbname={$config['name']} port={$config['port']}")){
			return true;
		} else {
			throw new KumbiaException($this->error("No se puede conectar a la base de datos"), $this->no_error(), false);
		}
	}

	/**
	 * Efectua operaciones SQL sobre la base de datos
	 *
	 * @param string $sqlQuery
	 * @return resource or false
	 */
	function query($sqlQuery){
		$this->debug($sqlQuery);
        if($this->logger){
            Logger::debug($sqlQuery);
        }
		if(!$this->id_connection){
			$this->connect();
			if(!$this->id_connection){
				return false;
			}
		}
		$this->last_query = $sqlQuery;
		if($resultQuery = @pg_query($this->id_connection, $sqlQuery)){
			$this->last_result_query = $resultQuery;
			return $resultQuery;
		} else {
			throw new KumbiaException($this->error(" al ejecutar <i>'$sqlQuery'</i>"), $this->no_error());
			return false;
		}
	}

	/**
	 * Cierra la Conexión al Motor de Base de datos
	 */
	function close(){
		if($this->id_connection) {
			return pg_close($this->id_connection);
		} else {
			return false;
		}
	}

	/**
	 * Devuelve fila por fila el contenido de un select
	 *
	 * @param resource $resultQuery
	 * @param integer $opt
	 * @return array
	 */
	function fetch_array($resultQuery='', $opt=''){
		if($opt==='') $opt = db::DB_BOTH;
		if(!$this->id_connection){
			return false;
		}
		if(!$resultQuery){
			$resultQuery = $this->last_result_query;
			if(!$resultQuery){
				return false;
			}
		}
		return pg_fetch_array($resultQuery, NULL, $opt);
	}

	/**
	 * Constructor de la Clase
	 *
	 * @param array $config
	 */
	function __construct($config){
		$this->connect($config);
	}

	/**
	 * Devuelve el numero de filas de un select
	 */
	function num_rows($resultQuery=''){
		if(!$this->id_connection){
			return false;
		}
		if(!$resultQuery){
			$resultQuery = $this->last_result_query;
			if(!$resultQuery){
				return false;
			}
		}
		if(($numberRows = pg_num_rows($resultQuery))!==false){
			return $numberRows;
		} else {
			throw new KumbiaException($this->error(), $this->no_error());
			return false;
		}
		return false;
	}

	/**
	 * Devuelve el nombre de un campo en el resultado de un select
	 *
	 * @param integer $number
	 * @param resource $resultQuery
	 * @return string
	 */
	function field_name($number, $resultQuery=''){
		if(!$this->id_connection){
			return false;
		}
		if(!$resultQuery){
			$resultQuery = $this->last_result_query;
			if(!$resultQuery){
				return false;
			}
		}
		if(($fieldName = pg_field_name($resultQuery, $number))!==false){
			return $fieldName;
		} else {
			throw new KumbiaException($this->error(), $this->no_error());
			return false;
		}
		return false;
	}


	/**
	 * Se Mueve al resultado indicado por $number en un select
	 *
	 * @param integer $number
	 * @param resource $resultQuery
	 * @return boolean
	 */
	function data_seek($number, $resultQuery=''){
		if(!$resultQuery){
			$resultQuery = $this->last_result_query;
			if(!$resultQuery){
				return false;
			}
		}
		if(($success = pg_result_seek($resultQuery, $number))!==false){
			return $success;
		} else {
			throw new KumbiaException($this->error(), $this->no_error());
			return false;
		}
		return false;
	}

	/**
	 * Numero de Filas afectadas en un insert, update o delete
	 *
	 * @param resource $resultQuery
	 * @return integer
	 */
	function affected_rows($resultQuery=''){
		if(!$this->id_connection){
			return false;
		}
		if(!$resultQuery){
			$resultQuery = $this->last_result_query;
			if(!$resultQuery){
				return false;
			}
		}
		if(($numberRows = pg_affected_rows($resultQuery))!==false){
			return $numberRows;
		} else {
			throw new KumbiaException($this->error(), $this->no_error());
			return false;
		}
		return false;
	}

	/**
	 * Devuelve el error de PostgreSQL
	 *
	 * @return string
	 */
	function error($err=''){
		if(!$this->id_connection){
		    $this->last_error = @pg_last_error() ? @pg_last_error().$err : "[Error Desconocido en PostgreSQL \"$err\"]";
            if($this->logger){
                Logger::error($this->last_error);
            }
			return $this->last_error;
		}
		$this->last_error = @pg_last_error() ? @pg_last_error().$err : "[Error Desconocido en PostgreSQL: $err]";
		$this->last_error.= $err;
        if($this->logger){
            Logger::error($this->last_error);
        }
		return pg_last_error($this->id_connection).$err;
	}

	/**
	 * Devuelve el no error de PostgreSQL
	 *
	 * @return integer
	 */
	function no_error(){
		if(!$this->id_connection){
			return false;
		}
		return "0"; //Codigo de Error?
	}

	/**
	 * Devuelve el ultimo id autonumerico generado en la BD
	 *
	 * @return integer
	 */
	public function last_insert_id($table='', $primary_key=''){
		if(!$this->id_connection){
			return false;
		}
		$last_id = $this->fetch_one("SELECT CURRVAL('{$table}_{$primary_key}_seq')");
		return $last_id[0];
	}

	/**
	 * Verifica si una tabla existe o no
	 *
	 * @param string $table
	 * @return boolean
	 */
	function table_exists($table, $schema=''){
		$table = addslashes(strtolower($table));
		if(strpos($table, ".")){
			list($schema, $table) = explode(".", $table);
		}
		if($schema==''){
			$num = $this->fetch_one("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'public' AND TABLE_NAME ='$table'");
		} else {
			$schema = addslashes(strtolower($schema));
			$num = $this->fetch_one("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = '$schema' AND TABLE_NAME ='$table'");
		}
		return $num[0];
	}

	/**
	 * Devuelve un LIMIT valido para un SELECT del RBDM
	 *
	 * @param string $sql consulta sql
	 * @return string
	 */
	public function limit($sql){
		$params = get_params(func_get_args());
		$sql_new = $sql;
	
		if(isset($params['limit']) && is_numeric($params['limit'])){
			$sql_new.=" LIMIT $params[limit]";
		}
		
		if(isset($params['offset']) && is_numeric($params['offset'])){
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
	public function drop_table($table, $if_exists=true){
		if($if_exists){
			if($this->table_exists($table)){
				return $this->query("DROP TABLE $table");
			} else {
				return true;
			}
		} else {
			return $this->query("DROP TABLE $table");
		}
	}

	/**
	 * Crea una tabla utilizando SQL nativo del RDBM
	 *
	 * TODO:
	 * - Falta que el parametro index funcione. Este debe listar indices compuestos multipes y unicos
	 * - Agregar el tipo de tabla que debe usarse (PostgreSQL)
	 * - Soporte para campos autonumericos
	 * - Soporte para llaves foraneas
	 *
	 * @param string $table
	 * @param array $definition
	 * @return boolean
	 */
	public function create_table($table, $definition, $index=array()){
		$create_sql = "CREATE TABLE $table (";
		if(!is_array($definition)){
			new KumbiaException("Definici&oacute;n invalida para crear la tabla '$table'");
			return false;
		}
		$create_lines = array();
		$index = array();
		$unique_index = array();
		$primary = array();
		$not_null = "";
		$size = "";
		foreach($definition as $field => $field_def){
			if(isset($field_def['not_null'])){
				$not_null = $field_def['not_null'] ? 'NOT NULL' : '';
			} else {
				$not_null = "";
			}
			if(isset($field_def['size'])){
				$size = $field_def['size'] ? '('.$field_def['size'].')' : '';
			} else {
				$size = "";
			}
			if(isset($field_def['index'])){
				if($field_def['index']){
					$index[] = "INDEX($field)";
				}
			}
			if(isset($field_def['unique_index'])){
				if($field_def['unique_index']){
					$index[] = "UNIQUE($field)";
				}
			}
			if(isset($field_def['primary'])){
				if($field_def['primary']){
					$primary[] = "$field";
				}
			}
			if(isset($field_def['auto'])){
				if($field_def['auto']){
					$field_def['type'] = "SERIAL";
				}
			}
			if(isset($field_def['extra'])){
				$extra = $field_def['extra'];
			} else {
				$extra = "";
			}
			$create_lines[] = "$field ".$field_def['type'].$size.' '.$not_null.' '.$extra;
		}
		$create_sql.= join(',', $create_lines);
		$last_lines = array();
		if(count($primary)){
			$last_lines[] = 'PRIMARY KEY('.join(",", $primary).')';
		}
		if(count($index)){
			$last_lines[] = join(',', $index);
		}
		if(count($unique_index)){
			$last_lines[] = join(',', $unique_index);
		}
		if(count($last_lines)){
			$create_sql.= ','.join(',', $last_lines).')';
		}
		return $this->query($create_sql);

	}

	/**
	 * Listar las tablas en la base de datos
	 *
	 * @return array
	 */
	public function list_tables(){
		return $this->fetch_all("SELECT c.relname AS table FROM pg_class c, pg_user u "
             ."WHERE c.relowner = u.usesysid AND c.relkind = 'r' "
             ."AND NOT EXISTS (SELECT 1 FROM pg_views WHERE viewname = c.relname) "
             ."AND c.relname !~ '^(pg_|sql_)' UNION "
             ."SELECT c.relname AS table_name FROM pg_class c "
             ."WHERE c.relkind = 'r' "
             ."AND NOT EXISTS (SELECT 1 FROM pg_views WHERE viewname = c.relname) "
             ."AND NOT EXISTS (SELECT 1 FROM pg_user WHERE usesysid = c.relowner) "
             ."AND c.relname !~ '^pg_'");
	}

	/**
	 * Listar los campos de una tabla
	 *
	 * @param string $table
	 * @return array
	 */
	public function describe_table($table, $schema=''){
		$describe = $this->fetch_all("SELECT a.attname AS Field, t.typname AS Type,
			 	CASE WHEN attnotnull=false THEN 'YES' ELSE 'NO' END AS Null,
			 	CASE WHEN (select cc.contype FROM pg_catalog.pg_constraint cc WHERE
			 	cc.conrelid = c.oid AND cc.conkey[1] = a.attnum limit 1)='p' THEN 'PRI' ELSE ''
			 	END AS Key FROM pg_catalog.pg_class c, pg_catalog.pg_attribute a,
			 	pg_catalog.pg_type t WHERE c.relname = '$table' AND c.oid = a.attrelid
			 	AND a.attnum > 0 AND t.oid = a.atttypid order by a.attnum");
		$final_describe = array();
		foreach($describe as $key => $value){
			$final_describe[] = array(
				"Field" => $value["field"],
				"Type" => $value["type"],
				"Null" => $value["null"],
				"Key" => $value["key"]
			);
		}
		return $final_describe;
	}
}