<?php
/**
 * KumbiaPHP web & app Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @category   Test
 * @package    Core
 *
 * @copyright  Copyright (c) 2005 - 2026 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

it('default router', function() {
    $_SERVER['REQUEST_METHOD'] = 'GET';

    expect(Router::execute('/'))
        ->toBeInstanceOf(Router::class);
    
});

it('default router vars', function($url, $method, $vars) {
    $_SERVER['REQUEST_METHOD'] = $method;

    Router::execute($url);
    expect(Router::get())->toBe($vars);
})->with([
            ['/', 'GET', 
               ['route'           => '',
                'method'          => 'GET',
                'module'          => '',
                'controller'      => 'index', //Nombre del controlador actual, por defecto index
                'action'          => 'index', //Nombre de la acción actual, por defecto index
                'parameters'      => [], //Lista los parámetros adicionales de la URL
                'controller_path' => 'index'
                ] 
            ],
            ['/', 'POST', 
               ['route'           => '',
                'method'          => 'POST',
                'module'          => '',
                'controller'      => 'index', //Nombre del controlador actual, por defecto index
                'action'          => 'index', //Nombre de la acción actual, por defecto index
                'parameters'      => [], //Lista los parámetros adicionales de la URL
                'controller_path' => 'index'
                ] 
            ],
]);
