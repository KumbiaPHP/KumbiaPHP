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
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

 /**
  * Clase principal para el manejo de excepciones.
  *
  * @category   Kumbia
  */
 abstract class KumbiaFacade
 {
     protected static $providers = [];

     /**
      * Set the providers.
      *
      * @param array $p key/value array with providers
      */
     public static function providers(array $p)
     {
         self::$providers = $p;
     }

     /**
      * Getter for the alias of the component.
      */
     protected static function getAlias()
     {
         throw new KumbiaException('Not implement');
     }

     protected static function getInstance($name)
     {
         return  isset(self::$providers[$name]) ? self::$providers[$name] : null;
     }

     /**
      * Handle dynamic, static calls to the object.
      *
      * @param string $method
      * @param array  $args
      *
      * @return mixed
      *
      * @throws \KumbiaException
      */
     public static function __callStatic($method, $args)
     {
         $instance = self::getInstance(static::getAlias());
         if (!$instance) {
             throw new KumbiaException('A facade root has not been set.');
         }

         switch (count($args)) {
            case 0:
                return $instance->$method();
            case 1:
                return $instance->$method($args[0]);
            case 2:
                return $instance->$method($args[0], $args[1]);
            default:
                return call_user_func_array([$instance, $method], $args);
        }
     }
 }
