

これは、[page3-2](./2.html) のactor(役者) <strong><?= htmlspecialchars($px->site()->get_current_page_info('title')); ?></strong> です。

- [role](./2.html)
- [actor1](./2-actor1.html)
- [actor2](./2-actor2.html)

<pre><?php
$role = $px->site()->get_role();
var_dump($role);
?></pre>
