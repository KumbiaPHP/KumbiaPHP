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
 * @subpackage AclRole
 * @copyright  Copyright (c) 2005-2007 Andres Felipe Gutierrez (andresfelipe at vagoogle.net)
 * @license    http://www.kumbia.org/license.txt GNU/GPL
 */

/**
 * Clase para manejar excepciones ocurridas en la clase AclRole
 *
 * @category Kumbia
 * @package Acl
 * @subpackage AclRole
 * @copyright Copyright (c) 2005-2007 Andres Felipe Gutierrez (andresfelipe at vagoogle.net)
 * @license http://www.kumbia.org/license.txt GNU/GPL
 * @access public
 */
class AclRoleException extends KumbiaException {

	/**
	 * Mensaje de la excepcion
	 *
	 * @var string
	 */
	public $message;

	/**
	 * Mensaje extendido de la excepcion
	 *
	 * @var string
	 */
	public $extended_message;

	/**
	 * Constructor de AclException
	 *
	 * @param string $message
	 * @param string $extended_message
	 */
	public function __construct($message, $extended_message=''){
		$this->message = "AclRoleException";
		$this->extended_message = $extended_message;
		parent::__construct($message);
	}

}