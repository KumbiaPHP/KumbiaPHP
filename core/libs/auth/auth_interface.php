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
 * @package    Auth
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * Contiene métodos claves que implementan los adaptadores
 *
 * @category   Kumbia
 * @package    Auth
 */
interface AuthInterface
{

    /**
     * Constructor del adaptador
     */
    public function __construct($auth, $extra_args);

    /**
     * Obtiene los datos de identidad obtenidos al autenticar
     *
     */
    public function get_identity();

    /**
     * Autentica un usuario usando el adaptador
     *
     * @return boolean
     */
    public function authenticate();
}
