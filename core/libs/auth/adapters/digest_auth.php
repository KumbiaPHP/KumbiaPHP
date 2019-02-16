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
 * Esta clase permite autenticar usuarios usando Digest Access Authentication.
 *
 * @category Kumbia
 * @package Auth
 * @subpackage Adapters
 * @link http://en.wikipedia.org/wiki/Digest_access_authentication
 */
class DigestAuth implements AuthInterface
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
     * Realm encontrado
     *
     * @var string
     */
    private $realm;
    /**
     * Resource
     *
     * @var string
     */
    private $resource;

    /**
     * Constructor del adaptador
     *
     * @param $auth
     * @param $extra_args
     */
    public function __construct($auth, $extra_args)
    {
        foreach (array('filename') as $param) {
            if (isset($extra_args[$param])) {
                $this->$param = $extra_args[$param];
            } else {
                throw new KumbiaException("Debe especificar el parámetro '$param'.");
            }
        }
        foreach (array('username', 'password') as $param) {
            if (isset($extra_args[$param])) {
                $this->$param = $extra_args[$param];
            }
        }
    }

    /**
     * Obtiene los datos de identidad obtenidos al autenticar
     *
     */
    public function get_identity()
    {
        return array("username" => $this->username, "realm" => $this->realm);
    }

    /**
     * Autentica un usuario usando el adaptador
     *
     * @return boolean
     */
    public function authenticate()
    {
        $this->resource = @fopen($this->filename, "r");
        if ($this->resource === false) {
            throw new KumbiaException("No existe o no se puede cargar el archivo '{$this->filename}'");
        }

        $exists_user = false;
        while (!feof($this->resource)) {
            $line = fgets($this->resource);
            $data = explode(":", $line);

            if ($data[0] === $this->username) {
                if (trim($data[2]) === md5($this->password)) {
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
    public function set_params($extra_args)
    {
        foreach (array('filename', 'username', 'password') as $param) {
            if (isset($extra_args[$param])) {
                $this->$param = $extra_args[$param];
            }
        }
    }

    /**
     * Limpia el objeto cerrando la conexion si esta existe
     *
     */
    public function __destruct()
    {
        @fclose($this->resource);
    }

}
