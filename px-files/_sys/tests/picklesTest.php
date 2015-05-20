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
	 * /?PX=api.get.* から値を取得するテスト
	 */
	public function testPxApiGetAnything(){

		// -------------------
		// api.get.vertion
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=api.get.version' ,
		] );
		clearstatcache();

		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertEquals( 1, preg_match('/^[0-9]+\.[0-9]+\.[0-9]+(?:\-(?:alpha|beta)[0-9]+)?(?:\-nb)?$/s', json_decode($output)) );


		// -------------------
		// api.get.config
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=api.get.config' ,
		] );
		clearstatcache();

		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output->name, 'Pickles 2' );
		$this->assertEquals( $output->path_controot, '/' );


		// -------------------
		// api.get.sitemap
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=api.get.sitemap' ,
		] );
		clearstatcache();

		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( $output->{"/dynamicPath/{*}"}->title, 'Dynamic Path' );
		$this->assertNull( $output->{"alias21:/sample_pages/page2/index.html"}->content );
		$this->assertEquals( $output->{"alias21:/sample_pages/page2/index.html"}->title, 'サンプルページ2へのエイリアス' );
		$this->assertEquals( $output->{"/sample_pages/help/index.html"}->id, ':auto_page_id.29' );

		// -------------------
		// api.get.page_info
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=api.get.page_info&path='.urlencode('/sample_pages/page2/') ,
		] );
		clearstatcache();

		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( $output->path, '/sample_pages/page2/index.html' );

		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=api.get.page_info&path='.urlencode('/dynamicPath/aaaaa.html') ,
		] );
		clearstatcache();

		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( $output->path, '/dynamicPath/{*}' );

		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=api.get.page_info&path='.urlencode('/dynamicPath/test1/') ,
		] );
		clearstatcache();

		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( $output->path, '/dynamicPath/test1/index.html' );


		// -------------------
		// api.get.path_homedir
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=api.get.path_homedir' ,
		] );
		clearstatcache();

		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( realpath($output), realpath(__DIR__.'/testData/standard/px-files') );


		// -------------------
		// api.get.path_controot
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=api.get.path_controot' ,
		] );
		clearstatcache();

		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( $output, '/' );


		// -------------------
		// api.get.path_docroot
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=api.get.path_docroot' ,
		] );
		clearstatcache();

		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( realpath($output), realpath(__DIR__.'/testData/standard') );


		// -------------------
		// api.get.domain
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=api.get.domain' ,
		] );
		clearstatcache();

		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( $output, null );


		// -------------------
		// api.get.directory_index
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=api.get.directory_index' ,
		] );
		clearstatcache();

		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( $output[0], 'index.html' );

		// -------------------
		// api.get.directory_index_primary
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=api.get.directory_index_primary' ,
		] );
		clearstatcache();

		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( $output, 'index.html' );


		// -------------------
		// api.get.path_proc_type
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/sample_pages/page2/?PX=api.get.path_proc_type' ,
		] );
		clearstatcache();

		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( $output, 'html' );

		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=api.get.path_proc_type&path='.urlencode('/sample_pages/page2/') ,
		] );
		clearstatcache();

		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( $output, 'html' );


		// -------------------
		// api.get.is_ignore_path
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=api.get.is_ignore_path&path='.urlencode('/sample_pages/page2/') ,
		] );
		clearstatcache();

		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertFalse( $output );

		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=api.get.is_ignore_path&path='.urlencode('/vendor/') ,
		] );
		clearstatcache();

		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertTrue( $output );



		// -------------------
		// api.get.href
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=api.get.href&linkto='.urlencode('/sample_pages/page2/index.html') ,
		] );
		clearstatcache();

		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( $output, '/sample_pages/page2/' );

		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=api.get.href&linkto='.urlencode('/dynamicPath/{*}') ,
		] );
		clearstatcache();

		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( $output, '/dynamicPath/' );

		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=api.get.href&linkto='.urlencode('/dynamicPath/{*}?a=b') ,
		] );
		clearstatcache();

		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( $output, '/dynamicPath/?a=b' );

		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=api.get.href&linkto='.urlencode('/dynamicPath/{*}?a=b#testAnch') ,
		] );
		clearstatcache();

		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( $output, '/dynamicPath/?a=b#testAnch' );







		// 後始末
		$output = $this->passthru( [
			'php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=clearcache'
		] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/' ) );
	}//testPxApiGetAnything();


	/**
	 * 遠いディレクトリからコマンドラインで実行してみるテスト
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
		$output = $this->passthru( [
			'php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=clearcache'
		] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/' ) );
	}


	/**
	 * 文字コード・改行コード変換テスト
	 */
	public function testEncodingConverter(){
		$detect_order = 'UTF-8,eucJP-win,SJIS-win,EUC-JP,SJIS';

		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/encodingconverter/.px_execute.php' ,
			'/' ,
		] );
		clearstatcache();

		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertEquals( preg_match( '/'.preg_quote('<meta charset="Shift_JIS" />', '/').'/s', $output ), 1 );
		$this->assertEquals( preg_match( '/\r\n/s', $output ), 1 );
		// var_dump(mb_detect_encoding($output, $detect_order));
		$this->assertEquals( @strtolower('SJIS-win'), @strtolower(mb_detect_encoding($output, $detect_order)) );


		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/encodingconverter/.px_execute.php' ,
			'/common/test.css' ,
		] );
		clearstatcache();

		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertEquals( preg_match( '/'.preg_quote('@charset "EUC-JP"', '/').'/s', $output ), 1 );
		$this->assertEquals( preg_match( '/\r\n/s', $output ), 0 );
		$this->assertEquals( preg_match( '/\n/s', $output ), 1 );
		$this->assertEquals( preg_match( '/\r/s', $output ), 0 );
		// var_dump(mb_detect_encoding($output, $detect_order));
		$this->assertEquals( @strtolower('eucJP-win'), @strtolower(mb_detect_encoding($output, $detect_order)) );


		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/encodingconverter/.px_execute.php' ,
			'/common/test.js' ,
		] );
		clearstatcache();

		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertEquals( preg_match( '/\r\n/s', $output ), 0 );
		$this->assertEquals( preg_match( '/\n/s', $output ), 0 );
		$this->assertEquals( preg_match( '/\r/s', $output ), 1 );
		// var_dump(mb_detect_encoding($output, $detect_order));
		$this->assertEquals( @strtolower('utf-8'), @strtolower(mb_detect_encoding($output, $detect_order)) );


		// 後始末
		// $this->assertTrue( false );
		$output = $this->passthru( [
			'php', __DIR__.'/testData/encodingconverter/.px_execute.php', '/?PX=clearcache'
		] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/encodingconverter/caches/p/' ) );

	}

	/**
	 * パブリッシュしてみるテスト
	 * @depends testStandardSetPageInfo
	 * @depends testPxApiGetAnything
	 * @depends testStandardCmdExecByFarDirectory
	 * @depends testEncodingConverter
	 */
	public function testStandardPublish(){
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=publish.run' ,
		] );
		clearstatcache();

		// var_dump($output);
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

		$this->assertTrue( is_file( __DIR__.'/testData/standard/px-files/_sys/ram/publish/htdocs/dynamicPath/test1/index.html' ) );
		$fileSrc = file_get_contents(__DIR__.'/testData/standard/px-files/_sys/ram/publish/htdocs/dynamicPath/test1/index.html');
		// var_dump( $fileSrc );
		$this->assertEquals( preg_match( '/'.preg_quote('<!DOCTYPE html>', '/').'/s', $fileSrc ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote(htmlspecialchars('<php_output>'), '/').'/s', $fileSrc ), 1 );

		$this->assertTrue( is_file( __DIR__.'/testData/standard/px-files/_sys/ram/publish/htdocs/dynamicPath/test2_direct/index.html' ) );
		$fileSrc = file_get_contents(__DIR__.'/testData/standard/px-files/_sys/ram/publish/htdocs/dynamicPath/test2_direct/index.html');
		// var_dump( $fileSrc );
		$this->assertEquals( $fileSrc, '<'.'?= htmlspecialchars(\'<php_output>\'); ?'.'>' );

		$this->assertTrue( is_file( __DIR__.'/testData/standard/caches/c/contents/path_files_cache_files/test.inc' ) );
		$this->assertTrue( is_file( __DIR__.'/testData/standard/caches/c/contents/path_files_cache_files/test2/test2.inc' ) );

		// $px->path_files_private_cache() のテスト
		$fileSrc = file_get_contents( __DIR__.'/testData/standard/px-files/_sys/ram/publish/htdocs/contents/path_files_private_cache.html' );
		// var_dump($fileSrc);
		$this->assertEquals( preg_match( '/'.preg_quote('<pre>'.$this->fs->get_realpath( $this->fs->normalize_path( __DIR__.'/testData/standard/px-files/_sys/ram/caches/c/contents/path_files_private_cache_files/a.html' ) ), '/').'/s', $fileSrc ), 1 );

		// ログファイルを確認
		$this->assertTrue( is_file( __DIR__.'/testData/standard/px-files/_sys/ram/publish/publish_log.csv' ) );
		$publish_log = $this->fs->read_csv( __DIR__.'/testData/standard/px-files/_sys/ram/publish/publish_log.csv' );
		// var_dump($publish_log);
		$this->assertEquals( $publish_log[0][0], 'datetime' );
		$this->assertEquals( $publish_log[0][1], 'path' );
		$this->assertEquals( $publish_log[0][2], 'proc_type' );
		$this->assertEquals( $publish_log[0][3], 'status_code' );
		$this->assertEquals( $publish_log[0][4], 'status_message' );
		$this->assertEquals( $publish_log[0][5], 'errors' );
		$this->assertNull( @$publish_log[0][6] );
		$this->assertTrue( is_file( __DIR__.'/testData/standard/px-files/_sys/ram/publish/alert_log.csv' ) );
		foreach( $publish_log as $publish_log_row ){
			if( $publish_log_row[1] == '/errors/server_side_unknown_error.html' ){
				$this->assertEquals( $publish_log_row[4], 'Unknown server error' );
				$this->assertEquals( $publish_log_row[5], '1 errors: Unknown server error.;' );
			}
		}

		$alert_log = $this->fs->read_csv( __DIR__.'/testData/standard/px-files/_sys/ram/publish/alert_log.csv' );
		// var_dump($alert_log);
		$this->assertEquals( $alert_log[0][0], 'datetime' );
		$this->assertEquals( $alert_log[0][1], 'path' );
		$this->assertEquals( $alert_log[0][2], 'error_message' );
		$this->assertNull( @$alert_log[0][3] );
		$this->assertEquals( $alert_log[1][2], 'status: 500 Unknown server error' );
		$this->assertEquals( $alert_log[2][2], 'Unknown server error.' );

		// 後始末
		// $this->assertTrue( false );
		$output = $this->passthru( [
			'php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=clearcache'
		] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );

	}//testStandardPublish()


	/**
	 * キャッシュを削除するテスト
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
