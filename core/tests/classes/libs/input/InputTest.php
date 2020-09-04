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
 * @category   Kumbia
 * @package    Session
 * @copyright  Copyright (c) 2005 - 2017 Kumbia Team (http://www.kumbiaphp.com)
 * @license    http://wiki.kumbiaphp.com/Licencia     New BSD License
 */

/**
 * @category Test
 */
class InputTest extends PHPUnit\Framework\TestCase
{
    public function setUp()
    {
        $this->originalValues = array(
            $_GET,
            $_POST,
            $_REQUEST,
            $_SERVER,
        );

        $_GET = array();
        $_POST = array();
        $_REQUEST = array();
        $_SERVER = array();
    }

    protected function tearDown()
    {
        list($_GET, $_POST, $_REQUEST, $_SERVER) = $this->originalValues;
    }

    public function isMethodProvider()
    {
        return array(
            array('GET', 'GET', true),
            array('POST', 'POST', true),
            array('get', 'GET', false),
            array('get', 'POST', false),
            array('GET', 'POST', false),
            array('POST', 'GET', false),
            array('GET ', 'GET', false),
            array(' GET ', 'GET', false),
            array(' GET', 'GET', false),
            array(' Get', 'GET', false),
        );
    }

    /**
     * @dataProvider isMethodProvider
     */
    public function testIsMethod($expectedMethod, $method, $canBeTrue)
    {
        $_SERVER['REQUEST_METHOD'] = $method;

        $result = Input::is($expectedMethod);

        if ($canBeTrue) {
            $this->assertTrue($result);
        } else {
            $this->assertFalse($result);
        }
    }

    public function testIsAjaxMustBeTrue()
    {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';

        $this->assertTrue(Input::isAjax());
    }

    public function testIsAjaxMustBeFalse()
    {
        $this->assertFalse(Input::isAjax());

        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'OtherValue';
        $this->assertFalse(Input::isAjax());
    }

    public function testDeleteIndex()
    {
        $_POST['__index__'] = '__value__';

        $this->assertSame('__value__', $_POST['__index__']);

        Input::delete('__index__');

        $this->assertSame(array(), $_POST['__index__']);
    }

    public function testDeleteWithoutIndex()
    {
        $_POST['__index__'] = '__value__';

        $this->assertSame('__value__', $_POST['__index__']);

        Input::delete();

        $this->assertSame(array(), $_POST);
    }

    public function testIpFromClientIp()
    {
        $_SERVER['HTTP_CLIENT_IP'] = '__test_ip__';

        $this->assertSame('__test_ip__', Input::ip());
    }

    public function testIpFromXForwaredFor()
    {
        $_SERVER['HTTP_X_FORWARDED_FOR'] = '__test_ip__';

        $this->assertSame('__test_ip__', Input::ip());
    }

    public function testIpFromRemoteAddr()
    {
        $_SERVER['REMOTE_ADDR'] = '__test_ip__';

        $this->assertSame('__test_ip__', Input::ip());
    }

    public function getRequestTestingData()
    {
        return array(
            array(&$_POST, 'post'),
            array(&$_GET, 'get'),
            array(&$_REQUEST, 'request'),
        );
    }

    /**
     * @dataProvider getRequestTestingData
     */
    public function testRequestSimpleIndex(&$GLOBAl, $method)
    {
        $hasMethod = 'has'.ucfirst($method);

        $this->assertFalse(Input::$hasMethod('__post_index__'));

        $GLOBAl['__post_index__'] = 'value';

        $this->assertSame('value', Input::$method('__post_index__'));
    }

    /**
     * @dataProvider getRequestTestingData
     */
    public function testRequestWithoutIndex(&$GLOBAl, $method)
    {
        $this->assertEmpty(Input::$method());

        $GLOBAl['__post_index__'] = 'value';

        $this->assertSame(array('__post_index__' => 'value'), Input::$method());
    }

    /**
     * @dataProvider getRequestTestingData
     */
    public function testRequestNestedIndex(&$GLOBAL, $method)
    {
        $this->assertSame('', Input::post('index1.index2.index4'));
        $this->assertSame('', Input::post('index1.index3'));

        $_POST['index1'] = array(
            'index2' => array(
                'index4' => 'value4',
            ),
            'index3' => 'value3',
        );

        $this->assertSame('value4', Input::post('index1.index2.index4'));
        $this->assertSame('value3', Input::post('index1.index3'));

        $this->assertSame('', Input::post('index1.index3.index4'));
        $this->assertSame('', Input::post('index1.index5'));
        $this->assertSame('', Input::post('index61'));
    }
}
