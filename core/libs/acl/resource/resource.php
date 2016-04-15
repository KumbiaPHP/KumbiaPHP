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
 * @package    Acl
 * @subpackage AclResource
 * @copyright  Copyright (c) 2005 - 2016 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

/**
 * Clase para la creación de Recursos ACL
 *
 * @category   Kumbia
 * @package    Acl
 * @subpackage AclResource
 */
class AclResource
{

    /**
     * Nombre del Recurso
     *
     * @var $name
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
            throw new KumbiaException('Nombre invalido "*" para nombre de Resource en Acl_Resoruce::__constuct');
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
