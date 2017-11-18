<?php
/**
 * Created by IntelliJ IDEA.
 * User: Chris Hansen (chris@iviking.org)
 * Date: 1/2/15
 * Time: 10:49 AM
 */

class KeenDefault extends KeenMVC\Controller {
    public function get() {
        return $this->view->parse();
    }
}