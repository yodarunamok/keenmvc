<?php
/**
 * Created by IntelliJ IDEA.
 * User: Chris Hansen (chris@iviking.org)
 * Date: 2/3/14
 * Time: 3:40 PM
 * To change this template use File | Settings | File Templates.
 */

class KeenTest extends PHPUnit_Framework_TestCase {
    public function testAddGet () {
        require_once '../lib/keen.php';
        global $get;
        // initialize variables for tests
        /** @var Keen $keen */
        $keen = Keen::instance('keen_test_config.ini');
        $_SERVER['REQUEST_URI'] = '//test//test//';
        // perform associated test(s)
        ob_start(); // we'll be checking the output
        $keen->run();
        $pageOut = ob_get_contents();
        ob_end_clean();
        $this->assertEquals('GET test', $pageOut);
    }
}