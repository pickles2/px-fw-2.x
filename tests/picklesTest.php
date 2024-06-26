<?php
/**
 * test for pickles2/px-fw-2.x
 */
class picklesTest extends PHPUnit\Framework\TestCase{
	private $fs;
	private $utils;

	public function setup() : void{
		mb_internal_encoding('UTF-8');
		$this->fs = new tomk79\filesystem();
		require_once(__DIR__.'/libs/utils.php');
		$this->utils = new \picklesFramework2\tests\utils();
	}


	/**
	 * 普通にコマンドラインから実行してみるテスト
	 */
	public function testCLIStandard(){
		$output = $this->utils->px_execute( '/standard/.px_execute.php', '/' );
		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertEquals( 1, preg_match('/'.preg_quote('<h2>テストページ</h2><p>このページは /index.html です。</p>', '/').'/s', $output) );
		$this->assertEquals( 0, preg_match('/'.preg_quote('<span id="hash_', '/').'/s', $output) );
		$this->assertEquals( 1, preg_match('/'.preg_quote('<p>Timezone = Asia/Tokyo</p>', '/').'/s', $output) );


		$output = $this->utils->px_execute( '/prevnext/.px_execute.php', '/' );
		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertEquals( 1, preg_match('/'.preg_quote('<p>Timezone = UTC</p>', '/').'/s', $output) );


		// 後始末
		$output = $this->utils->px_execute( '/standard/.px_execute.php', '/?PX=clearcache' );
		$output = $this->utils->px_execute( '/prevnext/.px_execute.php', '/?PX=clearcache' );

		clearstatcache();
		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/' ) );

	}

	/**
	 * コマンドラインからPOSTメソッドをエミュレートするテスト
	 */
	public function testCLIPostMethod(){

		// POSTメソッドをエミュレートするテスト
		$output = $this->utils->passthru( [
			PHP_BINARY,
			__DIR__.'/testData/standard/.px_execute.php',
			'-u', 'Mozilla',
			'--method', 'post',
			'--body', 'test=post_test&test2=post_test2',
			'/http_methods/index.html?test=get_test'
		] );
		clearstatcache();
		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertEquals( 1, preg_match('/'.preg_quote('<p>method = POST</p>', '/').'/s', $output) );
		$this->assertEquals( 1, preg_match('/'.preg_quote('<p>$_GET[\'test\'] = get_test</p>', '/').'/s', $output) );
		$this->assertEquals( 1, preg_match('/'.preg_quote('<p>$_POST[\'test\'] = post_test</p>', '/').'/s', $output) );

		// POSTメソッドで request body をファイルから送ってエミュレートするテスト
		$this->fs->save_file(__DIR__.'/post_request_body.txt', 'test=post_test_file&test2=post_test2_file');
		clearstatcache();
		$output = $this->utils->passthru( [
			PHP_BINARY,
			__DIR__.'/testData/standard/.px_execute.php',
			'-u', 'Mozilla',
			'--method', 'post',
			'--body-file', __DIR__.'/post_request_body.txt',
			'/http_methods/index.html?test=get_test'
		] );
		$this->fs->rm(__DIR__.'/post_request_body.txt');
		clearstatcache();
		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertEquals( 1, preg_match('/'.preg_quote('<p>method = POST</p>', '/').'/s', $output) );
		$this->assertEquals( 1, preg_match('/'.preg_quote('<p>$_GET[\'test\'] = get_test</p>', '/').'/s', $output) );
		$this->assertEquals( 1, preg_match('/'.preg_quote('<p>$_POST[\'test\'] = post_test_file</p>', '/').'/s', $output) );


		// POSTメソッドで request body をファイルから送ってエミュレートするテスト
		// _sys/ram/data フォルダを検索する場合
		$this->fs->save_file(__DIR__.'/testData/standard/px-files/_sys/ram/data/post_request_body2.txt', 'test=post_test_file&test2=post_test2_file');
		clearstatcache();
		$output = $this->utils->passthru( [
			PHP_BINARY,
			__DIR__.'/testData/standard/.px_execute.php',
			'-u', 'Mozilla',
			'--method', 'post',
			'--body-file', 'post_request_body2.txt',
			'/http_methods/index.html?test=get_test'
		] );
		$this->fs->rm(__DIR__.'/testData/standard/px-files/_sys/ram/data/post_request_body2.txt');
		clearstatcache();
		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertEquals( 1, preg_match('/'.preg_quote('<p>method = POST</p>', '/').'/s', $output) );
		$this->assertEquals( 1, preg_match('/'.preg_quote('<p>$_GET[\'test\'] = get_test</p>', '/').'/s', $output) );
		$this->assertEquals( 1, preg_match('/'.preg_quote('<p>$_POST[\'test\'] = post_test_file</p>', '/').'/s', $output) );


		// 後始末
		$output = $this->utils->px_execute( '/standard/.px_execute.php', '/?PX=clearcache' );

		clearstatcache();
		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/' ) );

	}

