<?php
$page_info = $px->site()->get_current_page_info();
?>

このページは、共通のHTMLで出力するサンプルです。

サイトマップの `content` 欄に入力したパスのコンテンツファイルと紐付けられます。

このページは、<strong><?= htmlspecialchars( $page_info['title'] ); ?></strong> に関連付けられて表示されています。

