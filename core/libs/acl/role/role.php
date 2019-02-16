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
 * @package    Acl
 * @subpackage AclRole
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Clase para la creación de Roles ACL
 *
 * Esta clase define los roles y parametros
 * de cada uno
 *
 * @category   Kumbia
 * @package    Acl
 * @subpackage AclRole
 */
class AclRole
{

    /**
     * Nombre del Rol
     *
     * @var string
     */
    public $name;

    /**
     * Constructor de la clase Rol
     *
     * @param string $name
     */
    public function __construct($name)
    {
        if ($name == '*') {
            throw new KumbiaException('Nombre invalido "*" para nombre de Rol en Acl_Role::__constuct');
        }
        $this->name = $name;
    }

    /**
     * Impide que le cambien el nombre al Rol en el Objeto
     *
     * @param string $name
     * @param string $value
     */
    public function __set($name, $value)
    {
        if ($name != 'name') {
            $this->$name = $value;
        }
    }

}
