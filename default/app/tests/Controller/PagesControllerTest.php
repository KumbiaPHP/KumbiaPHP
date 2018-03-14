<?php
/**
 * KumbiaPHP web & app Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://wiki.kumbiaphp.com/Licencia
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@kumbiaphp.com so we can send you a copy immediately.
 *
 * @category   Kumbia Tests
 * @copyright  Copyright (c) 2005 - 2018 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

use PHPUnit\Framework\TestCase;

/**
 * PagesControllerTest class
 */
class PagesControllerTest extends TestCase
{
    use KumbiaTestTrait;
    
    /**
     * testDisplay method
     *
     * @return void
     */
    public function testDisplay()
    {
        $actual = $this->get('/pages/show/kumbia/status/');
        $this->assertContains('<h2>config.ini', $actual);
        //$test = $this->get('/pages/show/kumbia/status/');
        $this->assertResponseCode(200);
    }
    /**
     * 
     *
     * @return void
     */
    public function testDisplayNoAction()
    {
        $actual = $this->get('/pages/kumbia/status/');
        $this->assertContains('<h2>config.ini', $actual);
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