	/**
	 * アプリケーションロックのテスト
	 * @depends testCLIStandard
	 */
	public function testAppLock(){
		// ロックする
		$output = $this->utils->passthru( [
			PHP_BINARY,
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/applock/lock.html' ,
		] );
		clearstatcache();
		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertTrue( is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/applock/' ) );
		$this->assertTrue( is_file( __DIR__.'/testData/standard/px-files/_sys/ram/applock/testAppLock.lock.txt' ) );
		$this->assertEquals( 1, preg_match('/'.preg_quote('testAppLock [SUCCESS]', '/').'/s', $output) );

		// ロックされてて動かない
		$output = $this->utils->passthru( [
			PHP_BINARY,
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/applock/lock.html' ,
		] );
		clearstatcache();
		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertTrue( is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/applock/' ) );
		$this->assertTrue( is_file( __DIR__.'/testData/standard/px-files/_sys/ram/applock/testAppLock.lock.txt' ) );
		$this->assertEquals( 1, preg_match('/'.preg_quote('testAppLock [FAILED]', '/').'/s', $output) );

		// ロックを解除
		$output = $this->utils->passthru( [
			PHP_BINARY,
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/applock/unlock.html' ,
		] );
		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertTrue( is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/applock/' ) );
		$this->assertFalse( is_file( __DIR__.'/testData/standard/px-files/_sys/ram/applock/testAppLock.lock.txt' ) );

		// 後始末
		$output = $this->utils->px_execute( '/standard/.px_execute.php', '/?PX=clearcache' );
		clearstatcache();
		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/' ) );
	}

	/**
	 * `$px->h()` のテスト
	 */
	public function testH(){
		$cd = realpath('.');
		chdir(__DIR__.'/testData/standard/');

		$px = new picklesFramework2\px('./px-files/');

		$this->assertSame( '', $px->h() );
		$this->assertSame( '', $px->h(null) );
		$this->assertSame( '0', $px->h('0') );
		$this->assertSame( '&lt;html lang=&quot;ja&quot;&gt;', $px->h('<html lang="ja">') );
		$this->assertSame( '&lt;html<br />'."\n".'lang=&quot;ja&quot;&gt;', nl2br($px->h('<html'."\n".'lang="ja">')) );


		chdir($cd);
		$px->__destruct();// <- required on Windows
		unset($px);

		// 後始末
		$output = $this->utils->px_execute( '/standard/.px_execute.php', '/?PX=clearcache' );
		clearstatcache();
		$this->assertTrue( $this->utils->common_error( $output ) );
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
		$this->assertEquals( count($toppage_info), 16 );
		$this->assertEquals( $toppage_info['title'], '<HOME>' );
		$this->assertEquals( $toppage_info['path'], '/index.html' );
		$this->assertNull( @$toppage_info[''] );
		$this->assertNull( @$toppage_info['start_with_no_asterisk'] );

		$page_info = $px->site()->get_page_info('pickles2.pxt.jp_1');
		$this->assertEquals( count($page_info), 16 );
		$this->assertEquals( $page_info['title'], 'Pickles2 Official Website(1)' );
		$this->assertEquals( $page_info['path'], 'alias35:http://pickles2.pxt.jp/' );

		$page_info = $px->site()->get_page_info('pickles2.pxt.jp_2');
		$this->assertEquals( count($page_info), 16 );
		$this->assertEquals( $page_info['title'], 'Pickles2 Official Website(2)' );
		$this->assertEquals( $page_info['path'], 'alias36://pickles2.pxt.jp/' );

		$page_info = $px->site()->get_page_info(':auto_page_id.37');
		$this->assertEquals( count($page_info), 16 );
		$this->assertEquals( $page_info['title'], 'Pickles2 Official Website(3)' );
		$this->assertEquals( $page_info['path'], 'alias37:http://pickles2.pxt.jp/index.html' );

		$page_info = $px->site()->get_page_info(':auto_page_id.38');
		$this->assertEquals( count($page_info), 16 );
		$this->assertEquals( $page_info['title'], 'Pickles2 Official Website(4)' );
		$this->assertEquals( $page_info['path'], 'alias38://pickles2.pxt.jp/index.html' );

		$page_info = $px->site()->get_page_info('trim_white_space_test');
		$this->assertEquals( count($page_info), 16 );
		$this->assertEquals( $page_info['id'], 'trim_white_space_test' );
		$this->assertEquals( $page_info['title'], '前後の空白文字を削除するテスト' );
		$this->assertEquals( $page_info['path'], '/sample_pages/trim_white_space_test.html' );
		$this->assertEquals( $page_info['content'], '/sample_pages/trim_white_space_test.html' );
		$this->assertEquals( $page_info['logical_path'], '/sample_pages/' );
		$this->assertEquals( $page_info['list_flg'], '1' );
		$this->assertEquals( $page_info['layout'], 'default' );
		$this->assertEquals( $page_info['orderby'], '100' );
		$this->assertEquals( $page_info['category_top_flg'], '0' );
		$this->assertEquals( $page_info['role'], '' );

		chdir($cd);
		$px->__destruct();// <- required on Windows
		unset($px);

		// 後始末
		$output = $this->utils->px_execute( '/standard/.px_execute.php', '/?PX=clearcache' );
		clearstatcache();
		$this->assertTrue( $this->utils->common_error( $output ) );
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
		$output = $this->utils->px_execute( '/caches_behavior/make/.px_execute.php', '/?PX=clearcache' );
		clearstatcache();
		$this->assertTrue( $this->utils->common_error( $output ) );
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
		$output = $this->utils->px_execute( '/caches_behavior/notmake/.px_execute.php', '/?PX=clearcache' );
		clearstatcache();
		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/caches_behavior/notmake/caches/p/' ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/caches_behavior/notmake/px-files/_sys/ram/caches/sitemaps/' ) );
		$this->assertTrue( $this->fs->rm( __DIR__.'/testData/caches_behavior/notmake/px-files/_sys/' ) );
	}

	/**
	 * $px->site()->set_page_info() を実行してみるテスト
	 * @depends testCLIStandard
	 */
	public function testStandardSetPageInfo(){
		$output = $this->utils->passthru( [
			PHP_BINARY,
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/site/set_page_info.html' ,
		] );
		clearstatcache();

		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertEquals( 'naked', $output );


		$output = $this->utils->passthru( [
			PHP_BINARY,
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/site/no_page.html' ,
		] );
		clearstatcache();

		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertEquals( 'no_page.html', $output );

		// 後始末
		$output = $this->utils->px_execute( '/standard/.px_execute.php', '/?PX=clearcache' );
		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/' ) );
	}

	/**
	 * $px->mk_link() を実行してみるテスト
	 * @depends testCLIStandard
	 */
	public function testStandardMkLink(){
		$output = $this->utils->px_execute(
			'/standard/.px_execute.php',
			'/mk_link/'
		);

		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertEquals(
			'<a href="/" class="current">&lt;HOME&gt;</a><a href="/" class="current"><link0></a>'
			.'<a href="/" class="current"><link1></a>'
			.'<a href="/" class="current"><link2></a>'
			.'<a href="/" class="current">&lt;link3&gt;</a>'
			.'<a href="/" target="_blank" class="current"><link4></a>'
			.'<a href="/" target="_blank" class="current"><link5></a>'
		, $output );


		// 後始末
		$output = $this->utils->px_execute( '/standard/.px_execute.php', '/?PX=clearcache' );
		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/' ) );
	}

	/**
	 * $px->href() と $px->canonical() を実行してみるテスト
	 * @depends testCLIStandard
	 */
	public function testStandardHrefCanonical(){
		$cd = realpath('.');
		$SCRIPT_FILENAME = $_SERVER['SCRIPT_FILENAME'];
		chdir(__DIR__.'/testData/standard/');
		$_SERVER['SCRIPT_FILENAME'] = __DIR__.'/testData/standard/.px_execute.php';

		$px = new picklesFramework2\px('./px-files/');

		$this->assertEquals( $px->href('/'), '/' );
		$this->assertEquals( $px->href('/undefined_page/test.html'), '/undefined_page/test.html' );
		$this->assertEquals( $px->href('/undefined_page/test.html?param1=1'), '/undefined_page/test.html?param1=1' );
		$this->assertEquals( $px->href('/undefined_page/test.html#hash'), '/undefined_page/test.html#hash' );
		$this->assertEquals( $px->href('/undefined_page/test.html?param1=1#hash'), '/undefined_page/test.html?param1=1#hash' );
		$this->assertEquals( $px->canonical(), 'http://pickles2.pxt.jp/' );
		$this->assertEquals( $px->canonical('/'), 'http://pickles2.pxt.jp/' );
		$this->assertEquals( $px->canonical('/undefined_page/test.html'), 'http://pickles2.pxt.jp/undefined_page/test.html' );
		$this->assertEquals( $px->canonical('/undefined_page/test.html?param1=1'), 'http://pickles2.pxt.jp/undefined_page/test.html?param1=1' );
		$this->assertEquals( $px->canonical('/undefined_page/test.html#hash'), 'http://pickles2.pxt.jp/undefined_page/test.html#hash' );
		$this->assertEquals( $px->canonical('/undefined_page/test.html?param1=1#hash'), 'http://pickles2.pxt.jp/undefined_page/test.html?param1=1#hash' );

		// `mk_link()` の canonical オプション
		$this->assertEquals( $px->mk_link('/'), '<a href="/" class="current">&lt;HOME&gt;</a>' );
		$this->assertEquals( $px->mk_link('/', array('canonical'=>true)), '<a href="http://pickles2.pxt.jp/" class="current">&lt;HOME&gt;</a>' );


		// サブリクエストでキャッシュを消去
		$output = $px->internal_sub_request(
			'/index.html?PX=clearcache',
			array(),
			$vars
		);
		$error = $px->get_errors();

		$this->assertTrue( is_string($output) );
		$this->assertSame( 0, $vars ); // <- strict equals
		$this->assertSame( array(), $error );


		chdir($cd);
		$_SERVER['SCRIPT_FILENAME'] = $SCRIPT_FILENAME;

		$px->__destruct();// <- required on Windows
		unset($px);
	}

	/**
	 * paths_enable_sitemap のテスト
	 * @depends testCLIStandard
	 */
	public function testPathsEnableSitemap(){
		$output = $this->utils->px_execute(
			'/standard/.px_execute.php',
			'/common/styles/sitemap_loaded.css'
		);
		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertEquals( preg_match('/failed/', $output), 0 );

		$output = $this->utils->px_execute(
			'/standard/.px_execute.php',
			'/common/styles/sitemap_not_loaded.css'
		);
		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertEquals( preg_match('/failed/', $output), 0 );


		// 後始末
		$output = $this->utils->px_execute( '/standard/.px_execute.php', '/?PX=clearcache' );
		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/' ) );
	}

	/**
	 * ダイナミックパスを実行してみるテスト
	 * @depends testCLIStandard
	 */
	public function testStandardDynamicPath(){
		$output = $this->utils->px_execute(
			'/standard/.px_execute.php',
			'/dynamicPath/'
		);
		$this->assertTrue( $this->utils->common_error( $output ) );
		$html = $this->utils->simple_html_dom_parse($output);
		$this->assertEquals( $html->find('.theme_megafooter_navi li a[href=/dynamicPath/]')[0]->class, 'current' );

		$output = $this->utils->px_execute(
			'/standard/.px_execute.php',
			'/dynamicPath/test1.html'
		);
		$this->assertTrue( $this->utils->common_error( $output ) );
		$html = $this->utils->simple_html_dom_parse($output);
		$this->assertEquals( $html->find('.theme_megafooter_navi li a[href=/dynamicPath/]')[0]->class, 'current' );

		$output = $this->utils->px_execute(
			'/standard/.px_execute.php',
			'/dynamicPath/dummy_file_name.html'
		);
		$this->assertTrue( $this->utils->common_error( $output ) );
		$html = $this->utils->simple_html_dom_parse($output);
		$this->assertEquals( $html->find('.theme_megafooter_navi li a[href=/dynamicPath/]')[0]->class, 'current' );

		$output = $this->utils->px_execute(
			'/standard/.px_execute.php',
			'/dynamicPath---/test1.html'
		);
		$this->assertTrue( $this->utils->common_error( $output ) );
		$html = $this->utils->simple_html_dom_parse($output);
		$this->assertFalse( $html->find('.theme_megafooter_navi li a[href=/dynamicPath/]')[0]->class );


		// 後始末
		$output = $this->utils->px_execute( '/standard/.px_execute.php', '/?PX=clearcache' );
		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/' ) );
	}



	/**
	 * $_SERVER にアクセスするテスト
	 * @depends testCLIStandard
	 */
	public function testServerEnv(){
		$output = $this->utils->passthru( [
			PHP_BINARY,
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/' ,
		] );
		clearstatcache();

		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertEquals( preg_match( '/'.preg_quote( '<p>DocumentRoot = <span>'.htmlspecialchars(realpath(__DIR__.'/testData/standard/')).'</span></p>', '/' ).'/', $output ), 1 );


		$output = $this->utils->passthru( [
			PHP_BINARY,
			__DIR__.'/testData/standard/subproj/sub1/.px_execute.php' ,
			'/' ,
		] );
		clearstatcache();

		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertEquals( preg_match( '/'.preg_quote( '<p>DocumentRoot = <span>'.htmlspecialchars(realpath(__DIR__.'/testData/standard/')).'</span></p>', '/' ).'/', $output ), 1 );


		// 後始末
		$output = $this->utils->px_execute( '/standard/.px_execute.php', '/?PX=clearcache' );
		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/' ) );

		$output = $this->utils->px_execute( '/standard/subproj/sub1/.px_execute.php', '/?PX=clearcache' );
		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/subproj/sub1/caches/p/' ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/subproj/sub1/px-files/_sys/ram/caches/sitemaps/' ) );
	}


	/**
	 * 遠いディレクトリからコマンドラインで実行してみるテスト
	 * @depends testCLIStandard
	 */
	public function testStandardCmdExecByFarDirectory(){
		$output = $this->utils->passthru( [
			PHP_BINARY,
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/contents/get_path_docroot.html' ,
		] );
		clearstatcache();

		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertEquals( '<pre>'.$this->fs->normalize_path( __DIR__.'/testData/standard/' ).'</pre>', $output );

		$cd = realpath('.');
		chdir(__DIR__.'/');
		$output = $this->utils->passthru( [
			PHP_BINARY,
			'./testData/standard/.px_execute.php' ,
			'/contents/get_path_docroot.html' ,
		] );
		clearstatcache();
		chdir($cd);

		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertEquals( '<pre>'.$this->fs->normalize_path( __DIR__.'/testData/standard/' ).'</pre>', $output );

		$cd = realpath('.');
		chdir(__DIR__.'/testData/standard/');
		$output = $this->utils->passthru( [
			PHP_BINARY,
			'./.px_execute.php' ,
			'/contents/get_path_docroot.html' ,
		] );
		clearstatcache();
		chdir($cd);

		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertEquals( '<pre>'.$this->fs->normalize_path( __DIR__.'/testData/standard/' ).'</pre>', $output );


		// 後始末
		$output = $this->utils->px_execute( '/standard/.px_execute.php', '/?PX=clearcache' );
		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/' ) );
	}


	/**
	 * コンフィグ項目をコマンドラインオプションで上書きするテスト
	 * @depends testCLIStandard
	 */
	public function testOverrideConfig(){

		$output = $this->utils->passthru( [
			PHP_BINARY,
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=api.get.config' ,
		] );
		clearstatcache();
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output->commands->php, PHP_BINARY );
		$this->assertNull( $output->commands->dummy ?? null );

		$output = $this->utils->passthru( [
			PHP_BINARY,
			__DIR__.'/testData/standard/.px_execute.php' ,
			'--command-php', '/path/to/command/php',
			'-c', '/path/to/file/php.ini',
			'/?PX=api.get.config' ,
		] );
		clearstatcache();
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output->commands->php, '/path/to/command/php' );
		$this->assertEquals( $output->path_phpini, '/path/to/file/php.ini' );

		$output = $this->utils->passthru( [
			PHP_BINARY,
			__DIR__.'/testData/standard/.px_execute.php' ,
			'--command-php', 'C:\path\to\command\php.exe',
			'-c', 'C:\path\to\file\php.ini',
			'/?PX=api.get.config' ,
		] );
		clearstatcache();
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output->commands->php, 'C:\path\to\command\php.exe' );
		$this->assertEquals( $output->path_phpini, 'C:\path\to\file\php.ini' );


		// 後始末
		$output = $this->utils->px_execute( '/standard/.px_execute.php', '/?PX=clearcache' );
		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/' ) );
	}


	/**
	 * 最小構成のサイトマップCSV
	 * @depends testCLIStandard
	 */
	public function testSitemapMinimum(){
		$path_csv = __DIR__.'/testData/sitemap_min/px-files/sitemaps/.~lock.sitemap.csv#';
		// $path_csv = __DIR__.'/testData/sitemap_min/px-files/sitemaps/lock.sitemap.csv'; // <- テストのテスト用
		$this->fs->save_file( $path_csv, '"* path","* title"'."\n".'"/not_read.html","NOT-READ"'."\n" );
		$this->assertTrue( is_file($path_csv) );

		$output = $this->utils->px_execute(
			'/sitemap_min/.px_execute.php',
			'/'
		);
		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertEquals( preg_match( '/\<span\>FAILED\<\/span\>/', $output ), 0 );


		// 後始末
		$this->fs->rm( $path_csv );
		$this->assertFalse( is_file($path_csv) );
		$output = $this->utils->px_execute( '/sitemap_min/.px_execute.php', '/?PX=clearcache' );
		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/sitemap_min/caches/p/' ) );

	} // testSitemapMinimum()


	/**
	 * path_proc_type のテスト
	 * @depends testCLIStandard
	 */
	public function testProcType(){
		$output = $this->utils->passthru( [
			PHP_BINARY,
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/vendor/autoload.php' ,
		] );
		clearstatcache();
		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertEquals( $output, 'ignored path' );


		$output = $this->utils->passthru( [
			PHP_BINARY,
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/pass/pass.html' ,
		] );
		clearstatcache();
		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertEquals( trim($output), 'passed file.' );


		// 後始末
		$output = $this->utils->px_execute( '/standard/.px_execute.php', '/?PX=clearcache' );
		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );

	} // testProcType()

	/**
	 * プラグインメソッドを `config.php` から直接的な呼び出し方で設定するテスト
	 */
	public function testGettingPluginsName(){
		$this->assertEquals( \picklesFramework2\processors\ssi\ssi::exec(), 'picklesFramework2\processors\ssi\ssi::exec()' );
		$this->assertEquals( \picklesFramework2\processors\scss\ext::exec(), 'picklesFramework2\processors\scss\ext::exec()' );
		$this->assertEquals( \picklesFramework2\processors\md\ext::exec(), 'picklesFramework2\processors\md\ext::exec()' );
		$this->assertEquals( \picklesFramework2\processors\encodingconverter\encodingconverter::exec(), 'picklesFramework2\processors\encodingconverter\encodingconverter::exec()' );
		$this->assertEquals( \picklesFramework2\processors\autoindex\autoindex::exec(), 'picklesFramework2\processors\autoindex\autoindex::exec()' );
		$this->assertEquals( \picklesFramework2\commands\publish::register(), 'picklesFramework2\commands\publish::register()' );
		$this->assertEquals( \picklesFramework2\commands\phpinfo::register(), 'picklesFramework2\commands\phpinfo::register()' );
		$this->assertEquals( \picklesFramework2\commands\config::register(), 'picklesFramework2\commands\config::register()' );
		$this->assertEquals( \picklesFramework2\commands\clearcache::register(), 'picklesFramework2\commands\clearcache::register()' );
		$this->assertEquals( \picklesFramework2\commands\api::register(), 'picklesFramework2\commands\api::register()' );

		// オプションを設定するテスト
		$options = array(
			'test' => 1,
		);
		$this->assertEquals( \picklesFramework2\commands\publish::register( $options ), 'picklesFramework2\commands\publish::register('.json_encode($options).')' );
		$this->assertEquals( \picklesFramework2\processors\ssi\ssi::exec( $options ), 'picklesFramework2\processors\ssi\ssi::exec('.json_encode($options).')' );
		$this->assertEquals( \picklesFramework2\processors\scss\ext::exec( $options ), 'picklesFramework2\processors\scss\ext::exec('.json_encode($options).')' );
		$this->assertEquals( \picklesFramework2\processors\md\ext::exec( $options ), 'picklesFramework2\processors\md\ext::exec('.json_encode($options).')' );
		$this->assertEquals( \picklesFramework2\processors\encodingconverter\encodingconverter::exec( $options ), 'picklesFramework2\processors\encodingconverter\encodingconverter::exec('.json_encode($options).')' );
		$this->assertEquals( \picklesFramework2\processors\autoindex\autoindex::exec( $options ), 'picklesFramework2\processors\autoindex\autoindex::exec('.json_encode($options).')' );
		$this->assertEquals( \picklesFramework2\commands\publish::register( $options ), 'picklesFramework2\commands\publish::register('.json_encode($options).')' );
		$this->assertEquals( \picklesFramework2\commands\phpinfo::register( $options ), 'picklesFramework2\commands\phpinfo::register('.json_encode($options).')' );
		$this->assertEquals( \picklesFramework2\commands\config::register( $options ), 'picklesFramework2\commands\config::register('.json_encode($options).')' );
		$this->assertEquals( \picklesFramework2\commands\clearcache::register( $options ), 'picklesFramework2\commands\clearcache::register('.json_encode($options).')' );
		$this->assertEquals( \picklesFramework2\commands\api::register( $options ), 'picklesFramework2\commands\api::register('.json_encode($options).')' );


	} // testGettingPluginsName()


}
