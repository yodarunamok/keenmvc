<?php
/**
 * Created by IntelliJ IDEA.
 * User: Chris Hansen (chris@iviking.org)
 * Date: 2/3/14
 * Time: 3:04 PM
 */

class SingletonTest extends PHPUnit_Framework_TestCase {
    public function testInstantiateSingleton () {
        require_once '../lib/keen.php';
        // initialize variables for tests
        /** @var Keen $keen */
        $keen = Keen::instance('keen_test_config.ini');
        /** @var Keen $keen2 */
        $keen2 = Keen::instance('keen_test_config.ini');
        // perform associated test(s)
        $this->assertNotEquals(false, $keen);
        $this->assertInstanceOf('Singleton', $keen);
        $this->assertSame($keen, $keen2);
    }
}