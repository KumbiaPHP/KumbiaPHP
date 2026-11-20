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
 * @package    Css
 *
 * @copyright  Copyright (c) 2005 - 2026 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

beforeEach(function () {
    Closure::bind(static function () {
        Css::$_css = [];
        Css::$_dependencies = [];
    }, null, Css::class)();
});

it('returns empty string when nothing added', function () {
    expect(Css::inc())->toBe('');
});

it('adds single file', function () {
    Css::add('style');

    $expected = '<link rel="stylesheet" href="' . PUBLIC_PATH . 'css/style.css"/>' . PHP_EOL;
    expect(Css::inc())->toBe($expected);
});

it('adds multiple files and preserves insertion order', function () {
    Css::add('reset');
    Css::add('base');
    Css::add('main');

    $expected =
        '<link rel="stylesheet" href="' . PUBLIC_PATH . 'css/reset.css"/>' . PHP_EOL .
        '<link rel="stylesheet" href="' . PUBLIC_PATH . 'css/base.css"/>' . PHP_EOL .
        '<link rel="stylesheet" href="' . PUBLIC_PATH . 'css/main.css"/>' . PHP_EOL;

    expect(Css::inc())->toBe($expected);
});

it('outputs dependencies before main file', function () {
    Css::add('main', ['reset', 'base']);

    $expected =
        '<link rel="stylesheet" href="' . PUBLIC_PATH . 'css/reset.css"/>' . PHP_EOL .
        '<link rel="stylesheet" href="' . PUBLIC_PATH . 'css/base.css"/>' . PHP_EOL .
        '<link rel="stylesheet" href="' . PUBLIC_PATH . 'css/main.css"/>' . PHP_EOL;

    expect(Css::inc())->toBe($expected);
});

it('includes all dependencies and main file', function () {
    Css::add('app', ['normalize', 'grid']);

    $expected =
        '<link rel="stylesheet" href="' . PUBLIC_PATH . 'css/normalize.css"/>' . PHP_EOL .
        '<link rel="stylesheet" href="' . PUBLIC_PATH . 'css/grid.css"/>' . PHP_EOL .
        '<link rel="stylesheet" href="' . PUBLIC_PATH . 'css/app.css"/>' . PHP_EOL;

    expect(Css::inc())->toBe($expected);
});

it('deduplicates same file added multiple times', function () {
    Css::add('style');
    Css::add('style');

    $expected = '<link rel="stylesheet" href="' . PUBLIC_PATH . 'css/style.css"/>' . PHP_EOL;
    expect(Css::inc())->toBe($expected);
});

it('deduplicates same dependency from multiple calls', function () {
    Css::add('comp-a', ['shared']);
    Css::add('comp-b', ['shared']);

    $expected =
        '<link rel="stylesheet" href="' . PUBLIC_PATH . 'css/shared.css"/>' . PHP_EOL .
        '<link rel="stylesheet" href="' . PUBLIC_PATH . 'css/comp-a.css"/>' . PHP_EOL .
        '<link rel="stylesheet" href="' . PUBLIC_PATH . 'css/comp-b.css"/>' . PHP_EOL;

    expect(Css::inc())->toBe($expected);
});

it('outputs file used as both dependency and main only once', function () {
    Css::add('base');
    Css::add('main', ['base']);

    $expected =
        '<link rel="stylesheet" href="' . PUBLIC_PATH . 'css/base.css"/>' . PHP_EOL .
        '<link rel="stylesheet" href="' . PUBLIC_PATH . 'css/main.css"/>' . PHP_EOL;

    expect(Css::inc())->toBe($expected);
});

it('is idempotent', function () {
    Css::add('style');

    expect(Css::inc())->toBe(Css::inc());
});

it('adds file with subdirectory path', function () {
    Css::add('vendor/bootstrap');

    $expected = '<link rel="stylesheet" href="' . PUBLIC_PATH . 'css/vendor/bootstrap.css"/>' . PHP_EOL;
    expect(Css::inc())->toBe($expected);
});
