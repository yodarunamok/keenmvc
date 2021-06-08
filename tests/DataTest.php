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
        require_once "../lib/Keen.php";
        // initialize variables for tests
        $_SERVER["REQUEST_METHOD"] = "GET";
        $_SERVER["REQUEST_URI"] = "/test_repeating_data/";
        // initialize test output
        $expectedOut = file_get_contents("includes/testRepeatingDataOut.html", true);
        $this->performOutputTest($expectedOut);
    }

    public function testRepeatingDataEmpty()
    {
        // TODO: implement
    }

    public function testKeenModel()
    {
        // TODO: implement
    }

    // This reworks the built-in XML test to test as HTML instead
    public static function assertHtmlStringEqualsHtmlString($expectedXml, $actualXml, $message = '')
    {
        $expected = PHPUnit_Util_XML::load($expectedXml, true);
        $actual   = PHPUnit_Util_XML::load($actualXml, true);

        static::assertEquals($expected, $actual, $message);
    }

    // Private methods
    private function performOutputTest($expectedOut)
    {
        // Don't show xml errors
        libxml_use_internal_errors(true);
        // generate output
        /** @var KeenMVC\App $keen */
        $keen = KeenMVC\App::load("lib/keen_test_environment_config.ini.php");
        $pageOut = $keen->run(true);
        // Create DOM elements
        $pageOutput = new DOMDocument();
        $pageOutput->preserveWhiteSpace = false;
        $pageOutput->loadHTML($pageOut);
        $expectedOutput = new DOMDocument();
        $expectedOutput->preserveWhiteSpace = false;
        $expectedOutput->loadHTML($expectedOut);
        // perform associated test(s)
        $this->assertHtmlStringEqualsHtmlString($expectedOutput->saveHTML(), $pageOutput->saveHTML());
        libxml_clear_errors();
    }
}
