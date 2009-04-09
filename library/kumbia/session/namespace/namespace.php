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
 * @package Session
 * @copyright  Copyright (C) 2007-2007 Emilio Rafael Silveira Tovar (emilio.rst@gmail.com)
 * @license http://www.kumbia.org/license.txt GNU/GPL
 */

/**
 * Modelo orientado a objetos para el acceso a datos en Sesiones a través de espacios con nombres
 *
 * @category Kumbia
 * @package Session
 * @abstract
 * @copyright  Copyright (C) 2007-2007 Emilio Rafael Silveira Tovar (emilio.rst@gmail.com)
 * @license http://www.kumbia.org/license.txt GNU/GPL
 * @access public
 */
abstract class SessionNamespace {

   /**
    * Añade un namespace
    *
    * @param string $namespace nombre del espacio
    * @param string $property nombre de la propiedad
    * @param mixed $value valor
    */
   static public function add($namespace, $property, $value){
      if(!Session::is_set($namespace)){
         Session::set_data($namespace, new StdClass());
      }
      $obj_namespace = Session::get_data($namespace);
      $obj_namespace->$property = $value;
   }

   /**
    * Obtiene los atributos de un namespace
    *
    * @param string $namespace nombre del espacio
    * @return object
    */
   static public function get($namespace){
      return SessionNamespace::exists($namespace) ? Session::get_data($namespace) : null;
   }

   /**
    * Verifica si existe el namespace
    *
    * @param string $namespace nombre del espacio
    * @return mixed
    */
   static public function exists($namespace){
      return Session::is_set($namespace);
   }

   /**
    * Reinicia el namespace
    *
    * @param string $namespace namspace a reiniciar
    */
   static public function reset($namespace){
      Session::set_data($namespace, new StdClass());
   }
}
