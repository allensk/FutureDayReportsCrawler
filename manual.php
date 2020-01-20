<?php

echo <<<_END
<pre>

---
Requirements:
1, Input account, password
2, Input verification code
3, Acquire day reports and store them hierarchically.

Thought:
1, Where information stay?
page is ok, user could see the convincable original day report.

2, what hierarchy?
by account, and by year, by day.

3, Space usage.
If store information in db, the storage is shortest, but the
logic you have to implement is more,
If store original page, the space using will be much.
Adopt a compression method, for less space usage and less logic implement.
the space used is 1.5kb per file after compression, normal 6kb.
6mb could be used for 3 year.

4, Future computation
Pages are not consume much CPU power since their amount is small.

---
To do: 
客户权益 is the valuable and straight money information.

I will save month report as well.

Dec 25, 2019

</pre>

_END;
