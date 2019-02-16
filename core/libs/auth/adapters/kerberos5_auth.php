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
 * @package    Auth
 * @subpackage Adapters
 * 
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Esta clase permite autenticar usuarios usando servidores Kerberos V.
 *
 * @category Kumbia
 * @package Auth
 * @subpackage Adapters
 * @link http://web.mit.edu/kerberos/www/krb5-1.2/krb5-1.2.8/doc/admin_toc.html.
 */
class Kerberos5Auth implements AuthInterface
{

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
     * Nombre de usuario para conectar al servidor de autenticación (si es utilizado)
     *
     * @var string
     */
    private $username;
    /**
     * Password de usuario para conectar al servidor de autenticación (si es utilizado)
     *
     * @var string
     */
    private $password;
    /**
     * Resource Kerberos5
     */
    private $resource;
    /**
     * Realm para autentificar
     *
     * @var string
     */
    private $realm;
    /**
     * El principal
     *
     * @var string
     */
    private $principal;

    /**
     * Constructor del adaptador
     *
     * @param $auth
     * @param $extra_args
     */
    public function __construct($auth, $extra_args)
    {

        if (!extension_loaded("kadm5")) {
            throw new KumbiaException("Debe cargar la extensión de php llamada kadm5");
        }

        foreach (array('server', 'username', 'principal', 'password') as $param) {
            if (isset($extra_args[$param])) {
                $this->$param = $extra_args[$param];
            } else {
                throw new KumbiaException("Debe especificar el parámetro '$param'");
            }
        }
    }

    /**
     * Obtiene los datos de identidad obtenidos al autenticar
     *
     */
    public function get_identity()
    {
        if (!$this->resource) {
            throw new KumbiaException("La conexión al servidor kerberos5 es inválida");
        }
        $identity = array("username" => $this->username, "realm" => $this->username);
        return $identity;
    }

    /**
     * Autentica un usuario usando el adaptador
     *
     * @return boolean
     */
    public function authenticate()
    {
        $this->resource = kadm5_init_with_password($this->server, $this->realm, $this->principal, $this->password);
        if ($this->resource === false) {
            return false;
        }
        return true;
    }

    /**
     * Obtiene los prinicipals del usuario autenticado
     *
     */
    public function get_principals()
    {
        if (!$this->resource) {
            throw new KumbiaException("La conexión al servidor kerberos5 es inválida");
        }
        return kadm5_get_principals($this->resource);
    }

    /**
     * Obtiene los policies del usuario autenticado
     *
     */
    public function get_policies()
    {
        if (!$this->resource) {
            throw new KumbiaException("La conexión al servidor kerberos5 es inválida");
        }
        return kadm5_get_policies($this->resource);
    }

    /**
     * Limpia el objeto cerrando la conexion si esta existe
     *
     */
    public function __destruct()
    {
        if ($this->resource) {
            kadm5_destroy($this->resource);
        }
    }

    /**
     * Asigna los valores de los parametros al objeto autenticador
     *
     * @param array $extra_args
     */
    public function set_params($extra_args)
    {
        foreach (array('server', 'principal', 'username', 'password') as $param) {
            if (isset($extra_args[$param])) {
                $this->$param = $extra_args[$param];
            }
        }
    }

}
