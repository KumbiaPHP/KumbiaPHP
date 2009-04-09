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
 * Esta clase permite autenticar usuarios usando servidores Kerberos V.
 *
 * @category Kumbia
 * @package Auth
 * @subpackage Adapters
 * @copyright Copyright (c) 2005-2007 Andres Felipe Gutierrez (andresfelipe at vagoogle.net)
 * @license http://www.kumbia.org/license.txt GNU/GPL
 * @link http://web.mit.edu/kerberos/www/krb5-1.2/krb5-1.2.8/doc/admin_toc.html.
 */
class Kerberos5Auth implements AuthInterface {

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
	 * Resource Kerberos5
	 */
	private $resource;

	/**
	 * Constructor del adaptador
	 *
	 * @param $auth
	 * @param $extra_args
	 */
	public function __construct($auth, $extra_args){

		if(!extension_loaded("kadm5")){
			throw new AuthException("Debe cargar la extensi&oacute;n de php llamada kadm5");
		}

		foreach(array('server', 'username', 'principal', 'password') as $param){
			if(isset($extra_args[$param])){
				$this->$param = $extra_args[$param];
			} else {
				throw new AuthException("Debe especificar el parametro '$param' en los par&aacute;metros");
			}
		}
	}

	/**
	 * Obtiene los datos de identidad obtenidos al autenticar
	 *
	 */
	public function get_identity(){
		if(!$this->resource){
			new AuthException("La conexion al servidor kerberos5 es invalida");
		}
		$identity = array("username" => $this->username, "realm" => $this->username);
		return $identity;
	}

	/**
	 * Autentica un usuario usando el adaptador
	 *
	 * @return boolean
	 */
	public function authenticate(){
		$this->resource = kadm5_init_with_password($this->server, $this->realm, $this->principal, $this->password);
		if($this->resource===false){
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Obtiene los prinicipals del usuario autenticado
	 *
	 */
	public function get_principals(){
		if(!$this->resource){
			new AuthException("La conexion al servidor kerberos5 es invalida");
		}
		return kadm5_get_principals($this->resource);
	}

	/**
	 * Obtiene los policies del usuario autenticado
	 *
	 */
	public function get_policies(){
		if(!$this->resource){
			new AuthException("La conexion al servidor kerberos5 es invalida");
		}
		return kadm5_get_policies($this->resource);
	}

	/**
	 * Limpia el objeto cerrando la conexion si esta existe
	 *
	 */
	public function __destruct(){
		if($this->resource){
			kadm5_destroy($this->resource);
		}
	}

	/**
	 * Asigna los valores de los parametros al objeto autenticador
	 *
	 * @param array $extra_args
	 */
	public function set_params($extra_args){
		foreach(array('server', 'principal', 'username', 'password') as $param){
			if(isset($extra_args[$param])){
				$this->$param = $extra_args[$param];
			}
		}
	}

}
