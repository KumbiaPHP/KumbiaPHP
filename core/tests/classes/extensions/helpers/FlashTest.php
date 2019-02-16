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
 * @package    Flash
 *
 * @copyright  Copyright (c) 2005 - 2019 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

/**
 * @category    Test
 * @package     Flash
 */
class FlashTest extends PHPUnit\Framework\TestCase
{
    public function setUp()
    {
        $_SERVER['SERVER_SOFTWARE'] = 'Test';
    }

    public function testShowTypeTest()
    {
        $this->expectOutputString('<div class="test flash">Test Content</div>'.PHP_EOL);
        Flash::show('test', 'Test Content');
    }

    public function testShowTypeSuccess()
    {
        $this->expectOutputString('<div class="success flash">Test Content</div>'.PHP_EOL);
        Flash::show('success', 'Test Content');
    }

    public function testValid()
    {
        $this->expectOutputString('<div class="valid flash">Test content for valid</div>'.PHP_EOL);
        Flash::valid('Test content for valid');
    }

    public function testError()
    {
        $this->expectOutputString('<div class="error flash">Test content for error</div>'.PHP_EOL);
        Flash::error('Test content for error');
    }

    public function testInfo()
    {
        $this->expectOutputString('<div class="info flash">Test content for info</div>'.PHP_EOL);
        Flash::info('Test content for info');
    }

    public function testWarning()
    {
        $this->expectOutputString('<div class="warning flash">Test content for warning</div>'.PHP_EOL);
        Flash::warning('Test content for warning');
    }
}
