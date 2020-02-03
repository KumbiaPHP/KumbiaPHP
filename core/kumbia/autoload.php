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
 * @package    Core
 *
 * @copyright  Copyright (c) 2005 - 2020 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

// @see Util
require CORE_PATH.'kumbia/util.php';

// Autocarga de clases
function kumbia_autoload($class)
{
    // Optimizando carga
    static $classes;
    if ( ! isset($classes)) {
        $classes = [
            'ActiveRecord'    => APP_PATH.'libs/active_record.php',
            'Load'            => CORE_PATH.'kumbia/load.php',
            'KumbiaException' => CORE_PATH.'kumbia/kumbia_exception.php',
            'KumbiaRouter'    => CORE_PATH.'kumbia/kumbia_router.php',
            'KumbiaFacade'    => CORE_PATH.'kumbia/kumbia_facade.php'
        ];
    }
    if (isset($classes[$class])) {
        return include $classes[$class];
    }
    // PSR0
    if (strpos($class, '\\')) {
        return kumbia_autoload_vendor($class);
    }
    // for legacy apps
    if ($class === 'Flash') {
        return kumbia_autoload_helper('Flash');
    }

    // Convert to smallcase
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
    // Perhaps is PEAR,  zend framework 1, ...

    return kumbia_autoload_vendor($class);
}

function kumbia_autoload_vendor($class)
{
    //Autoload PSR0
    $psr0 = dirname(dirname(APP_PATH)).'/vendor/'.str_replace(['_', '\\'], DIRECTORY_SEPARATOR, $class).'.php';
    if (is_file($psr0)) {
        include $psr0;
    }
}

function kumbia_autoload_helper($class)
{
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
