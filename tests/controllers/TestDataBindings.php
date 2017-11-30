<?php
/**
 * Created by IntelliJ IDEA.
 * User: Chris Hansen (chris@iviking.org)
 * Date: 3/17/14
 * Time: 12:07 PM
 */
require_once dirname(__FILE__) . '/../../lib/Keen.php';

class TestDataBindings extends KeenMVC\Controller {
    public function get($param = null) {
        $testBindingData = array(
            'test' => 'test',
            'two' => 2,
            'three' => '3'
        );
        return $this->view->render($testBindingData);
    }
}