;<?php
;/* idea from http://php.net/manual/en/function.parse-ini-file.php#99474 */
;die();/*

[title.test]
raw_value = "Keen Test"

[.test1]
raw_value = "%s1"
replace = "test"
is_html = true


[.test2]
raw_value = "test%s%d"
replace = "bogus, two"
is_html = true
replace_contents = false

[#test3]
raw_value = "%s<%d"
replace = "test, three"
is_html = false

[#test4]
raw_value = "
     <tr>
     <td>test 4a</td>
     <td>test 4b</td>
     </tr>"
is_html = true

[footer]
raw_value = "test5 > test4"


;keep this line at the end of the file */
