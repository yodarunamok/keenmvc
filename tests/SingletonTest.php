<?php
/**
 * Created by IntelliJ IDEA.
 * User: Chris Hansen (chris@iviking.org)
 * Date: 2/3/14
 * Time: 3:04 PM
 */

class SingletonTest extends PHPUnit_Framework_TestCase
{
    public function testInstantiateSingleton()
    {
        require_once '../lib/Keen.php';
        // initialize variables for tests
        /** @var KeenMVC\App $keen */
        $keen = KeenMVC\App::instance('keen_test_config.ini');
        /** @var KeenMVC\App $keen2 */
        $keen2 = KeenMVC\App::instance('keen_test_config.ini');
        // perform associated test(s)
        $this->assertNotEquals(false, $keen);
        $this->assertInstanceOf('KeenMVC\\Singleton', $keen);
        $this->assertSame($keen, $keen2);
    }
}