<?php
/**
 * KumbiaPHP web & app Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 *
 * @category   Kumbia Tests
 * @package    Controller
 *
 * @copyright  Copyright (c) 2005 - 2020 KumbiaPHP Team (http://www.kumbiaphp.com)
 * @license    https://github.com/KumbiaPHP/KumbiaPHP/blob/master/LICENSE   New BSD License
 */

use PHPUnit\Framework\TestCase;

/**
 * PagesControllerTest class
 */
class PagesControllerTest extends TestCase
{
    use KumbiaTestTrait;
    
    /**
     */
    public function testDisplayNoAction(): void
    {
        $actual = $this->get('/pages/kumbia/status/');
        $this->assertStringContainsString('<h2>config.php', $actual);
        $this->assertResponseCode(200);
    }
    /**
     * Test no page to show
     */
    public function testDisplayNoPage(): void
    {
        $this->expectWarning();
        $this->expectWarningMessageMatches('/No such file or directory/');
        
        $actual = $this->get('/pages/no_page/');

        $this->expectException(KumbiaException::class);
        $this->assertResponseCode(404);
        $this->assertStringContainsString('<h1>Vista "pages/no_page.phtml" no ffencontrada</h1>', $actual);
    }

    /**
     * Test for bad people
     */
    public function testBadPeople(): void
    {
        $this->expectException(KumbiaException::class);
        $actual = $this->get('/pages/../no_page/');

        $this->assertResponseCode(404);
        $this->assertStringContainsString("Posible intento de hack en URL: '/pages/../no_page/'", $actual);
    }

    public function testObLevel(): void
    {
        $this->assertEquals(1, ob_get_level());
    }
}
