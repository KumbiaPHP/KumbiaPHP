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
 * @see AuthInterface
 */
require_once __DIR__.'/auth_interface.php';

// Evita problemas al actualizar de la beta2
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

/**
 * Esta clase permite autenticar usuarios.
 *
 * @category   Kumbia
 * @package    Auth 
 */
class Auth
{
    /**
     * Nombres para crear las sessiones.
     *
     */
    const IDENTITY = 'KUMBIA_AUTH_IDENTITY';
    const VALID = 'KUMBIA_AUTH_VALID';
    /**
     * Nombre del adaptador usado para autenticar.
     *
     * @var string
     */
    private $adapter;
    /**
     * Objeto Adaptador actual.
     *
     * @var mixed
     */
    private $adapter_object;
    /**
     * Indica si un usuario debe loguearse sólo una vez en el sistema desde
     * cualquier parte.
     *
     * @var bool
     */
    private $active_session = false;
    /**
     * Tiempo en que expirará la sesion en caso de que no se termine con destroy_active_session.
     *
     * @var int
     */
    private $expire_time = 3600;
    /**
     * Argumentos extra enviados al Adaptador.
     *
     * @var array
     */
    private $extra_args = array();
    /**
     * Usar la misma sesion para las applicaciones con el mismo namespace en config.
     * de config.application.namespace_auth
     * 
     * @var string  
     */
    private static $app_namespace;
    /**
     * Indica si la última llamada a authenticate tuvo éxito o no (persistente en sesion).
     *
     * @var bool|null
     */
    private static $is_valid = null;

    /**
     * Última identidad obtenida por Authenticate (persistente en sesion).
     *
     * @var array
     */
    private static $active_identity = array();

    /**
     * Constructor del Autenticador.
     */
    public function __construct()
    {
        $adapter = 'model'; //default
        $extra_args = Util::getParams(func_get_args());
        if (isset($extra_args[0])) {
            $adapter = $extra_args[0];
            unset($extra_args[0]);
        }
        $this->set_adapter($adapter, $this, $extra_args);
        self::$app_namespace = Config::get('config.application.namespace_auth');
    }

    /**
     * Modifica el adaptador a usar.
     * 
     * @param string $adapter Tipo de adaptador a usar ('digest', 'model', 'kerberos5', 'radius')
     * @param Auth $auth Instancia de la clase Auth
     * @param array $extra_args Argumentos adicionales
     * @throws kumbiaException
     */
    public function set_adapter($adapter, $auth = '', $extra_args = array())
    {
        if (!in_array($adapter, array('digest', 'model', 'kerberos5', 'radius'))) {
            throw new kumbiaException("Adaptador de autenticación '$adapter' no soportado");
        }
        $this->adapter = Util::camelcase($adapter);
        require_once __DIR__."/adapters/{$adapter}_auth.php";
        $adapter_class = $this->adapter.'Auth';
        $this->extra_args = $extra_args;
        $this->adapter_object = new $adapter_class($auth, $extra_args);
    }

    /**
     * Obtiene el nombre del adaptador actual.
     *
     * @return string
     */
    public function get_adapter_name()
    {
        return $this->adapter;
    }

    /**
     * Realiza el proceso de autenticación.
     *
     * @return array|bool
     */
    public function authenticate()
    {
        $result = $this->adapter_object->authenticate();
        /*
         * Si es una sesion activa maneja un archivo persistente para control
         */
        if ($result && $this->active_session) {
            $this->active_session();
        }
        $_SESSION[self::IDENTITY][self::$app_namespace] = $this->adapter_object->get_identity();
        self::$active_identity = $this->adapter_object->get_identity();
        $_SESSION[self::VALID][self::$app_namespace] = $result;
        self::$is_valid = $result;

        return $result;
    }
    /**
     * Si es una sesión activa maneja un archivo persistente para control.
     * 
     * TODO usar sqlite
     */
    private function active_session()
    {
            $user_hash = md5(serialize($this->extra_args));
            $filename = APP_PATH.'temp/cache/'.base64_encode('auth');
            if (file_exists($filename)) {
                $fp = fopen($filename, 'r');
                while (!feof($fp)) {
                    $line = fgets($fp);
                    $user = explode(':', $line);
                if ($user_hash === $user[0]) {
                        if ($user[1] + $user[2] > time()) {
                            self::$active_identity = array();
                            self::$is_valid = false;

                            return false;
                    }

                            fclose($fp);
                            $this->destroy_active_session();
                            file_put_contents($filename, $user_hash.':'.time().':'.$this->expire_time."\n");
                        }
                    }

                fclose($fp);
                $fp = fopen($filename, 'a');
                fputs($fp, $user_hash.':'.time().':'.$this->expire_time."\n");
                fclose($fp);
            
            return;
        }

        file_put_contents($filename, $user_hash.':'.time().':'.$this->expire_time."\n");
    }
    /**
     * Realiza el proceso de autenticación usando HTTP.
     *
     * @return array
     */
    public function authenticate_with_http()
    {
        if (!$_SERVER['PHP_AUTH_USER']) {
            header('WWW-Authenticate: Basic realm="basic"');
            http_response_code(401);

            return false;
        }
        $options = array('username' => $_SERVER['PHP_AUTH_USER'], 'password' => $_SERVER['PHP_AUTH_PW']);
        $this->adapter_object->set_params($options);

        return $this->authenticate();
    }

