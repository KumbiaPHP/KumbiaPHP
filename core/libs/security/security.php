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
 * @package    Security
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Clase que contiene métodos útiles para manejar seguridad.
 *
 * @category   Kumbia
 * @package    Security
 */
abstract class Security
{
    public static function generateRSAKey($kumbia)
    {
        $h = date('G') > 12 ? 1 : 0;
        $time = uniqid().mktime($h, 0, 0, date('m'), date('d'), date('Y'));
        $key = sha1($time);
        $_SESSION['rsa_key'] = $key;
        $xCode = "<input type='hidden' id='rsa32_key' value='$key' />\r\n";
        if ($kumbia) {
            echo $xCode;
        } else {
            return $xCode;
        }
    }

    public static function createSecureRSAKey($kumbia = true)
    {
        $config = Config::read('config');
        if ($config->kumbia->secure_ajax) {
            if ($_SESSION['rsa_key']) {
                if ((time() % 8) == 0) {
                    return self::generateRSAKey($kumbia);
                }
                echo "<input type='hidden' id='rsa32_key' value=\"{$_SESSION['rsa_key']}\"/>";
            }

            return self::generateRSAKey($kumbia);
        }
    }
}
