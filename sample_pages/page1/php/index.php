<?php
/**
 * Pickles 2 上に動的なコンテンツを実装するサンプル
 */


// -----------------------------------
// require autoload.php
if( is_file('../../../vendor/autoload.php') ){
	// is preview
	require_once('../../../vendor/autoload.php');
}elseif( is_file('../../../../../vendor/autoload.php') ){
	// is finalized
	require_once('../../../../../vendor/autoload.php');
}


// -----------------------------------
// テンプレートを取得する
$tpl = '';
if( is_file( '../../../.px_execute.php' ) ){
	// is preview
	// .px_execute.php が存在する場合は、プレビュー環境だと判断。
	ob_start();
	passthru(implode( ' ', array(
		'php',
		'../../../.px_execute.php',
		'/sample_pages/page1/php/index_files/template.html'
	) ));
	$tpl = ob_get_clean();
}else{
	// is finalized
	// .px_execute.php が存在しなければ、パブリッシュ後の実行であると判断。
	$tpl = file_get_contents( __DIR__.'/index_files/template.html' );
}


// -----------------------------------
// 出力するHTMLコンテンツを生成
// あるいは、動的な処理を実装する
$content = '';
$content .= '<p>テンプレート中の文字列 <code>{$main_contents}</code> を、HTMLコードに置き換えます。</p>'."\n";
$content .= '<p>アプリケーションの動的な処理を実装することもできます。</p>'."\n";


// -----------------------------------
// テンプレートにHTMLをバインドする
$tpl = str_replace( '{$main_contents}', $content, $tpl );


// -----------------------------------
// 出力して終了する
echo $tpl;
exit();
