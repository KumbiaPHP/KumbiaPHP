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
 * @category   extensions
 * @package    Auth
 * @copyright  Copyright (c) 2005-2015 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

interface KumbiaAuthInterface{
    public function login();
    /**
     * @param string $name
     */
    public function get($name);
}

class KumbiaAuth{
    /**
     * Espacio de nombre para las variables de login
     * @var String
     */
    static protected $_ns = 'KumbiaAuthNameSpace';

    /**
     * Objeto de autenticacion
     * @var KumbiaAuthInterface
     */
    static protected $_obj = null;

    /**
     * Inyecta el objeto de autenticación
     * @param KumbiaAuthInterface $auth
     */
    static function inject(KumbiaAuthInterface $auth){
        self::$_obj = $auth;
    }

    /**
     * Retorna el objeto de autenticación haciendo la verificacion
     * @return KumbiaAuthInterface
     */
    static function getObj(){
        if(!self::$_obj instanceof KumbiaAuthInterface)
            throw new Exception('Objeto de autenticación nulo');
        return self::$_obj;
    }

    /**
     * Verifica si el usuario está logueado
     * @return bool
     */
    static function isLogin(){
        return (bool) Session::get('login', self::$_ns);
    }

    /**
     * Desloguea a un usuario
     */
    static function logout(){
        Session::set('login', FALSE, self::$_ns);
    }

    /**
     * Hace el login
     * @param Array $args Agumentos para autentica
     * @return bool
     */
    static function login(Array $args = array()){
        $auth = self::getObj();
        $login =  $auth->login($args);
        Session::set('login', $login, self::$_ns);
        return $login;
    }

    /**
     * Retorna una varible
     * @param string $name de la variable
     * @return mixed
     */
    static function get($name){
        $auth = self::getObj();
        return $auth->get($name);
    }
}
