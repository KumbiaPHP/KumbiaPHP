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
 * Clase encargada de cargar el adaptador de KumbiaPHP
 * 
 * @category   Kumbia
 * @package    Db
 * @subpackage Loader 
 * @copyright  Copyright (c) 2005-2009 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */
class DbLoader
{
    /**
     * Carga un driver Kumbia segun lo especificado en
     *
     * @return boolean
     */
    public static function load_driver ()
    {
        /**
         * Cargo el mode para mi aplicacion
         */
        $database = Config::get('config.application.database');
        $databases = Config::read('databases');
        $config = $databases[$database];
        if (isset($config['type']) && $config['type']) {
            if (isset($config['pdo']) && $config['pdo']) {
                require_once CORE_PATH . 'libs/db/adapters/pdo.php';
                require_once CORE_PATH . 'libs/db/adapters/pdo/' . $config['type'] . '.php';
                eval("class Db extends DbPDO{$config['type']} {}");
                return true;
            } else {
                require_once CORE_PATH . 'libs/db/adapters/' . $config['type'] . '.php';
                eval("class Db extends Db{$config['type']} {}");
                return true;
            }
        } else {
            return true;
        }
    }
}
