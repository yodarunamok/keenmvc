<?php
/**
 * Created by IntelliJ IDEA.
 * User: Chris Hansen (chris@iviking.org)
 * Date: 2/3/14
 * Time: 3:40 PM
 * To change this template use File | Settings | File Templates.
 */

class KeenTest extends PHPUnit_Framework_TestCase {
    public function testGetRoot () {
        require_once '../lib/Keen.php';
        // initialize variables for tests
        /** @var Keen $keen */
        $keen = Keen::instance('keen_test_config.ini');
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/';
        // perform associated test(s)
        $pageOut = $keen->run(true);
        $this->assertEquals('GET root', $pageOut);
    }
    public function testGetTest () {
        require_once '../lib/Keen.php';
        // initialize variables for tests
        /** @var Keen $keen */
        $keen = Keen::instance('keen_test_config.ini');
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '//test//';
        // perform associated test(s)
        $pageOut = $keen->run(true);
        $this->assertEquals('GET test', $pageOut);
    }
    public function testGetTestParam () {
        require_once '../lib/Keen.php';
        // initialize variables for tests
        /** @var Keen $keen */
        $keen = Keen::instance('keen_test_config.ini');
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '//test//param//';
        // perform associated test(s)
        $pageOut = $keen->run(true);
        $this->assertEquals('GET test param', $pageOut);
    }
}