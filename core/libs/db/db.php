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
 * Clase principal que deben heredar todas las clases driver de KumbiaPHP
 * contiene metodos utiles y variables generales
 * 
 * @category   Kumbia
 * @package    Db 
 * @copyright  Copyright (c) 2005-2009 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

/**
 * @see DbBaseInterface
 */
require CORE_PATH . 'libs/db/db_base_interface.php';

/**
 * @see DbLoader
 */
require CORE_PATH . 'libs/db/loader/loader.php';
/**
* @see ActiveRecordBase
*/
require CORE_PATH . 'libs/db/active_record_base/active_record_base.php';

/**
* Carga el modelo base
*/
require APP_PATH . 'model_base.php';		
/**
 * Clase principal que deben heredar todas las clases driver de KumbiaPHP
 * contiene metodos utiles y variables generales
 *
 * $debug : Indica si se muestran por pantalla todas las operaciones sql que se
 * realizen con el driver
 * $logger : Indica si se va a logear a un archivo todas las transacciones que
 * se realizen en en driver. $logger = true crea un archivo con la fecha actual
 * en logs/ y $logger="nombre", crea un log con el nombre indicado
 * $display_errors  : Indica si se muestran los errores sql en Pantalla
 * 
 */
class DbBase 
{
	/**
	 * Indica si esta en modo debug o no
	 *
	 * @var boolean
	 */
	public $debug = false;

	/**
	 * Indica si debe loggear o no (tambien permite establecer el nombre del log)
	 *
	 * @var mixed
	 */
	public $logger = false;

	/**
	 * Singleton de la conexi&oacute;n a la base de datos
	 *
	 * @var resource
	 */
	static private $raw_connection = null;

	/**
	 * Singleton de conexiones abiertas
	 * 
	 * @var array
	 **/
	protected static $raw_connections = array();

	/**
	 * Hace un select de una forma mas corta, listo para usar en un foreach
	 *
	 * @param string $table
	 * @param string $where
	 * @param string $fields
	 * @param string $orderBy
	 * @return array
	 */
	public function find($table, $where="1=1", $fields="*", $orderBy="1"){
		ActiveRecord::sql_item_sanizite($table);
		ActiveRecord::sql_sanizite($fields);
		ActiveRecord::sql_sanizite($orderBy);
		$q = $this->query("SELECT $fields FROM $table WHERE $where ORDER BY $orderBy");
		$results = array();
		while($row=$this->fetch_array($q)){
			$results[] = $row;
		}
		return $results;
	}

	/**
	 * Realiza un query SQL y devuelve un array con los array resultados en forma
	 * indexada por numeros y asociativamente
	 *
	 * @param string $sql
	 * @param integer $type
	 * @return array
	 */
	public function in_query($sql, $type=db::DB_BOTH){
		$q = $this->query($sql);
		$results = array();
		if($q){
			while($row=$this->fetch_array($q, $type)){
				$results[] = $row;
			}
		}
		return $results;
	}

	/**
	 * Realiza un query SQL y devuelve un array con los array resultados en forma
	 * indexada por numeros y asociativamente (Alias para in_query)
	 *
	 * @param string $sql
	 * @param integer $type
	 * @return array
	 */
	public function fetch_all($sql, $type=db::DB_BOTH){
		return $this->in_query($sql, $type);
	}

	/**
	 * Realiza un query SQL y devuelve un array con los array resultados en forma
	 * indexada asociativamente
	 *
	 * @param string $sql
	 * @param integer $type
	 * @return array
	 */
	public function in_query_assoc($sql){
		$q = $this->query($sql);
		$results = array();
		if($q){
			while($row=$this->fetch_array($q, db::DB_ASSOC)){
				$results[] = $row;
			}
		}
		return $results;
	}

	/**
	 * Realiza un query SQL y devuelve un array con los array resultados en forma
	 * numerica
	 *
	 * @param string $sql
	 * @param integer $type
	 * @return array
	 */
	public function in_query_num($sql){
		$q = $this->query($sql);
		$results = array();
		if($q){
			while($row=$this->fetch_array($q, db::DB_NUM)){
				$results[] = $row;
			}
		}
		return $results;
	}

	/**
	 * Devuelve un array del resultado de un select de un solo registro
	 *
	 * @param string $sql
	 * @return array
	 */
	public function fetch_one($sql){
		$q = $this->query($sql);
		if($q){
			if($this->num_rows($q)>1){
				Flash::warning("Una sentencia SQL: \"$sql\" retorno mas de una fila cuando se esperaba una sola");
			}
			return $this->fetch_array($q);
		} else {
			return array();
		}
	}

