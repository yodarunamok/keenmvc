<?php
/**
 * Created by IntelliJ IDEA.
 * User: Chris Hansen (chris@iviking.org)
 * Date: 5/20/21
 * Time: 2:26 PM
 * To change this template use File | Settings | File Templates.
 */

require_once dirname(__FILE__) . '/../../lib/Keen.php';

class TestRepeatingData extends KeenMVC\Controller
{
    public function get($param = null) {
        $this->addDataTemplate("version", "%01.1f");
        $products = array();
        $products[] = ["product" => "Widget Maker", "version" => 1.5, "status" => "live", "checks" => "a\r\nc", "ready" => "y", "notes" => "makes widgets!"];
        $products[] = ["product" => "Gear Spinner", "version" => 2, "status" => "retired", "checks" => "b", "ready" => "n", "notes" => ""];
        $products[] = ["product" => "Switch Twiddler", "version" => .9, "status" => "", "checks" => "", "ready" => "", "notes" => "early beta"];
        $this->addDataset("products", $products);
        return $this->view->render();
    }
}