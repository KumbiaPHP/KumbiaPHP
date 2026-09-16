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

class RouterController extends Controller
{
    public static array $events = [];
    public static bool $stopBeforeFilter = false;

    public function index(): void
    {
        self::$events[] = 'index';
    }

    public function show($id, $slug = 'default'): void
    {
        self::$events[] = "show:$id:$slug";
    }

    public function redirect(): void
    {
        self::$events[] = 'redirect';
        Router::to([
            'controller' => 'router',
            'action' => 'target',
            'parameters' => ['internal'],
            'controller_path' => 'router',
        ], true);
    }

    public function target($source = 'direct'): void
    {
        self::$events[] = "target:$source";
    }

    protected function initialize()
    {
        self::$events[] = 'initialize';
    }

    protected function before_filter()
    {
        self::$events[] = 'before_filter';

        if (self::$stopBeforeFilter) {
            return false;
        }
    }

    protected function after_filter()
    {
        self::$events[] = 'after_filter';
    }

    protected function finalize()
    {
        self::$events[] = 'finalize';
    }
}
