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
 * Cache en memoria con Sqlite
 * 
 * @category   Kumbia
 * @package    Cache
 * @subpackage Drivers 
 * @copyright  Copyright (c) 2005-2012 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */
/**
 * @see SqliteCache
 * */
require_once CORE_PATH . 'libs/cache/drivers/sqlite_cache.php';

/**
 * Cache en memoria con Sqlite
 *
 * @category   Kumbia
 * @package    Cache
 * @subpackage Drivers
 */
class MemSqliteCache extends SqliteCache
{

    /**
     * Constructor
     */
    public function __construct()
    {
        //Abre una conexión SqLite a la base de datos cache
        $this->_db = sqlite_open(':memory:');
        $result = sqlite_query($this->_db, "SELECT COUNT(*) FROM sqlite_master WHERE type='table' AND tbl_name='cache' ");
        $count = sqlite_fetch_single($result);

        if (!$count) {
            sqlite_exec($this->_db, ' CREATE TABLE cache (id TEXT, "group" TEXT, value TEXT, lifetime TEXT) ');
        }

        return $this->_db;
    }

}