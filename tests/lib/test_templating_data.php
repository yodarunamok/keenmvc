<?php
/**
 * Created by IntelliJ IDEA.
 * User: Chris Hansen (chris@iviking.org)
 * Date: 5/19/21
 * Time: 1:50 PM
 */

$globalSiteData["title.test"] = array("raw_value" => "Keen Test");
$globalSiteData[".test1"] = array("raw_value" => "%s1", "replace" => "test", "is_html" => true);
$globalSiteData[".test2"] = array("raw_value" => "test %s %d", "replace" => ["one", 2], "is_html" => true, "replace_contents" => false);
$globalSiteData["#test3"] = array("raw_value" => "%s<%d", "replace" => ["test", "3"], "is_html" => false);
$globalSiteData["#test4"] = array("raw_value" => "includes/testFourValue.html", "is_html" => true, "type" => "file", "use_include_path" => true);
$globalSiteData["footer"] = array("raw_value" => "test5 > test4");

return $globalSiteData;
