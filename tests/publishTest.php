<?php
/**
 * test for tomk79\PxFW-2.x
 */
class publishTest extends PHPUnit_Framework_TestCase{
	private $fs;

	public function setup(){
		mb_internal_encoding('UTF-8');
		$this->fs = new tomk79\filesystem();
	}


	/**
	 * API疎通確認
	 */
	public function testBefore(){

		// ----- standard -----

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
		$this->assertEquals( 1, preg_match('/^[0-9]+\.[0-9]+\.[0-9]+(?:\-(?:alpha|beta|rc)(?:\.[a-zA-Z0-9][a-zA-Z0-9\.]*)?)?(?:\+[a-zA-Z0-9\.]+)?$/s', json_decode($output)) );

		// -------------------
		// api.get.config
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=api.get.config' ,
		] );
		clearstatcache();

		// -------------------
		// publish.version
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=publish.version' ,
		] );
		clearstatcache();
		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertEquals( 1, preg_match('/^[0-9]+\.[0-9]+\.[0-9]+(?:\-(?:alpha|beta|rc)(?:\.[a-zA-Z0-9][a-zA-Z0-9\.]*)?)?(?:\+[a-zA-Z0-9\.]+)?$/s', json_decode($output)) );

		// -------------------
		// clearcache.version
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=clearcache.version' ,
		] );
		clearstatcache();
		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertEquals( 1, preg_match('/^[0-9]+\.[0-9]+\.[0-9]+(?:\-(?:alpha|beta|rc)(?:\.[a-zA-Z0-9][a-zA-Z0-9\.]*)?)?(?:\+[a-zA-Z0-9\.]+)?$/s', json_decode($output)) );


		// ----- publish -----

		// -------------------
		// api.get.vertion
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/publish/px2/.px_execute.php' ,
			'/?PX=api.get.version' ,
		] );
		clearstatcache();

		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertEquals( 1, preg_match('/^[0-9]+\.[0-9]+\.[0-9]+(?:\-(?:alpha|beta|rc)(?:\.[a-zA-Z0-9][a-zA-Z0-9\.]*)?)?(?:\+[a-zA-Z0-9\.]+)?$/s', json_decode($output)) );

		// -------------------
		// api.get.config
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/publish/px2/.px_execute.php' ,
			'/?PX=api.get.config' ,
		] );
		clearstatcache();

	}// testBefore()

	/**
	 * パブリッシュしてみるテスト (`before_sitemap` に設定した場合)
	 * @depends testBefore
	 */
	public function testStandardPublishBeforeSitemap(){
		$this->fs->copy(
			__DIR__.'/testData/publish/px2/px-files/config_before_sitemap.php',
			__DIR__.'/testData/publish/px2/px-files/config.php'
		);
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

		$this->assertTrue( is_file( __DIR__.'/testData/standard/px-files/_sys/ram/publish/htdocs/sample_pages/global_urls/index.html' ) );
		$fileSrc = file_get_contents(__DIR__.'/testData/standard/px-files/_sys/ram/publish/htdocs/sample_pages/global_urls/index.html');
		// var_dump( $fileSrc );
		$this->assertEquals( 1, preg_match('/'.preg_quote('<li><a href="http://pickles2.pxt.jp/">Pickles2 Official Website(1)</a></li>','/').'/s', $fileSrc) );
		$this->assertEquals( 1, preg_match('/'.preg_quote('<li><a href="http://pickles2.pxt.jp/index.html">Pickles2 Official Website(3)</a></li>','/').'/s', $fileSrc) );
		$this->assertEquals( 1, preg_match('/'.preg_quote('<li><a href="//pickles2.pxt.jp/index.html">Pickles2 Official Website(4)</a></li>','/').'/s', $fileSrc) );
		$this->assertEquals( 1, preg_match('/'.preg_quote('<li><a href="javascript:alert(\'JavaScript//URL//Link\');">JavaScript</a></li>','/').'/s', $fileSrc) );
		$this->assertEquals( 1, preg_match('/'.preg_quote('<li><a href="data:image/png;base64,hoge/fuga///foo.bar==">Data Scheme</a></li>','/').'/s', $fileSrc) );

		$this->assertTrue( is_file( __DIR__.'/testData/standard/caches/c/contents/path_files_cache_files/test.inc' ) );
		$this->assertTrue( is_file( __DIR__.'/testData/standard/caches/c/contents/path_files_cache_files/test2/test2.inc' ) );

		// $px->path_files_private_cache() のテスト
		$fileSrc = file_get_contents( __DIR__.'/testData/standard/px-files/_sys/ram/publish/htdocs/contents/path_files_private_cache.html' );
		// var_dump($fileSrc);
		$this->assertEquals( preg_match( '/'.preg_quote('<pre>'.$this->fs->normalize_path( $this->fs->get_realpath( __DIR__.'/testData/standard/px-files/_sys/ram/caches/c/contents/path_files_private_cache_files/a.html' ) ), '/').'/s', $fileSrc ), 1 );

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
		$this->assertEquals( $publish_log[0][6], 'filesize' );
		$this->assertEquals( $publish_log[0][7], 'proc_microtime' );
		$this->assertNull( @$publish_log[0][8] );
		$this->assertTrue( is_file( __DIR__.'/testData/standard/px-files/_sys/ram/publish/alert_log.csv' ) );
		foreach( $publish_log as $publish_log_row ){
			if( $publish_log_row[1] == '/errors/server_side_unknown_error.html' ){
				$this->assertEquals( strpos($publish_log_row[4], 'Unknown server error'), 0 );
				$this->assertEquals( strpos($publish_log_row[5], '1 errors: Unknown server error'), 0 );
			}
		}

		$alert_log = $this->fs->read_csv( __DIR__.'/testData/standard/px-files/_sys/ram/publish/alert_log.csv' );
		// var_dump($alert_log);
		$this->assertEquals( $alert_log[0][0], 'datetime' );
		$this->assertEquals( $alert_log[0][1], 'path' );
		$this->assertEquals( $alert_log[0][2], 'error_message' );
		$this->assertNull( @$alert_log[0][3] );
		$this->assertEquals( strpos($alert_log[1][2], 'status: 500 Unknown server error'), 0 );
		$this->assertEquals( strpos($alert_log[2][2], 'Unknown server error'), 0 );

		// 後始末
		// $this->assertTrue( false );
		$output = $this->passthru( [
			'php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=clearcache'
		] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );

	}//testStandardPublishBeforeSitemap()


	/**
	 * パブリッシュしてみるテスト (`before_content` に設定した場合)
	 * @depends testBefore
	 */
	public function testStandardPublishBeforeContent(){
		$this->fs->copy(
			__DIR__.'/testData/publish/px2/px-files/config_before_content.php',
			__DIR__.'/testData/publish/px2/px-files/config.php'
		);
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
		$this->assertEquals( preg_match( '/'.preg_quote('<pre>'.$this->fs->normalize_path( $this->fs->get_realpath( __DIR__.'/testData/standard/px-files/_sys/ram/caches/c/contents/path_files_private_cache_files/a.html' ) ), '/').'/s', $fileSrc ), 1 );

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
		$this->assertEquals( $publish_log[0][6], 'filesize' );
		$this->assertEquals( $publish_log[0][7], 'proc_microtime' );
		$this->assertNull( @$publish_log[0][8] );
		$this->assertTrue( is_file( __DIR__.'/testData/standard/px-files/_sys/ram/publish/alert_log.csv' ) );
		foreach( $publish_log as $publish_log_row ){
			if( $publish_log_row[1] == '/errors/server_side_unknown_error.html' ){
				$this->assertEquals( strpos($publish_log_row[4], 'Unknown server error'), 0 );
				$this->assertEquals( strpos($publish_log_row[5], '1 errors: Unknown server error'), 0 );
			}
		}

		$alert_log = $this->fs->read_csv( __DIR__.'/testData/standard/px-files/_sys/ram/publish/alert_log.csv' );
		// var_dump($alert_log);
		$this->assertEquals( $alert_log[0][0], 'datetime' );
		$this->assertEquals( $alert_log[0][1], 'path' );
		$this->assertEquals( $alert_log[0][2], 'error_message' );
		$this->assertNull( @$alert_log[0][3] );
		$this->assertEquals( strpos($alert_log[1][2], 'status: 500 Unknown server error'), 0 );
		$this->assertEquals( strpos($alert_log[2][2], 'Unknown server error'), 0 );

		// 後始末
		// $this->assertTrue( false );
		$output = $this->passthru( [
			'php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=clearcache'
		] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );

	}//testStandardPublishBeforeContent()


	/**
	 * hash と query がついているページのパブリッシュ結果を確認
	 * @depends testBefore
	 */
	public function testPublishHashQueryPages(){
		$this->assertTrue( is_file( __DIR__.'/testData/publish/published/hashtest/index.html' ) );
		$this->assertTrue( is_file( __DIR__.'/testData/publish/published/hashtest/child.html' ) );
		$files = $this->fs->ls( __DIR__.'/testData/publish/published/hashtest/' );
		// var_dump($files);
		$this->assertEquals( count($files), 2 );
	}//testPublishHashQueryPages()

	/**
	 * パブリッシュ範囲のテスト
	 * @depends testBefore
	 */
	public function testPublishRegion(){
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=publish.run&path_region=/contents/&paths_ignore[]=/contents/path_files_cache_files/test2/&paths_ignore[]=/contents/path_files_cache.html' ,
		] );
		clearstatcache();

		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( is_dir( __DIR__.'/testData/standard/caches/p/' ) );
		$this->assertFalse( is_file( __DIR__.'/testData/standard/px-files/_sys/ram/publish/htdocs/index.html' ) );
		$this->assertFalse( is_file( __DIR__.'/testData/standard/px-files/_sys/ram/publish/htdocs/templates.ignore/template.html.md' ) );
		$this->assertTrue( is_file( __DIR__.'/testData/standard/px-files/_sys/ram/publish/htdocs/contents/path_plugin_files.html' ) );
		$this->assertTrue( is_file( __DIR__.'/testData/standard/px-files/_sys/ram/publish/htdocs/contents/path_files_private_cache.html' ) );
		$this->assertTrue( is_file( __DIR__.'/testData/standard/px-files/_sys/ram/publish/htdocs/contents/get_contents_manifesto.html' ) );
		$this->assertFalse( is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/publish/htdocs/contents/path_files_cache_files/test2/' ) );
		$this->assertFalse( is_file( __DIR__.'/testData/standard/px-files/_sys/ram/publish/htdocs/contents/path_files_cache.html' ) );


		// 後始末
		// $this->assertTrue( false );
		$output = $this->passthru( [
			'php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=clearcache'
		] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );

	}//testPublishRegion()

	/**
	 * パブリッシュ範囲のテスト (ワイルドカードを使用)
	 * @depends testBefore
	 */
	public function testPublishRegionWildcard(){
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/publish/px2/.px_execute.php' ,
			'/?PX=publish.run&path_region=/region/ignore/&paths_ignore[]=*.tmp_ig&paths_ignore[]=*/test2.html' ,
		] );
		clearstatcache();

		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( is_dir( __DIR__.'/testData/publish/px2/caches/p/' ) );
		$this->assertTrue( is_file( __DIR__.'/testData/publish/px2/px-files/_sys/ram/publish/htdocs/region/ignore/index.html' ) );
		$this->assertTrue( is_file( __DIR__.'/testData/publish/px2/px-files/_sys/ram/publish/htdocs/region/ignore/test1.html' ) );
		$this->assertFalse( is_file( __DIR__.'/testData/publish/px2/px-files/_sys/ram/publish/htdocs/region/ignore/test1_files/ignored_ext.tmp_ig' ) );
		$this->assertFalse( is_file( __DIR__.'/testData/publish/px2/px-files/_sys/ram/publish/htdocs/region/ignore/test2.html' ) );

		$this->assertTrue( is_file( __DIR__.'/testData/publish/published/region/ignore/index.html' ) );
		$this->assertTrue( is_file( __DIR__.'/testData/publish/published/region/ignore/test1.html' ) );
		$this->assertTrue( is_file( __DIR__.'/testData/publish/published/region/ignore/test1_files/ignored_ext.tmp_ig' ) );
		$this->assertTrue( is_file( __DIR__.'/testData/publish/published/region/ignore/test2.html' ) );

		// 後始末
		// $this->assertTrue( false );
		$output = $this->passthru( [
			'php', __DIR__.'/testData/publish/px2/.px_execute.php', '/?PX=clearcache'
		] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/publish/px2/caches/p/' ) );

	}//testPublishRegionWildcard()


	/**
	 * パブリッシュの対象が0件の場合のテスト
	 * @depends testBefore
	 */
	public function testNothingToPublish(){
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/nothing/to/publish/?PX=publish.run' ,
		] );
		clearstatcache();

		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );

		// 後始末
		// $this->assertTrue( false );
		$output = $this->passthru( [
			'php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=clearcache'
		] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );

	}//testNothingToPublish()


	/**
	 * パブリッシュディレクトリのテスト
	 */
	public function testPublishDirectoryTest(){
		@$this->fs->mkdir_r( __DIR__.'/testData/publish/published/update_sync_test/' );
		@$this->fs->save_file( __DIR__.'/testData/publish/published/update_sync_test/old.html', 'old.html' );
		@$this->fs->rm( __DIR__.'/testData/publish/published/update_sync_test/new.html' );

		// テストデータの操作をテスト
		$this->assertTrue( is_file( __DIR__.'/testData/publish/published/update_sync_test/old.html' ) );
		$this->assertTrue( is_file( __DIR__.'/testData/publish/published/update_sync_test/old.html' ) );

		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/publish/px2/.px_execute.php' ,
			'/?PX=publish.run' ,
		] );
		clearstatcache();

		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( is_file( __DIR__.'/testData/publish/published/update_sync_test/new.html' ) );
		$this->assertFalse( is_file( __DIR__.'/testData/publish/published/update_sync_test/old.html' ) );

		$this->assertFalse( is_file( __DIR__.'/testData/publish/published/path_ignored/testpage.html' ) );
		$this->assertTrue( is_file( __DIR__.'/testData/publish/published/path_ignored/untouchable.html' ) );

		$this->assertFalse( is_file( __DIR__.'/testData/publish/px2/files_ignored/index.html' ) );
		$this->assertTrue( is_file( __DIR__.'/testData/publish/published/files_ignored/index.html' ) );
		$this->assertTrue( is_file( __DIR__.'/testData/publish/px2/files_ignored/not_ignored.html' ) );
		$this->assertTrue( is_file( __DIR__.'/testData/publish/published/files_ignored/not_ignored.html' ) );

		$this->assertTrue( is_file( __DIR__.'/testData/publish/published/files_ignored_dist_only/test1.txt' ) );
		$this->assertFalse( is_file( __DIR__.'/testData/publish/px2/files_ignored_dist_only/test1.txt' ) );


		// パブリッシュ対象外設定のテスト
		$this->assertTrue( is_file( __DIR__.'/testData/publish/published/no_publish/index.html' ) );
		$this->assertTrue( is_file( __DIR__.'/testData/publish/published/no_publish/not_ignore/index.html' ) );
		$this->assertFalse( is_file( __DIR__.'/testData/publish/published/no_publish/ignore/index.html' ) );
		$this->assertFalse( is_file( __DIR__.'/testData/publish/published/no_publish/ignore/index_files/no_publish.css' ) );
		$this->assertFalse( is_file( __DIR__.'/testData/publish/published/no_publish/ignore/index_files/no_publish.js' ) );
		$this->assertFalse( is_file( __DIR__.'/testData/publish/published/no_publish/ignore/index_files/no_publish.html' ) );
		$this->assertTrue( is_file( __DIR__.'/testData/publish/published/no_publish/ext/css.css' ) );
		$this->assertTrue( is_file( __DIR__.'/testData/publish/published/no_publish/ext/scss.css' ) );
		$this->assertFalse( is_file( __DIR__.'/testData/publish/published/no_publish/ext/scss.css.scss' ) );
		$this->assertFalse( is_file( __DIR__.'/testData/publish/published/no_publish/ext/md.html' ) );
		$this->assertFalse( is_file( __DIR__.'/testData/publish/published/no_publish/ext/md.html.md' ) );
		$this->assertFalse( is_file( __DIR__.'/testData/publish/published/no_publish/ext/html.html' ) );

		$this->assertFalse( is_file( __DIR__.'/testData/publish/published/proc_type/ignore.html' ) );
		$this->assertTrue( is_file( __DIR__.'/testData/publish/published/proc_type/pass.html' ) );
		$this->assertEquals(
			file_get_contents( __DIR__.'/testData/publish/published/proc_type/pass.html' ),
			file_get_contents( __DIR__.'/testData/publish/px2/proc_type/pass.html' )
		);



		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/publish/px2/.px_execute.php' ,
			'/?PX=publish.run&path_region=/&paths_ignore[]=/' ,
		] );
		clearstatcache();

		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( is_file( __DIR__.'/testData/publish/published/update_sync_test/new.html' ) );
		$this->assertFalse( is_file( __DIR__.'/testData/publish/published/update_sync_test/old.html' ) );

		$this->assertFalse( is_file( __DIR__.'/testData/publish/published/path_ignored/testpage.html' ) );
		$this->assertTrue( is_file( __DIR__.'/testData/publish/published/path_ignored/untouchable.html' ) );

		$this->assertFalse( is_file( __DIR__.'/testData/publish/px2/files_ignored/index.html' ) );
		$this->assertTrue( is_file( __DIR__.'/testData/publish/published/files_ignored/index.html' ) );
		$this->assertTrue( is_file( __DIR__.'/testData/publish/px2/files_ignored/not_ignored.html' ) );
		$this->assertTrue( is_file( __DIR__.'/testData/publish/published/files_ignored/not_ignored.html' ) );

		$this->assertTrue( is_file( __DIR__.'/testData/publish/published/files_ignored_dist_only/test1.txt' ) );
		$this->assertFalse( is_file( __DIR__.'/testData/publish/px2/files_ignored_dist_only/test1.txt' ) );



		// 後始末
		$output = $this->passthru( [
			'php', __DIR__.'/testData/publish/px2/.px_execute.php', '/?PX=clearcache'
		] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/publish/px2/caches/p/' ) );

	}//testPublishDirectoryTest()


	/**
	 * パブリッシュディレクトリ + 複数のパブリッシュ対象範囲 のテスト
	 */
	public function testPublishDirectoryMultiRegionTest(){
		@$this->fs->rm( __DIR__.'/testData/publish/published/region/' );

		// テストデータの操作をテスト
		$this->assertFalse( is_file( __DIR__.'/testData/publish/published/region/region1/index.html' ) );
		$this->assertFalse( is_file( __DIR__.'/testData/publish/published/region/region2/index.html' ) );
		$this->assertFalse( is_file( __DIR__.'/testData/publish/published/region/region3/index.html' ) );

		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/publish/px2/.px_execute.php' ,
			'/?PX=publish.run&path_region=/region/region1/&paths_region[]=/region/region3/' ,
		] );
		clearstatcache();

		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( is_file( __DIR__.'/testData/publish/published/region/region1/index.html' ) );
		$this->assertFalse( is_file( __DIR__.'/testData/publish/published/region/region2/index.html' ) );
		$this->assertTrue( is_file( __DIR__.'/testData/publish/published/region/region3/index.html' ) );


		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/publish/px2/.px_execute.php' ,
			'/?PX=publish.run&path_region=/region/' ,
		] );
		clearstatcache();

		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( is_file( __DIR__.'/testData/publish/published/region/region1/index.html' ) );
		$this->assertTrue( is_file( __DIR__.'/testData/publish/published/region/region2/index.html' ) );
		$this->assertTrue( is_file( __DIR__.'/testData/publish/published/region/region3/index.html' ) );



		// 後始末
		$output = $this->passthru( [
			'php', __DIR__.'/testData/publish/px2/.px_execute.php', '/?PX=clearcache'
		] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/publish/px2/caches/p/' ) );

	}//testPublishDirectoryMultiRegionTest()


	/**
	 * ファイルを単体でパブリッシュするテスト
	 * @depends testBefore
	 * @depends testStandardPublishBeforeContent
	 */
	public function testStandardSingleFile(){

		// pass扱いになる txt ファイルをパブリッシュ
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=publish.run&path_region=/common/pass_file.txt' ,
		] );
		// var_dump($output);
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( is_file( __DIR__.'/testData/standard/px-files/_sys/ram/publish/htdocs/common/pass_file.txt' ) );


		// 2重拡張子のSCSSファイルを単品パブリッシュ(2つ目の拡張子を付けてコール)
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=publish.run&path_region=/common/styles/sitemap_loaded.css.scss' ,
		] );
		// var_dump($output);
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertFalse( is_file( __DIR__.'/testData/standard/px-files/_sys/ram/publish/htdocs/common/styles/sitemap_loaded.css.scss' ) );
		$this->assertTrue( is_file( __DIR__.'/testData/standard/px-files/_sys/ram/publish/htdocs/common/styles/sitemap_loaded.css' ) );


		// 2重拡張子のSCSSファイルを単品パブリッシュ
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=publish.run&path_region=/common/styles/sitemap_loaded.css' ,
		] );
		// var_dump($output);
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertFalse( is_file( __DIR__.'/testData/standard/px-files/_sys/ram/publish/htdocs/common/styles/sitemap_loaded.css.scss' ) );
		$this->assertTrue( is_file( __DIR__.'/testData/standard/px-files/_sys/ram/publish/htdocs/common/styles/sitemap_loaded.css' ) );

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
	 * サイトマップ生成の途中でパブリッシュするテスト
	 * @depends testBefore
	 * @depends testStandardPublishBeforeContent
	 */
	public function testPublishDuringGeneratingSitemapCache(){

		// 一旦キャッシュを消去し、サイトマップキャッシュ生成中を示すロックファイルを作成する。
		$output = $this->passthru( [
			'php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=clearcache'
		] );
		$this->fs->mkdir_r(__DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/');
		$this->fs->save_file(
			__DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/making_sitemap_cache.lock.txt',
			''
		);

		// サイトマップ生成中にパブリッシュプロセスを発行
		$memo_time1 = time();
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=publish.run&path_region=/index.html&keep_cache=1' ,
		] );
		$memo_time2 = time();
		// var_dump($output);
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		// $this->assertTrue( $memo_time2 - $memo_time1 >= 2 ); // 待ち時間はなくすことにした。 2016-10-28 @tomk79
		$this->assertTrue( is_file( __DIR__.'/testData/standard/px-files/_sys/ram/publish/publish_log.csv' ) );
		$this->assertTrue( is_file( __DIR__.'/testData/standard/px-files/_sys/ram/publish/alert_log.csv' ) );

		$csv = $this->fs->read_csv( __DIR__.'/testData/standard/px-files/_sys/ram/publish/alert_log.csv' );
		$this->assertTrue( is_array( $csv ) );
		$this->assertEquals( $csv[1][1], '/index.html' );
		$this->assertEquals( $csv[1][2], 'Sitemap cache generating is now in progress. This page has been incompletely generated.' );


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
	 * キャッシュを削除するテスト
	 * @depends testBefore
	 * @depends testStandardPublishBeforeContent
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

		// 後始末
		$output = $this->passthru( [
			'php', __DIR__.'/testData/publish/px2/.px_execute.php', '/?PX=clearcache'
		] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/publish/px2/caches/p/' ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/publish/px2/px-files/_sys/ram/caches/sitemaps/' ) );

		//↓realpathが記載されるため削除
		unlink( __DIR__.'/testData/publish/published/php_error/notice.html' );
		unlink( __DIR__.'/testData/publish/published/php_error/warning.html' );
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
