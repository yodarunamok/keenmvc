<?php
/**
 * Created by IntelliJ IDEA.
 * User: Chris Hansen (chris@iviking.org)
 * Date: 11/27/17
 * Time: 2:00 PM
 */


class DataBindingsTest extends PHPUnit_Framework_TestCase
{
    public function testGetTestDataBindings()
    {
        require_once '../lib/Keen.php';
        // initialize variables for tests
        /** @var KeenMVC\App $keen */
        $keen = KeenMVC\App::load('keen_test_config.ini');
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/test/data_bindings/';
        // initialize test output
        $expectedOut = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head><title>Keen Test</title></head>
<body>
    test0 <span class="test1">test1</span>
    <p><span class="test2">This should <strong>not</strong> be removed!test2</span></p>
    <p id="test3">test&lt;3</p>
    <table id="test4">
        <tr>
            <td>test 4a</td>
            <td>test 4b</td>
        </tr>
    </table><footer>test5 > test4</footer></body>
</html>
HTML;
        // generate output
        $pageOut = $keen->run(true);
        // Create DOM elements
        $pageOutput = new DOMDocument();
        $pageOutput->preserveWhiteSpace = false;
        $pageOutput->loadHTML($pageOut);
        $expectedOutput = new DOMDocument();
        $expectedOutput->preserveWhiteSpace = false;
        $expectedOutput->loadHTML($expectedOut);
        // perform associated test(s)
        $this->assertXmlStringEqualsXmlString($expectedOutput->saveHTML(), $pageOutput->saveHTML());
    }
}
