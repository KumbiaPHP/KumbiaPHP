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


trait KumbiaTestTrait
{
    /**
     * Asserts HTTP response code
     *
     * @param int $code
     */
    public function assertResponseCode($code)
    {
        $actual = http_response_code();
        $this->assertSame(
            $code,
            $actual,
            "Status code is not $code but $actual."
        );
    }
    /**
     * Request to Controller
     *
     * @param string       $method      HTTP method
     * @param string       $url         controller/method/arg|uri
     * @param array        $params      POST parameters/Query string
     */
    protected function request($method, $url, $params = [])
    {
        $_SERVER['REQUEST_METHOD'] = $method;
        
        ob_start();
        $start_ob_level = ob_get_level();
        ob_start();
        View::render(Router::execute($url));
        while (ob_get_level() > $start_ob_level) {
            ob_end_flush();
        }

        //$content = $this->getActualOutput();
        return ob_get_clean();
    }
    /**
     * GET Request to Controller
     *
     * @param string       $url         controller/method/arg|uri
     * @param array        $params      Query string
     */
    public function get($url, $params = [])
    {
        return $this->request('GET', $url, $params);
    }
}
