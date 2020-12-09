<?php
/**
 * test for pickles2/px-fw-2.x
 */
class headerTest extends PHPUnit\Framework\TestCase{
	private $fs;

	public function setup() : void{
		mb_internal_encoding('UTF-8');
		$this->fs = new tomk79\filesystem();
		require_once(__DIR__.'/libs/simple_html_dom.php');
	}


	/**
	 * $px->header() で HTTPヘッダーを管理するテスト
	 */
	public function testHeader(){
		$cd = realpath('.');
		chdir(__DIR__.'/testData/standard/');

		$px = new picklesFramework2\px('./px-files/');

		// Content-type
		$px->header('Content-type: text/plain');
		$px->header('content-type: image/jpeg');

		// Status Code
		$px->header('HTTP/1.1 200 OK');
		$px->header('HTTP/1.0 404 Not Found');

		// strtolower
		$px->header('X-TEST-HEADER:Test'."\n");
		$px->header('X-TEST-HEADER:Test2', false);

		// replace
		$px->header('X-TEST-HEADER-2: test1-1');
		$px->header('X-TEST-HEADER-2: test1-2',false);
		$px->header('X-TEST-HEADER-2: test1-3',false);
		$px->header('X-TEST-HEADER-2: test1-4',false);
		$px->header('X-TEST-HEADER-2: test2-1');
		$px->header('X-TEST-HEADER-2: test2-2',false);

		$headers = $px->header_list();
		// var_dump($headers);
		$this->assertEquals( $headers[0], 'Content-type: image/jpeg' );
		$this->assertEquals( $headers[1], 'x-test-header: Test' );
		$this->assertEquals( $headers[2], 'x-test-header: Test2' );
		$this->assertEquals( $headers[3], 'x-test-header-2: test2-1' );
		$this->assertEquals( $headers[4], 'x-test-header-2: test2-2' );

		chdir($cd);
		$px->__destruct();// <- required on Windows
		unset($px);

		// 後始末
		$output = $this->px_execute( '/standard/.px_execute.php', '/?PX=clearcache' );
		clearstatcache();
		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/' ) );

	}





	/**
	 * PHPがエラー吐いてないか確認しておく。
	 */
	private function common_error( $output ){
		if( preg_match('/'.preg_quote('Parse error:', '/').'/si', $output) ){ return false; }
		if( preg_match('/'.preg_quote('Fatal error:', '/').'/si', $output) ){ return false; }
		if( preg_match('/'.preg_quote('Warning:', '/').'/si', $output) ){ return false; }
		if( preg_match('/'.preg_quote('Notice:', '/').'/si', $output) ){ return false; }
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
