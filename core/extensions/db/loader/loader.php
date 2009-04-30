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
 * @category   Kumbia
 * @package    Db
 * @subpackage Loader
 * @copyright  Copyright (c) 2005-2007 Andres Felipe Gutierrez (andresfelipe at vagoogle.net)
 * @copyright  Copyright (c) 2008-2008 Emilio Rafael Silveira Tovar (emilio.rst at gmail.com)
 * @copyright  Copyright (c) 2008-2009 Deivinson Tejeda Brito (deivinsontejeda at gmail.com)
 * @license http://www.kumbia.org/license.txt GNU/GPL
 */

/**
 * Clase encargada de cargar el adaptador de Kumbia
 *
 * @category   Kumbia
 * @package    Db
 * @subpackage Loader
 * @copyright  Copyright (c) 2005-2007 Andres Felipe Gutierrez (andresfelipe at vagoogle.net)
 * @copyright  Copyright (c) 2008-2008 Emilio Rafael Silveira Tovar (emilio.rst at gmail.com)
 * @copyright  Copyright (c) 2008-2009 Deivinson Tejeda Brito (deivinsontejeda at gmail.com)
 * @license    http://www.kumbia.org/license.txt GNU/GPL
 */
class DbLoader  {

	/**
	 * Carga un driver Kumbia segun lo especificado en
	 *
	 * @return boolean
	 */
	public static function load_driver(){
		/**
		 * Cargo el mode para mi aplicacion
		 */
        $database = Config::get('config.application.database');
		$databases = Config::read('databases.ini');
		$config = $databases[$database];

		if(isset($config['type']) && $config['type']){
			if(isset($config['pdo']) && $config['pdo']){
				require_once CORE_PATH . 'extensions/db/adapters/pdo.php';
				require_once CORE_PATH . 'extensions/db/adapters/pdo/' . $config['type'] . '.php';
				eval("class Db extends DbPDO{$config['type']} {}");
				return true;
			} else {
				require_once CORE_PATH . 'extensions/db/adapters/' . $config['type'] . '.php';
				eval("class Db extends Db{$config['type']} {}");
				return true;
			}
		} else {
			return true;
		}
	}
}
