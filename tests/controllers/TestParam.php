<?php
/**
 * Created by IntelliJ IDEA.
 * User: Chris Hansen (chris@iviking.org)
 * Date: 3/17/14
 * Time: 3:29 PM
 */
require_once dirname(__FILE__) . '/../../lib/Keen.php';

class TestParam extends KeenController {
    public function get() {
        return 'GET test ' . $this->pathParameters['@test'];
    }
}