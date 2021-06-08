;<?php
;/* idea from http://php.net/manual/en/function.parse-ini-file.php#99474 */
;die();/*

[routes]
; route declarations are below (ensure that each has a leading and trailing "/")
/ = "TestRoot"
/test/ = "TestTest"
/test/@/ = "TestParam"

/test_templating/ = "TestTemplating"
/test_single_datum/ = "TestSingleDatum"
/test_repeating_data/ = "TestRepeatingData"

[aliases]
; route aliases (or names) are below
root = "/"
test = "/test/"


;keep this line at the end of the file */
