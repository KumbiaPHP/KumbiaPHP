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
 * @copyright  Copyright (c) 2005 - 2023 KumbiaPHP Team (http://www.kumbiaphp.com)
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
     * 
     *
     * @return void
     */
    public function testDisplayPage()
    {
        $actual = $this->get('/pages/kumbia/status');
        $this->assertStringContainsString('<h2>config.', $actual);
        //$test = $this->get('/pages/show/kumbia/status/');
        $this->assertResponseCode(200);
    }
    /**
     * expectedException KumbiaException
     */
    //public function testDisplayNoPage()
    //{
        //$this->expectException(KumbiaException::class);
        //$actual = $this->get('/pages/no_page/');
        //$this->assertResponseCode(404);
        //$this->assertContains('<h1>Vista "pages/no_page.phtml" no encontrada</h1>', $actual);
        //$this->assertResponseCode(404);
        //$this->expectException(KumbiaException::class);
        
    //}
}
