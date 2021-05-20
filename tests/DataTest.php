<?php
/**
 * Created by IntelliJ IDEA.
 * User: Chris Hansen (chris@iviking.org)
 * Date: 11/27/17
 * Time: 2:00 PM
 */


class DataTest extends PHPUnit_Framework_TestCase
{
    public function testDoTemplating()
    {
        require_once "../lib/Keen.php";
        // initialize variables for tests
        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/test_templating/";
        // initialize test output
        $expectedOut = file_get_contents("includes/testDoTemplatingOut.html", true);
        $this->performOutputTest($expectedOut);
    }

    public function testSingleDatum()
    {
        require_once "../lib/Keen.php";
        // initialize variables for tests
        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/test_single_datum/";
        // initialize test output
        $expectedOut = file_get_contents("includes/testSingleDatumOut.html", true);
        $this->performOutputTest($expectedOut);
    }

    public function testRepeatingData()
    {
        // TODO: implement
    }

    public function testKeenModel()
    {
        // TODO: implement
    }

    // Private methods
    private function performOutputTest($expectedOut)
    {
        // generate output
        /** @var KeenMVC\App $keen */
        $keen = KeenMVC\App::load("lib/keen_test_environment_config.ini.php");
        $pageOut = $keen->run(true);
        // Create DOM elements
        libxml_use_internal_errors(true);
        $pageOutput = new DOMDocument();
        $pageOutput->preserveWhiteSpace = false;
        $pageOutput->loadHTML($pageOut);
        $expectedOutput = new DOMDocument();
        $expectedOutput->preserveWhiteSpace = false;
        $expectedOutput->loadHTML($expectedOut);
        // perform associated test(s)
        $this->assertXmlStringEqualsXmlString($expectedOutput->saveHTML(), $pageOutput->saveHTML());
        libxml_clear_errors();
    }
}
