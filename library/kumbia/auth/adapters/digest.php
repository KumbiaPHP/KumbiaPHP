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
 * Esta clase permite autenticar usuarios usando Digest Access Authentication.
 *
 * @category Kumbia
 * @package Auth
 * @subpackage Adapters
 * @copyright Copyright (c) 2005-2007 Andres Felipe Gutierrez (andresfelipe at vagoogle.net)
 * @license http://www.kumbia.org/license.txt GNU/GPL
 * @link http://en.wikipedia.org/wiki/Digest_access_authentication
 */
class DigestAuth implements AuthInterface {

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
	 * Realm encontrado
	 *
	 * @var string
	 */
	private $realm;


	/**
	 * Constructor del adaptador
	 *
	 * @param $auth
	 * @param $extra_args
	 */
	public function __construct($auth, $extra_args){
		foreach(array('filename') as $param){
			if(isset($extra_args[$param])){
				$this->$param = $extra_args[$param];
			} else {
				throw new AuthException("Debe especificar el parametro '$param' en los par&aacute;metros");
			}
		}
		foreach(array('username', 'password') as $param){
			if(isset($extra_args[$param])){
				$this->$param = $extra_args[$param];
			}
		}
	}

	/**
	 * Obtiene los datos de identidad obtenidos al autenticar
	 *
	 */
	public function get_identity(){
		$identity = array("username" => $this->username, "realm" => $this->realm);
		return $identity;
	}

	/**
	 * Autentica un usuario usando el adaptador
	 *
	 * @return boolean
	 */
	public function authenticate(){
		$this->resource = @fopen($this->filename, "r");
		if($this->resource===false){
			throw new AuthException("No existe o no se puede cargar el archivo '{$this->filename}'");
		}

		$exists_user = false;
		while(!feof($this->resource)){
			$line = fgets($this->resource);
			$data = explode(":", $line);

			if($data[0]==$this->username){
				if(trim($data[2])==md5($this->password)){
					$this->realm = $data[1];
					$exists_user = true;
					break;
				}
			}
		}
		return $exists_user;
	}


	/**
	 * Asigna los valores de los parametros al objeto autenticador
	 *
	 * @param array $extra_args
	 */
	public function set_params($extra_args){
		foreach(array('filename', 'username', 'password') as $param){
			if(isset($extra_args[$param])){
				$this->$param = $extra_args[$param];
			}
		}
	}


	/**
	 * Limpia el objeto cerrando la conexion si esta existe
	 *
	 */
	public function __destruct(){
		@fclose($this->resource);
	}

}