	/**
	 * Realiza una inserci&oacute;n
	 *
	 * @param string $table
	 * @param array $values
	 * @param array $fields
	 * @return boolean
	 */
	public function insert($table, $values, $fields=null){
		$insert_sql = "";
		if(is_array($values)){
			if(!count($values)){
				new KumbiaException("Imposible realizar inserci&oacute;n en $table sin datos");
			}
			if(is_array($fields)){
				$insert_sql = "INSERT INTO $table (".join(",", $fields).") VALUES (".join(",", $values).")";
			} else {
				$insert_sql = "INSERT INTO $table VALUES (".join(",", $values).")";
			}
			return $this->query($insert_sql);
		} else{
			throw new KumbiaException('El segundo parametro para insert no es un Array');
		}
	}

	/**
	 * Actualiza registros en una tabla
	 *
	 * @param string $table
	 * @param array $fields
	 * @param array $values
	 * @param string $where_condition
	 * @return boolean
	 */
	public function update($table, $fields, $values, $where_condition=null){
		$update_sql = "UPDATE $table SET ";
		if(count($fields)!=count($values)){
			throw new KumbiaException('Los n&uacute;mero de valores a actualizar no es el mismo de los campos');
		}
		$i = 0;
		$update_values = array();
		foreach($fields as $field){
			$update_values[] = $field.' = '.$values[$i];
			$i++;
		}
		$update_sql.= join(',', $update_values);
		if($where_condition!=null){
			$update_sql.= " WHERE $where_condition";
		}
		return $this->query($update_sql);
	}

	/**
	 * Borra registros de una tabla!
	 *
	 * @param string $table
	 * @param string $where_condition
	 */
	public function delete($table, $where_condition){
		if(trim($where_condition)){
			return $this->query("DELETE FROM $table WHERE $where_condition");
		} else {
			return $this->query("DELETE FROM $table");
		}
	}

	/**
	 * Inicia una transacci&oacute;n si es posible
	 *
	 */
	public function begin(){
		return $this->query('BEGIN');
	}


	/**
	 * Cancela una transacci&oacute;n si es posible
	 *
	 */
	public function rollback(){
		return $this->query('ROLLBACK');
	}

	/**
	 * Hace commit sobre una transacci&oacute;n si es posible
	 *
	 */
	public function commit(){
		return $this->query('COMMIT');
	}

	/**
	 * Agrega comillas o simples segun soporte el RBDM
	 *
	 * @return string
	 */
	static public function add_quotes($value){
		return "'".addslashes($value)."'";
	}

	/**
	 * Loggea las operaciones sobre la base de datos si estan habilitadas
	 *
	 * @param string $msg
	 * @param string $type
	 */
	protected function log($msg, $type){
		if($this->logger) {
            Logger::log($this->logger, $msg, $type);
		}
	}

	/**
	 * Muestra Mensajes de Debug en Pantalla si esta habilitado
	 *
	 * @param string $sql
	 */
	protected function debug($sql){
		if($this->debug){
			Flash::notice($sql);
		}
	}

	/**
	 * Realiza una conexión directa al motor de base de datos
	 * usando el driver de Kumbia
	 *
	 * @param boolean $new_connection nueva conexion
	 * @param string $database base de datos a donde conectar
	 * @return db
	 */
	public static function raw_connect($new_connection=false, $database = null){
		/**
		 * Cargo el mode para mi aplicacion
		 **/
		if(!$database) {
			$database = Config::get('config.application.database');
		}
		
		$databases = Config::read('databases');
		$config = $databases[$database];
		
		/**
		 * Cargo valores por defecto para la conexion
		 * en caso de que no existan
		 **/
		if(!isset($config['port'])){
			$config['port'] = 0;
		}
		if(!isset($config['dsn'])){
			$config['dsn'] = '';
		}
		if(!isset($config['host'])){
			$config['host'] = '';
		}
		if(!isset($config['username'])){
			$config['username'] = '';
		}
		if(!isset($config['password'])){
			$config['password'] = '';
		}

		/**
		 * Si no es una conexion nueva y existe la conexion singleton
		 **/
		if(!$new_connection && isset(self::$raw_connections[$database])) {
			return self::$raw_connections[$database];
		}

		/**
		 * Cargo la clase adaptadora necesaria
		 **/
		if(isset($config['pdo'])){
			$dbclass = "DbPDO{$config['type']}";
			if(!class_exists($dbclass)) {
				/**
				 * @see DbPDO
				 */
				require_once CORE_PATH . 'libs/db/adapters/pdo.php';
				require_once CORE_PATH . 'libs/db/adapters/pdo/' . $config['type'] . '.php';
			}
		} else {
			$dbclass = "Db{$config['type']}";
			if(!class_exists($dbclass)) {
				require_once CORE_PATH . 'libs/db/adapters/' . $config['type'] . '.php';
			}
		}
		if(!class_exists($dbclass)){
			throw new KumbiaException("No existe la clase {$dbclass}, necesaria para iniciar el adaptador", 0);
		}
		
		$connection = new $dbclass($config);
		
		/**
		 * Si no es para conexion nueva, la cargo en el singleton
		 **/
		if(!$new_connection) {
			self::$raw_connections[$database] = $connection;
		} 
		
		return $connection;
	}
}
return DbLoader::load_driver();
