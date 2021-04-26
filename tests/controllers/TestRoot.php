<?php
/**
 * Created by IntelliJ IDEA.
 * User: Chris Hansen (chris@iviking.org)
 * Date: 2/28/14
 * Time: 12:12 PM
 */
require_once dirname(__FILE__) . '/../../lib/Keen.php';

class TestRoot extends KeenMVC\Controller {
	public function get($param = null) {
		return 'GET root';
	}
}
