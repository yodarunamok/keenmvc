<?php
/**
 * Created by IntelliJ IDEA.
 * User: Chris Hansen (chris@iviking.org)
 * Date: 1/25/14
 * Time: 12:35 PM
 */
require_once '../lib/keen.php';
$keen = Keen::instance();

echo "<pre>\n";
var_dump(explode('/', trim(preg_replace('/\/+/', '/', '//test//test//'), " \t\n\r\0\x0B/")));
echo "</pre>\n";

echo "<pre>\n";
var_dump($_SERVER);
echo "</pre>\n";