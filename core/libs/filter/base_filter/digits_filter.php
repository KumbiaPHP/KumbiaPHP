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
 * Filtra una cadena para que contenga solo Digitos
 *
 * @category   Kumbia
 * @package    Filter
 * @subpackage BaseFilter
 */
class DigitsFilter implements FilterInterface
{

    /**
     * Ejecuta el filtro
     *
     * @param string $s
     * @param string $options
     * @return string
     */
    public static function execute($s, $options)
    {
        $patron = '/[^0-9]/';
        return preg_replace($patron, '', (string) $s);
    }

}