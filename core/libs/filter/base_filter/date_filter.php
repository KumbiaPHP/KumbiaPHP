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
 * Filtra una cadena haciendo que sea fecha
 *
 * @category   Kumbia
 * @package    Filter
 * @subpackage BaseFilter
 */
class DateFilter implements FilterInterface
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
        $patron = '/(19|20)(\d{2})-(\d{1,2})-(\d{1,2})/';
        if (preg_match($patron, $s, $regs)) {
            return $regs[0];
        } else {
            return "";
        }
    }

}