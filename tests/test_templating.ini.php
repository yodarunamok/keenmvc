;<?php
;/* idea from http://php.net/manual/en/function.parse-ini-file.php#99474 */
;die();/*

[title]
pattern = "Keen Test"

[.test1]
pattern = "%s1"
replace = "test"
is_html = true


[.test2]
pattern = "test%s%d"
replace = "bogus, two"
is_html = true
replace_contents = false

[#test3]
pattern = "%s<%d"
replace = "test, three"
is_html = false

[#test4]
pattern = "
     <tr>
     <td>test 4a</td>
     <td>test 4b</td>
     </tr>"
is_html = true

[footer]
pattern = "test5 > test4"


;keep this line at the end of the file */
