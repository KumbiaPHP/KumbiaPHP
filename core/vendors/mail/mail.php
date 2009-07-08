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
 * Esta es la clase principal del framework, contiene metodos importantes
 * para cargar los controladores y ejecutar las acciones en estos ademas
 * de otras funciones importantes
 *
 * @category   Kumbia
 * @package    vendors
 * @copyright  Copyright (c) 2005-2009 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

include_once CORE_PATH . 'vendors/mail/class.smtp.php';
include_once CORE_PATH . 'vendors/mail/class.phpmailer.php';

