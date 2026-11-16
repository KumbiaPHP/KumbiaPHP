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

/**
 * @category    Test
 * @package     Css
 */
class CssTest extends PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        Closure::bind(static function () {
            Css::$_css = [];
            Css::$_dependencies = [];
        }, null, Css::class)();
    }

    public function testIncReturnsEmptyStringWhenNothingAdded()
    {
        $this->assertSame('', Css::inc());
    }

    public function testAddSingleFile()
    {
        Css::add('style');

        $expected = '<link href="' . PUBLIC_PATH . 'css/style.css" rel="stylesheet" type="text/css" />' . PHP_EOL;
        $this->assertSame($expected, Css::inc());
    }

    public function testAddMultipleFilesPreservesInsertionOrder()
    {
        Css::add('reset');
        Css::add('base');
        Css::add('main');

        $expected =
            '<link href="' . PUBLIC_PATH . 'css/reset.css" rel="stylesheet" type="text/css" />' . PHP_EOL .
            '<link href="' . PUBLIC_PATH . 'css/base.css" rel="stylesheet" type="text/css" />' . PHP_EOL .
            '<link href="' . PUBLIC_PATH . 'css/main.css" rel="stylesheet" type="text/css" />' . PHP_EOL;

        $this->assertSame($expected, Css::inc());
    }

    public function testDependenciesAreOutputBeforeMainFile()
    {
        Css::add('main', ['reset', 'base']);

        $output   = Css::inc();
        $resetPos = strpos($output, 'reset.css');
        $basePos  = strpos($output, 'base.css');
        $mainPos  = strpos($output, 'main.css');

        $this->assertLessThan($mainPos, $resetPos, 'reset.css should appear before main.css');
        $this->assertLessThan($mainPos, $basePos,  'base.css should appear before main.css');
    }

    public function testAllDependenciesAndMainFileAreIncluded()
    {
        Css::add('app', ['normalize', 'grid']);

        $output = Css::inc();

        $this->assertStringContainsString(PUBLIC_PATH . 'css/normalize.css', $output);
        $this->assertStringContainsString(PUBLIC_PATH . 'css/grid.css', $output);
        $this->assertStringContainsString(PUBLIC_PATH . 'css/app.css', $output);
    }

    public function testAddingSameFileMultipleTimesDeduplicates()
    {
        Css::add('style');
        Css::add('style');

        $this->assertSame(1, substr_count(Css::inc(), 'style.css'));
    }

    public function testAddingSameDependencyFromMultipleCallsDeduplicates()
    {
        Css::add('comp-a', ['shared']);
        Css::add('comp-b', ['shared']);

        $this->assertSame(1, substr_count(Css::inc(), 'shared.css'));
    }

    public function testFileUsedAsBothDependencyAndMainAppearsOnce()
    {
        Css::add('base');
        Css::add('main', ['base']);

        $this->assertSame(1, substr_count(Css::inc(), 'base.css'));
    }

    public function testIncIsIdempotent()
    {
        Css::add('style');

        $this->assertSame(Css::inc(), Css::inc());
    }

    public function testAddFileWithSubdirectoryPath()
    {
        Css::add('vendor/bootstrap');

        $expected = '<link href="' . PUBLIC_PATH . 'css/vendor/bootstrap.css" rel="stylesheet" type="text/css" />' . PHP_EOL;
        $this->assertSame($expected, Css::inc());
    }
}
