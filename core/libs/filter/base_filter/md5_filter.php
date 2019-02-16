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
 * @package    Filter
 * @subpackage BaseFilter
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Filtra una cadena cifrando con md5
 *
 * @category   Kumbia
 * @package    Filter
 * @subpackage BaseFilter
 */
class Md5Filter implements FilterInterface
{

    /**
     * Ejecuta el filtro
     *
     * @param string $s
     * @param array $options
     * @return string
     */
    public static function execute($s, $options)
    {
        if (isset($options['binary']) && $options['binary'] == 'true') {
            return md5((string) $s, true);
        } else {
            return md5((string) $s);
        }
    }

}