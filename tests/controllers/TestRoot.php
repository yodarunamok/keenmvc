<?php
/**
 * Created by IntelliJ IDEA.
 * User: Chris Hansen (chris@iviking.org)
 * Date: 2/28/14
 * Time: 12:12 PM
 */
require_once dirname(__FILE__) . '/../../lib/Keen.php';

class TestRoot extends KeenController {
    public function get() {
        return 'GET root';
    }
}