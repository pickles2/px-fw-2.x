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
		$this->assertEquals( 1, preg_match('/'.preg_quote('<p>このページは /index.html です。</p>', '/').'/s', $output) );

		// 後始末
		$output = $this->passthru( [
			'php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=clearcache'
		] );
		clearstatcache();
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );

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

		// 後始末
		$output = $this->passthru( [
			'php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=clearcache'
		] );
		clearstatcache();
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );

	}

	/**
	 * キャッシュを削除してみるテスト
	 */
	public function testStandardClearcache(){
		// トップページを実行
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/' ,
		] );
		clearstatcache();
		$this->assertTrue( is_dir( __DIR__.'/testData/standard/caches/p/' ) );
		$this->assertTrue( is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/' ) );
		$this->assertTrue( is_file( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/sitemap.array' ) );

		// 後始末
		$output = $this->passthru( [
			'php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=clearcache'
		] );
		clearstatcache();
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/' ) );
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
		$this->assertTrue( is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/applock/' ) );
		$this->assertTrue( is_file( __DIR__.'/testData/standard/px-files/_sys/ram/applock/testAppLock.lock.txt' ) );
		$this->assertEquals( 1, preg_match('/'.preg_quote('testAppLock [FAILED]', '/').'/s', $output) );

		// ロックを解除
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/applock/unlock.html' ,
		] );
		$this->assertTrue( is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/applock/' ) );
		$this->assertFalse( is_file( __DIR__.'/testData/standard/px-files/_sys/ram/applock/testAppLock.lock.txt' ) );

		// 後始末
		$output = $this->passthru( [
			'php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=clearcache'
		] );
		clearstatcache();
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/' ) );
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
