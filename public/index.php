<?php
/**
 * Created by IntelliJ IDEA.
 * User: Chris Hansen (chris@iviking.org)
 * Date: 1/25/14
 * Time: 12:35 PM
 */
require_once '../lib/Keen.php';

/** @var KeenMVC\App $keen */
$keen = KeenMVC\App::load();
$keen->run();

echo 'woot';