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
 * @category   Kumbia
 * @package    Cache 
 * @copyright  Copyright (c) 2005-2012 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

/**
 * Clase base para componentes de cacheo
 *
 * @category   Kumbia
 * @package    Cache
 */
abstract class Cache
{

    /**
     * Pool de drivers para cache
     *
     * @var array
     * */
    protected static $_drivers = array();
    /**
     * Driver por defecto
     *
     * @var string
     * */
    protected static $_default_driver = 'file';
    /**
     * Id de ultimo elemento solicitado
     *
     * @var string
     */
    protected $_id = null;
    /**
     * Grupo de ultimo elemento solicitado
     *
     * @var string
     */
    protected $_group = 'default';
    /**
     * Tiempo de vida
     *
     * @var string
     */
    protected $_lifetime = null;

    /**
     * Carga un elemento cacheado
     *
     * @param string $id
     * @param string $group
     * @return string
     */
    public abstract function get($id, $group = 'default');

    /**
     * Guarda un elemento en la cache con nombre $id y valor $value
     *
     * @param string $value
     * @param string $lifetime tiempo de vida con formato strtotime, utilizado para cache
     * @param string $id
     * @param string $group
     * @return boolean
     */
    public abstract function save($value, $lifetime = NULL, $id = FALSE, $group = 'default');

    /**
     * Limpia la cache
     *
     * @param string $group
     * @return boolean
     */
    public abstract function clean($group=false);

    /**
     * Elimina un elemento de la cache
     *
     * @param string $id
     * @param string $group
     * @return boolean
     */
    public abstract function remove($id, $group = 'default');

    /**
     * Inicia el cacheo del buffer de salida hasta que se llame a end
     *
     * @param string $lifetime tiempo de vida con formato strtotime, utilizado para cache
     * @param string $id
     * @param string $group
     * @return boolean
     */
    public function start($lifetime, $id, $group = 'default')
    {
        if ($data = $this->get($id, $group)) {
            echo $data;

            // No es necesario cachear
            return FALSE;
        }
        $this->_lifetime = $lifetime;

        // inicia la captura del buffer
        ob_start();

        // Inicia cacheo
        return TRUE;
    }

    /**
     * Termina el buffer de salida
     *
     * @param boolean $save indica si al terminar guarda la cache
     * @return boolean
     */
    public function end($save = TRUE)
    {
        if (!$save) {
            ob_end_flush();
            return FALSE;
        }

        // obtiene el contenido del buffer
        $value = ob_get_contents();

        // libera el buffer
        ob_end_flush();

        return $this->save($value, $this->_lifetime, $this->_id, $this->_group);
    }

    /**
     * Obtiene el driver de cache indicado
     *
     * @param string $driver (file, sqlite, memsqlite, APC)
     * */
    public static function driver($driver = NULL)
    {
        if (!$driver) {
            $driver = self::$_default_driver;
        }

        if (!isset(self::$_drivers[$driver])) {
            require_once CORE_PATH . "libs/cache/drivers/{$driver}_cache.php";
            $class = $driver . 'cache';
            self::$_drivers[$driver] = new $class();
        }

        return self::$_drivers[$driver];
    }

    /**
     * Cambia el driver por defecto
     *
     * @param string $driver nombre del driver por defecto
     */
    public static function setDefault($driver = 'file')
    {
        self::$_default_driver = $driver;
    }

}