    /**
     * Devuelve la identidad encontrada en caso de exito.
     *
     * @return array
     */
    public function get_identity()
    {
        return $this->adapter_object->get_identity();
    }

    /**
     * Permite controlar que usuario no se loguee más de una vez en el 
     * sistema desde cualquier parte.
     * 
     * @param bool $value En true para activar la validación
     * @param int $time Tiempo en el que expirará la sesión
     */
    public function set_active_session($value, $time = 3600)
    {
        $this->active_session = $value;
        $this->expire_time = $time;
    }

    /**
     * Permite destruir sesion activa del usuario autenticado.
     */
    public function destroy_active_session()
    {
        $user_hash = md5(serialize($this->extra_args));
        $filename = APP_PATH.'temp/cache/'.base64_encode('auth');
        $lines = file($filename);
        $lines_out = array();
        foreach ($lines as $line) {
            if (substr($line, 0, 32) !== $user_hash) {
                $lines_out[] = $line;
            }
        }
        file_put_contents($filename, join("\n", $lines_out));
    }

    /**
     * Devuelve la instancia del adaptador.
     *
     * @return mixed Objeto Adaptador actual.
     */
    public function get_adapter_instance()
    {
        return $this->adapter_object;
    }

    /**
     * Determinar si debe dormir la aplicación cuando falle la autenticación y cuanto tiempo en segundos.
     *
     * @param bool $value
     * @param int  $time
     * 
     * @deprecated se mantiene para no romper apps
     */
    public function sleep_on_fail($value, $time = 2)
    {
        throw new KumbiaException("El método sleep_on_fail($value, $time) de la clase Auth está desaconsejado. Borrar de su código.");
    }

    /**
     * Devuelve si es un usuario válido.
     *
     * @return bool
     */
    public static function is_valid()
    {
        if (!is_null(self::$is_valid)) {
            return self::$is_valid;
        }
        self::$is_valid = isset($_SESSION[self::VALID][Config::get('config.application.namespace_auth')]) ? $_SESSION[self::VALID][Config::get('config.application.namespace_auth')] : null;

        return self::$is_valid;
    }

    /**
     * Devuelve el resultado de la ultima identidad obtenida en authenticate 
     * desde el ultimo objeto Auth instanciado.
     *
     * @return array
     */
    public static function get_active_identity()
    {
        if (count(self::$active_identity)) {
            return self::$active_identity;
        }

        return self::$active_identity = $_SESSION[self::IDENTITY][Config::get('config.application.namespace_auth')];
    }
    
    /**
     * Obtiene un valor de la identidad actual.
     * 
     * @param string $var Llave que identifica el valor
     * @return string Valor de la llave
     */
    public static function get($var)
    {
        if (isset($_SESSION[self::IDENTITY][Config::get('config.application.namespace_auth')][$var])) {
            return $_SESSION[self::IDENTITY][Config::get('config.application.namespace_auth')][$var];
        }
    }

    /**
     * Anula la identidad actual.
     */
    public static function destroy_identity()
    {
        self::$is_valid = null;
        unset($_SESSION['KUMBIA_AUTH_VALID'][Config::get('config.application.namespace_auth')]);
        self::$active_identity = array();
        unset($_SESSION[self::IDENTITY][Config::get('config.application.namespace_auth')]);
    }
}
