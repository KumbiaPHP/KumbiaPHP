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
 * @package Acl
 * @subpackage AclResource
 * @copyright Copyright (c) 2005-2007 Andres Felipe Gutierrez (andresfelipe at vagoogle.net)
 * @license http://www.kumbia.org/license.txt GNU/GPL
 */

/**
 * Clase para la creación de Resources ACL
 *
 * @category Kumbia
 * @package Acl
 * @subpackage AclResource
 * @access public
 * @copyright Copyright (c) 2005-2007 Andres Felipe Gutierrez (andresfelipe at vagoogle.net)
 * @license http://www.kumbia.org/license.txt GNU/GPL
 */
class AclResource{

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
	 * @return AclResource
	 */
	function __construct($name){
		if($name=='*'){
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
	function __set($name, $value){
		if($name!='name'){
			$this->$name = $value;
		}
	}

}