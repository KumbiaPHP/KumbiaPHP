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
 * @package    Core
 *
 * @copyright  Copyright (c) 2005 - 2021 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Kumbia Version
 *
 * @category   Kumbia
 * @package    Core
 */

const KUMBIA_VERSION = '1.1.5';

/**
 * Versión de KumbiaPHP
 * 
 * @deprecated 1.1  Use constant KUMBIA_VERSION
 * @return string
 */
function kumbia_version()
{
    return KUMBIA_VERSION;
}
