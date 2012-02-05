<?php
/**
 * Warning! This IS A ALPHA VERSION NOT USE IN PRODUCTION APP!
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
 * ApplicationController Es la clase principal para controladores de Kumbia
 * 
 * @author     Ashrey
 * @category   Kumbia
 * @package    Controller 
 * @copyright  Copyright (c) 2005-2009 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */
class API
{
	protected static $fSupport = array('text', 'json', 'xml', 'php', 'html');
	
	static function execute(){
		/*Compruebo el método de petición*/
		$method = strtolower($_SERVER['REQUEST_METHOD']);
		$format = explode(',', $_SERVER['HTTP_ACCEPT']);
		while($f = array_shift($format)){
			$f = str_replace(array('text/', 'application/'), '', $f);
			if(in_array($f, self::$fSupport))
				break;
		}
		if($f== null){
			return 'error';
		}else{
			View::response($f);
			return $method;	
		}
    }
}

echo API::execute();
