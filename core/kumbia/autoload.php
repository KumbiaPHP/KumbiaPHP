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
 * @package    Core
 * @copyright  Copyright (c) 2005-2015 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

// @see Util
require CORE_PATH.'kumbia/util.php';

// Autocarga de clases
function kumbia_autoload($class) {
	// Optimizando carga
	static $classes;
	if (!isset($classes)) {
		$classes = array (
			'ActiveRecord'    => APP_PATH.'libs/active_record.php',
			'Load'            => CORE_PATH.'kumbia/load.php',
			'KumbiaException' => CORE_PATH.'kumbia/kumbia_exception.php',
			'KumbiaRouter'    => CORE_PATH.'kumbia/kumbia_router.php'
		);
	}
	if (array_key_exists($class, $classes)) {
		return include $classes[$class];
	}elseif($class == 'Flash'){
		return kumbia_autoload_helper('Flash');
	}

	// Pasando a smallcase
	$sclass = Util::smallcase($class);
	if (is_file(APP_PATH."models/$sclass.php")) {
		return include APP_PATH."models/$sclass.php";
	}
	if (is_file(APP_PATH."libs/$sclass.php")) {
		return include APP_PATH."libs/$sclass.php";
	}
	if (is_file(CORE_PATH."libs/$sclass/$sclass.php")) {
		return include CORE_PATH."libs/$sclass/$sclass.php";
	}

	//Autoload PSR0
	$psr0 = dirname(CORE_PATH).'/vendor/'.str_replace(array('_', '\\'), DIRECTORY_SEPARATOR, $class).'.php';
	if (is_file($psr0)) {
		include $psr0;
	}
}

function kumbia_autoload_helper($class) {
	$sclass = Util::smallcase($class);
	if (is_file(APP_PATH."extensions/helpers/$sclass.php")) {
		return include APP_PATH."extensions/helpers/$sclass.php";
	}
	if (is_file(CORE_PATH."extensions/helpers/$sclass.php")) {
		return include CORE_PATH."extensions/helpers/$sclass.php";
	}
}


// Registrar la autocarga
spl_autoload_register('kumbia_autoload');