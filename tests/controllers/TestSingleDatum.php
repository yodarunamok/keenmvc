<?php
/**
 * Created by IntelliJ IDEA.
 * User: Chris Hansen (chris@iviking.org)
 * Date: 5/20/21
 * Time: 2:26 PM
 * To change this template use File | Settings | File Templates.
 */

require_once dirname(__FILE__) . '/../../lib/Keen.php';

class TestSingleDatum extends KeenMVC\Controller
{
    public function get($param = null) {
        $this->addControllerDatum("#trait", "Love");
        return $this->view->render();
    }
}