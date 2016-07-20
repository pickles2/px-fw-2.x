<?php
/**
 * test for pickles2/px-fw-2.x
 */
class picklesTest extends PHPUnit_Framework_TestCase{
	private $fs;

	public function setup(){
		mb_internal_encoding('UTF-8');
		$this->fs = new tomk79\filesystem();
		require_once(__DIR__.'/libs/simple_html_dom.php');
	}


	/**
	 * 普通にインスタンス化して実行してみるテスト
	 */
	public function testStandard(){
		$cd = realpath('.');
		chdir(__DIR__.'/testData/standard/');

		$px = new picklesFramework2\px('./px-files/');
		$toppage_info = $px->site()->get_page_info('');
		// var_dump($toppage_info);
		$this->assertEquals( $toppage_info['title'], '<HOME>' );
		$this->assertEquals( $toppage_info['path'], '/index.html' );

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
	 * 普通にコマンドラインから実行してみるテスト
	 * @depends testStandard
	 */
	public function testCLIStandard(){
		$output = $this->px_execute( '/standard/.px_execute.php', '/' );
		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertEquals( 1, preg_match('/'.preg_quote('<h2>テストページ</h2><p>このページは /index.html です。</p>', '/').'/s', $output) );
		$this->assertEquals( 0, preg_match('/'.preg_quote('<span id="hash_', '/').'/s', $output) );
		$this->assertEquals( 1, preg_match('/'.preg_quote('<p>Timezone = Asia/Tokyo</p>', '/').'/s', $output) );


		$output = $this->px_execute( '/prevnext/.px_execute.php', '/' );
		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertEquals( 1, preg_match('/'.preg_quote('<p>Timezone = UTC</p>', '/').'/s', $output) );


		// 後始末
		$output = $this->px_execute( '/standard/.px_execute.php', '/?PX=clearcache' );
		$output = $this->px_execute( '/prevnext/.px_execute.php', '/?PX=clearcache' );

		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/' ) );

	}

	/**
	 * アプリケーションロックのテスト
	 * @depends testCLIStandard
	 */
	public function testAppLock(){
		// ロックする
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/applock/lock.html' ,
		] );
		clearstatcache();
		// var_dump($output);
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
		$output = $this->px_execute( '/standard/.px_execute.php', '/?PX=clearcache' );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/' ) );
	}

	/**
	 * サイトマップCSVの解釈のテスト
	 */
	public function testSitemap(){
		$cd = realpath('.');
		chdir(__DIR__.'/testData/standard/');

		$px = new picklesFramework2\px('./px-files/');
		$toppage_info = $px->site()->get_page_info('');
		// var_dump($toppage_info);
		$this->assertEquals( count($toppage_info), 16 );
		$this->assertEquals( $toppage_info['title'], '<HOME>' );
		$this->assertEquals( $toppage_info['path'], '/index.html' );
		$this->assertNull( @$toppage_info[''] );
		$this->assertNull( @$toppage_info['start_with_no_asterisk'] );

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
	 * キャッシュの振る舞いのテスト1
	 */
	public function testCachesBehavior1(){
		$cd = realpath('.');
		chdir(__DIR__.'/testData/caches_behavior/make/');

		$px = new picklesFramework2\px('./px-files/');
		$toppage_info = $px->site()->get_page_info('');
		// var_dump($toppage_info);
		clearstatcache();
		$this->assertTrue( is_dir( './caches/' ) );
		$this->assertTrue( is_dir( './px-files/_sys/' ) );
		$this->assertTrue( is_dir( './px-files/_sys/ram/' ) );
		$this->assertTrue( is_dir( './px-files/_sys/ram/applock/' ) );
		$this->assertTrue( is_dir( './px-files/_sys/ram/caches/' ) );
		$this->assertTrue( is_dir( './px-files/_sys/ram/data/' ) );
		$this->assertTrue( is_dir( './px-files/_sys/ram/publish/' ) );

		chdir($cd);
		$px->__destruct();// <- required on Windows
		unset($px);

		// 後始末
		$output = $this->px_execute( '/caches_behavior/make/.px_execute.php', '/?PX=clearcache' );
		clearstatcache();
		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/caches_behavior/make/caches/p/' ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/caches_behavior/make/px-files/_sys/ram/caches/sitemaps/' ) );
		$this->assertTrue( $this->fs->rm( __DIR__.'/testData/caches_behavior/make/caches/' ) );
		$this->assertTrue( $this->fs->rm( __DIR__.'/testData/caches_behavior/make/px-files/_sys/' ) );

	}

	/**
	 * キャッシュの振る舞いのテスト2
	 */
	public function testCachesBehavior2(){
		$cd = realpath('.');
		chdir(__DIR__.'/testData/caches_behavior/notmake/');

		$px = new picklesFramework2\px('./px-files/');
		$toppage_info = $px->site()->get_page_info('');
		// var_dump($toppage_info);
		clearstatcache();
		$this->assertTrue( !is_dir( './caches/' ) );
		$this->assertTrue( is_dir( './px-files/_sys/' ) );
		$this->assertTrue( is_dir( './px-files/_sys/ram/' ) );
		$this->assertTrue( is_dir( './px-files/_sys/ram/applock/' ) );
		$this->assertTrue( is_dir( './px-files/_sys/ram/caches/' ) );
		$this->assertTrue( is_dir( './px-files/_sys/ram/data/' ) );
		$this->assertTrue( is_dir( './px-files/_sys/ram/publish/' ) );

		chdir($cd);
		$px->__destruct();// <- required on Windows
		unset($px);

		// 後始末
		$output = $this->px_execute( '/caches_behavior/notmake/.px_execute.php', '/?PX=clearcache' );
		clearstatcache();
		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/caches_behavior/notmake/caches/p/' ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/caches_behavior/notmake/px-files/_sys/ram/caches/sitemaps/' ) );
		$this->assertTrue( $this->fs->rm( __DIR__.'/testData/caches_behavior/notmake/px-files/_sys/' ) );

	}

	/**
	 * $px->site()->set_page_info() を実行してみるテスト
	 * @depends testCLIStandard
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
		$output = $this->px_execute( '/standard/.px_execute.php', '/?PX=clearcache' );
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/' ) );
	}

	/**
	 * $px->mk_link() を実行してみるテスト
	 * @depends testCLIStandard
	 */
	public function testStandardMkLink(){
		$output = $this->px_execute(
			'/standard/.px_execute.php',
			'/mk_link/'
		);

		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertEquals( '<a href="/" class="current">&lt;HOME&gt;</a><a href="/" class="current"><link0></a><a href="/" class="current"><link1></a><a href="/" class="current"><link2></a><a href="/" class="current">&lt;link3&gt;</a>', $output );


		// 後始末
		$output = $this->px_execute( '/standard/.px_execute.php', '/?PX=clearcache' );
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/' ) );
	}

	/**
	 * paths_enable_sitemap のテスト
	 * @depends testCLIStandard
	 */
	public function testPathsEnableSitemap(){
		$output = $this->px_execute(
			'/standard/.px_execute.php',
			'/common/styles/sitemap_loaded.css'
		);

		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertEquals( preg_match('/failed/', $output), 0 );

		$output = $this->px_execute(
			'/standard/.px_execute.php',
			'/common/styles/sitemap_not_loaded.css'
		);

		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertEquals( preg_match('/failed/', $output), 0 );


		// 後始末
		$output = $this->px_execute( '/standard/.px_execute.php', '/?PX=clearcache' );
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/' ) );
	}

	/**
	 * ダイナミックパスを実行してみるテスト
	 * @depends testCLIStandard
	 */
	public function testStandardDynamicPath(){
		$output = $this->px_execute(
			'/standard/.px_execute.php',
			'/dynamicPath/'
		);
		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$html = $this->simple_html_dom_parse($output);
		$this->assertEquals( $html->find('.theme_megafooter_navi li a[href=/dynamicPath/]')[0]->class, 'current' );

		$output = $this->px_execute(
			'/standard/.px_execute.php',
			'/dynamicPath/test1.html'
		);
		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$html = $this->simple_html_dom_parse($output);
		$this->assertEquals( $html->find('.theme_megafooter_navi li a[href=/dynamicPath/]')[0]->class, 'current' );

		$output = $this->px_execute(
			'/standard/.px_execute.php',
			'/dynamicPath/dummy_file_name.html'
		);
		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$html = $this->simple_html_dom_parse($output);
		$this->assertEquals( $html->find('.theme_megafooter_navi li a[href=/dynamicPath/]')[0]->class, 'current' );

		$output = $this->px_execute(
			'/standard/.px_execute.php',
			'/dynamicPath---/test1.html'
		);
		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$html = $this->simple_html_dom_parse($output);
		$this->assertFalse( $html->find('.theme_megafooter_navi li a[href=/dynamicPath/]')[0]->class );


		// 後始末
		$output = $this->px_execute( '/standard/.px_execute.php', '/?PX=clearcache' );
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/' ) );
	}



	/**
	 * 遠いディレクトリからコマンドラインで実行してみるテスト
	 * @depends testCLIStandard
	 */
	public function testStandardCmdExecByFarDirectory(){
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/contents/get_path_docroot.html' ,
		] );
		clearstatcache();

		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertEquals( '<pre>'.$this->fs->normalize_path( __DIR__.'/testData/standard/' ).'</pre>', $output );

		$cd = realpath('.');
		chdir(__DIR__.'/');
		$output = $this->passthru( [
			'php',
			'./testData/standard/.px_execute.php' ,
			'/contents/get_path_docroot.html' ,
		] );
		clearstatcache();
		chdir($cd);

		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertEquals( '<pre>'.$this->fs->normalize_path( __DIR__.'/testData/standard/' ).'</pre>', $output );

		$cd = realpath('.');
		chdir(__DIR__.'/testData/standard/');
		$output = $this->passthru( [
			'php',
			'./.px_execute.php' ,
			'/contents/get_path_docroot.html' ,
		] );
		clearstatcache();
		chdir($cd);

		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertEquals( '<pre>'.$this->fs->normalize_path( __DIR__.'/testData/standard/' ).'</pre>', $output );


		// 後始末
		$output = $this->px_execute( '/standard/.px_execute.php', '/?PX=clearcache' );
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/' ) );
	}


	/**
	 * コンフィグ項目をコマンドラインオプションで上書きするテスト
	 * @depends testCLIStandard
	 */
	public function testOverrideConfig(){

		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=api.get.config' ,
		] );
		clearstatcache();
		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output->commands->php, 'php' );
		$this->assertNull( @$output->commands->dummy );

		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'--command-php', '/path/to/command/php',
			'-c', '/path/to/file/php.ini',
			'/?PX=api.get.config' ,
		] );
		clearstatcache();
		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output->commands->php, '/path/to/command/php' );
		$this->assertEquals( $output->path_phpini, '/path/to/file/php.ini' );

		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'--command-php', 'C:\path\to\command\php.exe',
			'-c', 'C:\path\to\file\php.ini',
			'/?PX=api.get.config' ,
		] );
		clearstatcache();
		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output->commands->php, ($this->fs->is_windows() ? addslashes('C:\path\to\command\php.exe') : 'C:\path\to\command\php.exe') );
		$this->assertEquals( $output->path_phpini, ($this->fs->is_windows() ? addslashes('C:\path\to\file\php.ini') : 'C:\path\to\file\php.ini') );
			// ↑Windowsでは、エスケープされたバックスラッシュが、受け取り側で解決されず倍に増えてる(?) ような、問題がある。
			// 　バックスラッシュが増えてても、スラッシュで指定していても、この用途においては問題にならないようなので、深追いしない。
			// 　なので、このテストは本来あるべきではない分岐を使い、変な感じの実装になっている。



		// 後始末
		$output = $this->px_execute( '/standard/.px_execute.php', '/?PX=clearcache' );
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/' ) );
	}


	/**
	 * 最小構成のサイトマップCSV
	 * @depends testCLIStandard
	 */
	public function testSitemapMinimum(){

		$output = $this->px_execute(
			'/sitemap_min/.px_execute.php',
			'/'
		);
		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertEquals( preg_match( '/\<span\>FAILED\<\/span\>/', $output ), 0 );


		// 後始末
		// $this->assertTrue( false );
		$output = $this->px_execute( '/sitemap_min/.px_execute.php', '/?PX=clearcache' );
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/sitemap_min/caches/p/' ) );

	} // testSitemapMinimum()





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
