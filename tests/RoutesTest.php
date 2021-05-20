<?php

use KeenMVC\App;

/**
 * Created by IntelliJ IDEA.
 * User: Chris Hansen (chris@iviking.org)
 * Date: 2/3/14
 * Time: 3:40 PM
 */

class RoutesTest extends PHPUnit_Framework_TestCase
{
    public function testBadRoute()
    {
        require_once "../lib/Keen.php";
        // initialize variables for tests
        /** @var App $keen */
        $keen = App::load("lib/keen_test_environment_config.ini.php");
        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "//bad//path//";
        // perform associated test(s)
        $pageOut = $keen->run(true);
        $this->assertEquals("404", $pageOut);
    }

    public function testNoViewFile()
    {
        require_once "../lib/Keen.php";
        // initialize variables for tests
        /** @var App $keen */
        $keen = App::load("lib/keen_test_environment_config.ini.php");
        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/";
        // perform associated test(s)
        $pageOut = $keen->run(true);
        $this->assertEquals("GET root", $pageOut);
    }

    public function testGetRoot()
    {
        require_once "../lib/Keen.php";
        // initialize variables for tests
        /** @var App $keen */
        $keen = App::load("lib/keen_test_environment_config.ini.php");
        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/";
        // perform associated test(s)
        $pageOut = $keen->run(true);
        $this->assertEquals("GET root", $pageOut);
    }

    public function testGetTest()
    {
        require_once "../lib/Keen.php";
        // initialize variables for tests
        /** @var App $keen */
        $keen = App::load("lib/keen_test_environment_config.ini.php");
        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "//test//";
        // perform associated test(s)
        $pageOut = $keen->run(true);
        $this->assertEquals("GET test", $pageOut);
    }

    public function testInvalidPostTest()
    {
        require_once "../lib/Keen.php";
        // initialize variables for tests
        /** @var App $keen */
        $keen = App::load("lib/keen_test_environment_config.ini.php");
        $_SERVER["REQUEST_METHOD"] = "POST";
        $_SERVER["REQUEST_URI"] = "//test//";
        // perform associated test(s)
        $pageOut = $keen->run(true);
        $this->assertEquals("501", $pageOut);
    }

    public function testGetTestParam()
    {
        require_once "../lib/Keen.php";
        // initialize variables for tests
        /** @var App $keen */
        $keen = App::load("lib/keen_test_environment_config.ini.php");
        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "//test//param//";
        // perform associated test(s)
        $pageOut = $keen->run(true);
        $this->assertEquals("GET test param", $pageOut);
    }
}