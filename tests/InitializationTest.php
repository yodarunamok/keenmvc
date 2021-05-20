<?php
/**
 * Created by IntelliJ IDEA.
 * User: Chris Hansen (chris@iviking.org)
 * Date: 2/3/14
 * Time: 3:04 PM
 */

class InitializationTest extends PHPUnit_Framework_TestCase
{
    public function testInstantiateSingleton()
    {
        require_once "../lib/Keen.php";
        // initialize variables for tests
        /** @var KeenMVC\App $keen */
        $keen = KeenMVC\App::load("lib/keen_test_environment_config.ini.php");
        /** @var KeenMVC\App $keen2 */
        $keen2 = KeenMVC\App::load("lib/keen_test_environment_config.ini.php");
        // perform associated test(s)
        $this->assertNotEquals(false, $keen);
        $this->assertInstanceOf("KeenMVC\\Singleton", $keen);
        $this->assertSame($keen, $keen2);
    }

    // TODO: Need to test in here for how things are handled when required settings are missing
}