<?php
	$page_info = $px->site()->get_current_page_info();
?>

共通コンテンツのテスト。

このページは『<?= htmlspecialchars( $page_info['title'] ); ?>』から出力されています。

