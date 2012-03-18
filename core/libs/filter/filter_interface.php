<?php
/**
 * Kumbia PHP Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://XXXXXXXX
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Kumbia
 * @package    Filter 
 * @copyright  Copyright (c) 2005-2012 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * Interface para los filtros
 *
 * @category  Kumbia
 * @package   Filter
 */
interface FilterInterface
{

    /**
     * Metodo para ejecutar el filtro
     *
     * @param string $s cadena a filtrar
     * @param array $options opciones para el filtro
     */
    public static function execute($s, $options);
}

