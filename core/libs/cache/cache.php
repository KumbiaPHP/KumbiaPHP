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
 * @package    Cache
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
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
    protected static $_drivers = [];
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
    protected $_id;
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
    protected $_lifetime = '';
    /**
     * Start - end data
     *
     * @var array
     */
    protected $_start = [];

    /**
     * Carga un elemento cacheado
     *
     * @param string $id    identificador
     * @param string $group grupo
     * @return string
     */
    abstract public function get($id, $group = 'default');

    /**
     * Guarda un elemento en la cache con nombre $id y valor $value
     *
     * @param string $value     Contenido a cachear
     * @param string $lifetime  Tiempo de vida con formato strtotime, utilizado para cache
     * @param string|null $id   Identificador
     * @param string $group     Grupo, se crea la cache con $group.$id
     * @return boolean
     */
    abstract public function save($value, $lifetime = '', $id = null, $group = 'default');

    /**
     * Limpia la cache
     *
     * @param string|null $group
     * @return boolean
     */
    abstract public function clean($group = null);

    /**
     * Elimina un elemento de la cache
     *
     * @param string $id
     * @param string $group
     * @return boolean
     */
    abstract public function remove($id, $group = 'default');

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
            return false;
        }
        $this->_start = [
                'lifetime' => $lifetime,
                'id'       => $id,
                'group'    => $group
            ];

        // inicia la captura del buffer
        ob_start();

        // Inicia cacheo
        return true;
    }

    /**
     * Termina el buffer de salida
     *
     * @param boolean $save indica si al terminar guarda la cache
     * @return boolean
     */
    public function end($save = true)
    {
        if (!$save) {
            ob_end_flush();
            return false;
        }

        // obtiene el contenido del buffer
        $value = ob_get_contents();

        // libera el buffer
        ob_end_flush();

        return $this->save($value, $this->_start['lifetime'], $this->_start['id'], $this->_start['group']);
    }

    /**
     * Obtiene el driver de cache indicado
     *
     * @param string $driver (file, sqlite, memsqlite, APC)
     * @return Cache
     * */
    public static function driver($driver = '')
    {
        if (!$driver) {
            $driver = self::$_default_driver;
        }

        if (!isset(self::$_drivers[$driver])) {
            require __DIR__ . "/drivers/{$driver}_cache.php";
            $class = $driver . 'cache';
            self::$_drivers[$driver] = new $class();
        }

        return self::$_drivers[$driver];
    }

    /**
     * Cambia el driver por defecto
     *
     * @param string $driver nombre del driver por defecto
     * @return void
     */
    public static function setDefault($driver = 'file')
    {
        self::$_default_driver = $driver;
    }
}
