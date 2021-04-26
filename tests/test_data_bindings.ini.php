;<?php
;/* idea from http://php.net/manual/en/function.parse-ini-file.php#99474 */
;die();/*

[title]
data = "Keen Test"

[.test1]
data = "%s1"
args = "test"
is_html = true


[.test2]
data = "test%s%d"
args = "bogus, two"
is_html = true
replace_contents = false

[#test3]
data = "%s<%d"
args = "test, three"
is_html = false

[#test4]
data = "
     <tr>
     <td>test 4a</td>
     <td>test 4b</td>
     </tr>"
is_html = true

[footer]
data = "test5 > test4"


;keep this line at the end of the file */
