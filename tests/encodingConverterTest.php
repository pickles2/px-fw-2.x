<?php
/**
 * test for pickles2/px-fw-2.x
 */
class encodingConverterTest extends PHPUnit_Framework_TestCase{
	private $fs;

	public function setup(){
		mb_internal_encoding('UTF-8');
		$this->fs = new tomk79\filesystem();
		require_once(__DIR__.'/libs/simple_html_dom.php');
	}



	/**
	 * 文字コード・改行コード変換テスト
	 */
	public function testEncodingConverter(){
		$detect_order = 'UTF-8,eucJP-win,SJIS-win,EUC-JP,SJIS';

		$output = $this->px_execute(
			'/encodingconverter/.px_execute.php',
			'/'
		);
		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertEquals( preg_match( '/'.preg_quote('<meta charset="Shift_JIS" />', '/').'/s', $output ), 1 );
		$this->assertEquals( preg_match( '/\r\n/s', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote(mb_convert_encoding('<p>美しき日本語ウェブの世界</p>', 'Shift_JIS', 'utf-8'), '/').'/s', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('<p>美しき日本語ウェブの世界</p>', '/').'/s', $output ), 0 );
		// var_dump(mb_detect_encoding($output, $detect_order));
		$this->assertEquals( @strtolower('SJIS-win'), @strtolower(mb_detect_encoding($output, $detect_order)) );


		$output = $this->px_execute(
			'/encodingconverter/.px_execute.php',
			'/common/test.css'
		);
		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertEquals( preg_match( '/'.preg_quote('@charset "EUC-JP"', '/').'/s', $output ), 1 );
		$this->assertEquals( preg_match( '/\r\n/s', $output ), 0 );
		$this->assertEquals( preg_match( '/\n/s', $output ), 1 );
		$this->assertEquals( preg_match( '/\r/s', $output ), 0 );
		$this->assertEquals( preg_match( '/'.preg_quote('<'.'?php', '/').'/s', $output ), 0 );
		$this->assertEquals( preg_match( '/'.preg_quote(mb_convert_encoding('美しき日本語ウェブの世界', 'eucJP-win', 'utf-8'), '/').'/s', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('美しき日本語ウェブの世界', '/').'/s', $output ), 0 );
		// var_dump(mb_detect_encoding($output, $detect_order));
		$this->assertEquals( @strtolower('eucJP-win'), @strtolower(mb_detect_encoding($output, $detect_order)) );


		$output = $this->px_execute(
			'/encodingconverter/.px_execute.php',
			'/common/test.js'
		);
		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertEquals( preg_match( '/\r\n/s', $output ), 0 );
		$this->assertEquals( preg_match( '/\n/s', $output ), 0 );
		$this->assertEquals( preg_match( '/\r/s', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote(mb_convert_encoding('美しき日本語ウェブの世界', 'SJIS-win', 'utf-8'), '/').'/s', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('美しき日本語ウェブの世界', '/').'/s', $output ), 0 );
		// var_dump(mb_detect_encoding($output, $detect_order));
		$this->assertEquals( @strtolower('SJIS-win'), @strtolower(mb_detect_encoding($output, $detect_order)) );


		// 後始末
		// $this->assertTrue( false );
		$output = $this->px_execute( '/encodingconverter/.px_execute.php', '/?PX=clearcache' );
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/encodingconverter/caches/p/' ) );

	}// testEncodingConverter()


	/**
	 * direct と pass のテスト
	 */
	public function testEncodingConverterDirectAndPass(){
		$detect_order = 'UTF-8,eucJP-win,SJIS-win,EUC-JP,SJIS';

		$output = $this->px_execute(
			'/encodingconverter/.px_execute.php',
			'/common/direct/test.css'
		);
		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertEquals( preg_match( '/'.preg_quote('@charset "utf-8"', '/').'/s', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('<'.'?php', '/').'/s', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('美しき日本語ウェブの世界', '/').'/s', $output ), 1 );
		// var_dump(mb_detect_encoding($output, $detect_order));
		$this->assertEquals( @strtolower('utf-8'), @strtolower(mb_detect_encoding($output, $detect_order)) );



		$output = $this->px_execute(
			'/encodingconverter/.px_execute.php',
			'/common/direct/test2.css'
		);
		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertEquals( preg_match( '/\r\n/s', $output ), 0 );
		$this->assertEquals( preg_match( '/\n/s', $output ), 1 );
		$this->assertEquals( preg_match( '/\r/s', $output ), 0 );
		$this->assertEquals( preg_match( '/'.preg_quote('@charset "EUC-JP"', '/').'/s', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('<'.'?php', '/').'/s', $output ), 0 );
		$this->assertEquals( preg_match( '/'.preg_quote(mb_convert_encoding('美しき日本語ウェブの世界', 'eucJP-win', 'utf-8'), '/').'/s', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('美しき日本語ウェブの世界', '/').'/s', $output ), 0 );
		// var_dump(mb_detect_encoding($output, $detect_order));
		$this->assertEquals( @strtolower('eucJP-win'), @strtolower(mb_detect_encoding($output, $detect_order)) );



		$output = $this->px_execute(
			'/encodingconverter/.px_execute.php',
			'/common/pass/test.css'
		);
		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertEquals( preg_match( '/'.preg_quote('@charset "utf-8"', '/').'/s', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('<'.'?php', '/').'/s', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('美しき日本語ウェブの世界', '/').'/s', $output ), 1 );
		// var_dump(mb_detect_encoding($output, $detect_order));
		$this->assertEquals( @strtolower('utf-8'), @strtolower(mb_detect_encoding($output, $detect_order)) );



		$output2 = $this->px_execute(
			'/encodingconverter/.px_execute.php',
			'/common/pass/test2.css.css'
		);
		// var_dump($output);
		$this->assertTrue( $this->common_error( $output2 ) );
		$this->assertEquals( $output2, $output );



		// 後始末
		// $this->assertTrue( false );
		$output = $this->px_execute( '/encodingconverter/.px_execute.php', '/?PX=clearcache' );
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/encodingconverter/caches/p/' ) );

	}// testEncodingConverterDirectAndPass()





	/**
	 * PHPがエラー吐いてないか確認しておく。
	 */
	private function common_error( $output ){
		if( preg_match('/'.preg_quote('Fatal', '/').'/si', $output) ){ return false; }
		if( preg_match('/'.preg_quote('Warning', '/').'/si', $output) ){ return false; }
		if( preg_match('/'.preg_quote('Notice', '/').'/si', $output) ){ return false; }
		return true;
	}


	/**
	 * HTMLコードを受け取り、Simple HTML DOM Parser オブジェクトを返す
	 * @param string $src_html HTMLコード
	 * @return object Simple HTML DOM Parser オブジェクト
	 */
	private function simple_html_dom_parse( $src_html ){
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
	 * `.px_execute.php` を実行し、標準出力値を返す
	 * @param string $path_entry_script エントリースクリプトのパス(testData起点)
	 * @param string $command コマンド(例: `/?PX=clearcache`)
	 * @return string コマンドの標準出力値
	 */
	private function px_execute( $path_entry_script, $command ){
		$output = $this->passthru( [
			'php', __DIR__.'/testData/'.$path_entry_script, $command
		] );
		clearstatcache();
		return $output;
	}

	/**
	 * コマンドを実行し、標準出力値を返す
	 * @param array $ary_command コマンドのパラメータを要素として持つ配列
	 * @return string コマンドの標準出力値
	 */
	private function passthru( $ary_command ){
		set_time_limit(60*10);
		$cmd = array();
		foreach( $ary_command as $row ){
			$param = '"'.addslashes($row).'"';
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
