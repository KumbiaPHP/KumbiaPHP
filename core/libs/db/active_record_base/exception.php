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
 * @package Db
 * @subpackage ActiveRecord
 * @copyright Copyright (c) 2005-2007 Andres Felipe Gutierrez (andresfelipe at vagoogle.net)
 * @license http://www.kumbia.org/license.txt GNU/GPL
 */

/**
 * Clase para manejar errores ocurridos en operaciones de ActiveRecord
 *
 * @category Kumbia
 * @package Db
 * @subpackage ActiveRecord
 * @copyright Copyright (c) 2005-2007 Andres Felipe Gutierrez (andresfelipe at vagoogle.net)
 * @license http://www.kumbia.org/license.txt GNU/GPL
 * @access public
 */
class ActiveRecordException extends KumbiaException {

	/**
	 * Muestra un warning de ActiveRecord
	 *
	 * @param string $title
	 * @param string $message
	 * @param string $source
	 */
	static function display_warning($title, $message, $source){

		$controller_name = Router::get('controller');
		$action = Router::get('action');

		Flash::warning("
		<span style='font-size:16px;color:black'>KumbiaWarning: $title</span><br/>
		<div>$message<br>
		<span style='font-size:12px;color:black'>En el modelo <i>{$source}</i> al ejecutar <i>$controller_name/$action</i></span></div>", true);
		print "<pre style='border:1px solid #969696;background:#FFFFE8;color:black'>";
		print debug_print_backtrace()."\n";
		print "</pre>";
	}

}