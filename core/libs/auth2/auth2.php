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
 * 
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Clase Base para la gestion de autenticación
 *
 * @category   Kumbia
 * @package    Auth 
 */
abstract class Auth2 {

    /**
     * Mensaje de Error
     *
     * @var String
     */
    protected $_error = '';
    /**
     * Campo de la BD donde se guarda el nombre de usuario
     *
     * @var String
     */
    protected $_login = 'login';
    /**
     * Campo de la BD donde se guarda la clave/pass
     *
     * @var String
     */
    protected $_pass = 'password';
    /**
     * Algoritmo de cifrado de la clave/pass
     *
     * @var String
     */
    protected $_algos = 'md5';
    /**
     * Clave de sesion
     *
     * @var string
     */
    protected $_key = 'jt2D14KIdRs7LA==';
    /**
     * Verificar que no se inicie sesion desde browser distinto con la misma IP
     *
     * @var boolean
     */
    protected $_checkSession = TRUE;
    /**
     * Adaptador por defecto
     *
     * @var string
     */
    protected static $_defaultAdapter = 'model';

    /**
     * Asigna el nombre de campo para el campo de nombre de usuario
     *
     * @param string $field nombre de campo que recibe por POST
     */
    public function setLogin($field) {
        $this->_login = $field;
    }

    /**
     * Asigna el nombre de campo para el campo de clave
     *
     * @param string $field nombre de campo que recibe por POST
     */
    public function setPass($field) {
        $this->_pass = $field;
    }

    /**
     * Asigna la clave de sesion
     *
     * @param string $key clave de sesion
     */
    public function setKey($key) {
        $this->_key = $key;
    }

    /**
     * Realiza el proceso de identificacion.
     *
     * @param $login string Valor opcional del nombre de usuario en la bd
     * @param $pass string Valor opcional de la contraseña del usuario en la bd
     * @param $mode string Valor opcional del método de identificación (auth)
     * @return bool
     */
    public function identify($login = '', $pass = '', $mode = '') {
        if ($this->isValid()) {
            return TRUE;
        } else {
            // check
            if (($mode == 'auth') || (isset($_POST['mode']) && $_POST['mode'] === 'auth')) {
                $login = empty($login)?Input::post($this->_login):$login;
                $pass  = empty($pass)?Input::post($this->_pass):$pass;
                return $this->_check($login, $pass);
            }
            //FAIL
            return false;
        }
    }

    /**
     * Realiza el proceso de autenticacion segun para cada adapter
     *
     * @param $username
     * @param $password
     * @return bool
     */
    abstract protected function _check($username, $password);

    /**
     * logout
     *
     * @param void
     * @return void
     */
    public function logout() {
        Session::set($this->_key, FALSE);
        session_destroy();
    }

    /**
     * Verifica que exista una identidad válida para la session actual
     *
     * @return bool
     */
    public function isValid() {
        if ($this->_checkSession) {
            $this->_checkSession();
        }

        return Session::has($this->_key) && Session::get($this->_key) === TRUE;
    }

    /**
     * Verificar que no se inicie sesion desde browser distinto con la misma IP
     *
     */
    private function _checkSession() {
        Session::set('USERAGENT', $_SERVER['HTTP_USER_AGENT']);
        Session::set('REMOTEADDR', $_SERVER['REMOTE_ADDR']);

        if ($_SERVER['REMOTE_ADDR'] !== Session::get('REMOTEADDR') ||
            $_SERVER['HTTP_USER_AGENT'] !== Session::get('USERAGENT')) {
            session_destroy();
        }
    }

    /**
     * Indica que no se inicie sesion desde browser distinto con la misma IP
     *
     * @param bool $check
     */
    public function setCheckSession($check) {
        $this->_checkSession = $check;
    }

    /**
     * Indica algoritmo de cifrado
     *
     * @param string $algos
     */
    public function setAlgos($algos, $salt = '') {
        $this->_algos = $algos;
    }

    /**
     * Obtiene el mensaje de error
     *
     * @return string
     */
    public function getError() {
        return $this->_error;
    }

    /**
     * Indica el mensaje de error
     *
     * @param string $error
     */
    public function setError($error) {
        $this->_error = $error;
    }

    /**
     * Logger de las operaciones Auth
     * @param $msg
     */
    public static function log($msg) {
        $date = date('Y-m-d', strtotime('now'));
        Logger::custom('AUTH', $msg, "auth-$date.log");
    }

    /**
     * Obtiene el adaptador para Auth
     *
     * @param string $adapter (model, openid, oauth)
     */
    public static function factory($adapter = '') {
        if (!$adapter) {
            $adapter = self::$_defaultAdapter;
        }

        require_once __DIR__ ."/adapters/{$adapter}_auth.php";
        $class = $adapter.'auth';

        return new $class;
    }

    /**
     * Cambia el adaptador por defecto
     *
     * @param string $adapter nombre del adaptador por defecto
     */
    public static function setDefault($adapter) {
        self::$_defaultAdapter = $adapter;
    }

}
