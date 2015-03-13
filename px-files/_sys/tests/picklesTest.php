<?php
/**
 * test for tomk79\PxFW-2.x
 * 
 * $ cd (project dir)
 * $ ./vendor/phpunit/phpunit/phpunit picklesTest "./px-files/_sys/tests/picklesTest.php"
 */

class picklesTest extends PHPUnit_Framework_TestCase{
	private $fs;

	public function setup(){
		mb_internal_encoding('UTF-8');
		$this->fs = new tomk79\filesystem();
	}


	/**
	 * 普通に実行してみるテスト
	 */
	public function testStandard(){
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/' ,
		] );
		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertEquals( 1, preg_match('/'.preg_quote('<h2>テストページ</h2><p>このページは /index.html です。</p>', '/').'/s', $output) );
		$this->assertEquals( 0, preg_match('/'.preg_quote('<span id="hash_', '/').'/s', $output) );

		// 後始末
		$output = $this->passthru( [
			'php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=clearcache'
		] );

		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );

	}

	/**
	 * アプリケーションロックのテスト
	 */
	public function testAppLock(){
		// ロックする
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/applock/lock.html' ,
		] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/applock/' ) );
		$this->assertTrue( is_file( __DIR__.'/testData/standard/px-files/_sys/ram/applock/testAppLock.lock.txt' ) );
		$this->assertEquals( 1, preg_match('/'.preg_quote('testAppLock [SUCCESS]', '/').'/s', $output) );

		// ロックされてて動かない
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/applock/lock.html' ,
		] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/applock/' ) );
		$this->assertTrue( is_file( __DIR__.'/testData/standard/px-files/_sys/ram/applock/testAppLock.lock.txt' ) );
		$this->assertEquals( 1, preg_match('/'.preg_quote('testAppLock [FAILED]', '/').'/s', $output) );

		// ロックを解除
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/applock/unlock.html' ,
		] );
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/applock/' ) );
		$this->assertFalse( is_file( __DIR__.'/testData/standard/px-files/_sys/ram/applock/testAppLock.lock.txt' ) );

		// 後始末
		$output = $this->passthru( [
			'php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=clearcache'
		] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/' ) );
	}

	/**
	 * $px->site()->set_page_info() を実行してみるテスト
	 */
	public function testStandardSetPageInfo(){
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/site/set_page_info.html' ,
		] );
		clearstatcache();

		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertEquals( 'naked', $output );


		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/site/no_page.html' ,
		] );
		clearstatcache();

		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertEquals( 'no_page.html', $output );

		// 後始末
		$output = $this->passthru( [
			'php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=clearcache'
		] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/' ) );
	}


	/**
	 * パブリッシュしてみるテスト
	 */
	public function testStandardPublish(){
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=publish.run' ,
		] );
		clearstatcache();

		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( is_dir( __DIR__.'/testData/standard/caches/p/' ) );
		$this->assertTrue( is_file( __DIR__.'/testData/standard/px-files/_sys/ram/publish/htdocs/index.html' ) );

		$page_src = $this->fs->read_file( __DIR__.'/testData/standard/px-files/_sys/ram/publish/htdocs/sample_pages/page1/template.html' );
		$this->assertEquals( preg_match( '/'.preg_quote('<!DOCTYPE html>', '/').'/s', $page_src ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('<p>このページは『共通点コンテンツからの出力A』から出力されています。</p>', '/').'/s', $page_src ), 1 );

		$page_src = $this->fs->read_file( __DIR__.'/testData/standard/px-files/_sys/ram/publish/htdocs/sample_pages/page3/3.html' );
		$this->assertEquals( preg_match( '/'.preg_quote('<!DOCTYPE html>', '/').'/s', $page_src ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('<p>このページは『共通点コンテンツからの出力B』から出力されています。</p>', '/').'/s', $page_src ), 1 );

		$this->assertFalse( is_file( __DIR__.'/testData/standard/px-files/_sys/ram/publish/htdocs/templates.ignore/template.html' ) );
		$this->assertFalse( is_file( __DIR__.'/testData/standard/px-files/_sys/ram/publish/htdocs/templates.ignore/template.html.md' ) );

		$this->assertTrue( is_file( __DIR__.'/testData/standard/px-files/_sys/ram/publish/htdocs/dynamicPath/index.html' ) );
		$this->assertTrue( is_file( __DIR__.'/testData/standard/px-files/_sys/ram/publish/htdocs/dynamicPath/dp_test/index.html' ) );
		$this->assertTrue( is_file( __DIR__.'/testData/standard/px-files/_sys/ram/publish/htdocs/dynamicPath/dp_test/a.html' ) );

		$this->assertTrue( is_file( __DIR__.'/testData/standard/caches/c/contents/path_files_cache_files/test.inc' ) );
		$this->assertTrue( is_file( __DIR__.'/testData/standard/caches/c/contents/path_files_cache_files/test2/test2.inc' ) );

		// ログファイルを確認
		$this->assertTrue( is_file( __DIR__.'/testData/standard/px-files/_sys/ram/publish/publish_log.csv' ) );
		$publish_log = $this->fs->read_csv( __DIR__.'/testData/standard/px-files/_sys/ram/publish/publish_log.csv' );
		$this->assertEquals( $publish_log[0][0], 'datetime' );
		$this->assertEquals( $publish_log[0][1], 'path' );
		$this->assertEquals( $publish_log[0][2], 'proc_type' );
		$this->assertEquals( $publish_log[0][3], 'status_code' );
		$this->assertNull( $publish_log[0][4] );
		$this->assertTrue( is_file( __DIR__.'/testData/standard/px-files/_sys/ram/publish/alert_log.csv' ) );
		$alert_log = $this->fs->read_csv( __DIR__.'/testData/standard/px-files/_sys/ram/publish/alert_log.csv' );
		$this->assertEquals( $alert_log[0][0], 'datetime' );
		$this->assertEquals( $alert_log[0][1], 'path' );
		$this->assertEquals( $alert_log[0][2], 'error_message' );
		$this->assertNull( $alert_log[0][3] );

		// 後始末
		// $this->assertTrue( false );
		$output = $this->passthru( [
			'php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=clearcache'
		] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );

	}

	/**
	 * キャッシュを削除してみるテスト
	 * @depends testStandardPublish
	 */
	public function testStandardClearcache(){
		// トップページを実行
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/' ,
		] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( is_dir( __DIR__.'/testData/standard/caches/p/' ) );
		$this->assertTrue( is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/' ) );
		$this->assertTrue( is_file( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/sitemap.array' ) );

		// 後始末
		$output = $this->passthru( [
			'php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=clearcache'
		] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/' ) );
	}






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
	 * コマンドを実行し、標準出力値を返す
	 * @param array $ary_command コマンドのパラメータを要素として持つ配列
	 * @return string コマンドの標準出力値
	 */
	private function passthru( $ary_command ){
		$cmd = array();
		foreach( $ary_command as $row ){
			$param = '"'.addslashes($row).'"';
			array_push( $cmd, $param );
		}
		$cmd = implode( ' ', $cmd );
		ob_start();
		passthru( $cmd );
		$bin = ob_get_clean();
		return $bin;
	}

}
