<?php
/**
 * Created by IntelliJ IDEA.
 * User: Chris Hansen (chris@iviking.org)
 * Date: 3/17/14
 * Time: 3:29 PM
 */
require_once dirname(__FILE__) . '/../../lib/Keen.php';

class TestParam extends KeenMVC\Controller {
	public function get($pathParameter = null) {
		return 'GET test ' . $pathParameter;
	}
}
