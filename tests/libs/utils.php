<?php
namespace picklesFramework2\tests;

class utils {

	/**
	 * HTMLコードを受け取り、Simple HTML DOM Parser オブジェクトを返す
	 * @param string $src_html HTMLコード
	 * @return object Simple HTML DOM Parser オブジェクト
	 */
	public function simple_html_dom_parse( $src_html ){
		require_once(__DIR__.'/simple_html_dom.php');
		$simple_html_dom = \picklesFramework2\tests\str_get_html(
			$src_html ,
			true, // $lowercase
			true, // $forceTagsClosed
			DEFAULT_TARGET_CHARSET, // $target_charset
			false, // $stripRN
			DEFAULT_BR_TEXT, // $defaultBRText
			DEFAULT_SPAN_TEXT // $defaultSpanText
		);
		return $simple_html_dom;
	}

	/**
	 * PHPがエラー吐いてないか確認しておく。
	 */
	public function common_error( $output ){
		if( preg_match('/'.preg_quote('Parse error:', '/').'/si', $output) ){ return false; }
		if( preg_match('/'.preg_quote('Fatal error:', '/').'/si', $output) ){ return false; }
		if( preg_match('/'.preg_quote('Warning:', '/').'/si', $output) ){ return false; }
		if( preg_match('/'.preg_quote('Notice:', '/').'/si', $output) ){ return false; }
		return true;
	}


	/**
	 * `.px_execute.php` を実行し、標準出力値を返す
	 * @param string $path_entry_script エントリースクリプトのパス(testData起点)
	 * @param string $command コマンド(例: `/?PX=clearcache`)
	 * @return string コマンドの標準出力値
	 */
	public function px_execute( $path_entry_script, $command ){
		$output = $this->passthru( [
			'php', __DIR__.'/../testData/'.$path_entry_script, $command
		] );
		clearstatcache();
		return $output;
	}

	/**
	 * コマンドを実行し、標準出力値を返す
	 * @param array $ary_command コマンドのパラメータを要素として持つ配列
	 * @return string コマンドの標準出力値
	 */
	public function passthru( $ary_command ){
		set_time_limit(60*10);
		$cmd = array();
		foreach( $ary_command as $row ){
			$param = escapeshellcmd($row);
			array_push( $cmd, $param );
		}
		$cmd = implode( ' ', $cmd );
		ob_start();
		passthru( $cmd );
		$bin = ob_get_clean();
		set_time_limit(30);
		return $bin;
	}

}