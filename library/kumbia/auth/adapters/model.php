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
 * @package Auth
 * @subpackage Adapters
 * @copyright Copyright (c) 2005-2007 Andres Felipe Gutierrez (andresfelipe at vagoogle.net)
 * @license http://www.kumbia.org/license.txt GNU/GPL
 */

/**
 * Esta clase permite autenticar usuarios usando una entidad de la base de datos
 *
 * @category Kumbia
 * @package Auth
 * @subpackage Adapters
 * @copyright Copyright (c) 2005-2007 Andres Felipe Gutierrez (andresfelipe at vagoogle.net)
 * @license http://www.kumbia.org/license.txt GNU/GPL
 * @link http://web.mit.edu/kerberos/www/krb5-1.2/krb5-1.2.8/doc/admin_toc.html.
 */
class ModelAuth implements AuthInterface {

	/**
	 * Nombre del archivo (si es utilizado)
	 *
	 * @var string
	 */
	private $filename;

	/**
	 * Servidor de autenticación (si es utilizado)
	 *
	 * @var string
	 */
	private $server;

	/**
	 * Nombre de usuario para conectar al servidor de autenticacion (si es utilizado)
	 *
	 * @var string
	 */
	private $username;

	/**
	 * Password de usuario para conectar al servidor de autenticacion (si es utilizado)
	 *
	 * @var string
	 */
	private $password;

	/**
	 * Atributos del modelo a comparar para autenticacion valida
	 */
	private $compare_attributes = array();

	/**
	 * Identidad encontrara
	 */
	private $identity = array();

	/**
	 * Constructor del adaptador
	 *
	 * @param $auth
	 * @param $extra_args
	 */
	public function __construct($auth, $extra_args){

		foreach(array('class') as $param){
			if(isset($extra_args[$param])){
				$this->$param = $extra_args[$param];
			} else {
				throw new AuthException("Debe especificar el parametro '$param' en los par&aacute;metros");
			}
		}

		if(!isset(Kumbia::$models[$extra_args['class']])){
			throw new AuthException("No existe el modelo '{$extra_args['class']}' para realizar la autenticaci&oacute;n");
		}
		unset($extra_args[0]);
		unset($extra_args['class']);
		$this->compare_attributes = $extra_args;

	}

	/**
	 * Obtiene los datos de identidad obtenidos al autenticar
	 *
	 */
	public function get_identity(){
		return $this->identity;
	}

	/**
	 * Autentica un usuario usando el adaptador
	 *
	 * @return boolean
	 */
	public function authenticate(){

		$where_condition = array();
		foreach($this->compare_attributes as $field => $value){
			$value = addslashes($value);
			$where_condition[] = "$field = '$value'";
		}
		$result = Kumbia::$models[$this->class]->count(join(" AND ", $where_condition));
		if($result){
			$model = Kumbia::$models[$this->class]->find_first(join(" AND ", $where_condition));
			$identity = array();
			foreach($model->fields as $field){
				/**
				 * Trata de no incluir en la identidad el password del usuario
				 */
				if(!in_array($field, array("password", "clave", "contrasena", "passwd", "pass"))){
					$identity[$field] = $model->$field;
				}
			}
			$this->identity = $identity;
		}
		return $result;

	}

	/**
	 * Asigna los valores de los parametros al objeto autenticador
	 *
	 * @param array $extra_args
	 */
	public function set_params($extra_args){
		foreach(array('server', 'secret', 'principal', 'password', 'port', 'max_retries') as $param){
			if(isset($extra_args[$param])){
				$this->$param = $extra_args[$param];
			}
		}
	}

}
