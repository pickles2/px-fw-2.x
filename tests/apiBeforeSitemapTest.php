<?php
/**
 * test for pickles2\PxFW-2.x
 */
class apiBeforeSitemapTest extends PHPUnit\Framework\TestCase{
	private $fs;
	private $utils;

	public function setup() : void{
		mb_internal_encoding('UTF-8');
		$this->fs = new tomk79\filesystem();
		require_once(__DIR__.'/libs/utils.php');
		$this->utils = new \picklesFramework2\tests\utils();
	}




	/**
	 * /?PX=api.get.* から値を取得するテスト
	 */
	public function testPxApiGetAnything(){

		// -------------------
		// api.get.vertion
		$output = $this->utils->passthru( [
			PHP_BINARY,
			__DIR__.'/testData/apiBeforeSitemap/.px_execute.php' ,
			'/?PX=api.get.version' ,
		] );
		clearstatcache();

		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertEquals( 1, preg_match('/^[0-9]+\.[0-9]+\.[0-9]+(?:\-(?:alpha|beta|rc)(?:\.[a-zA-Z0-9][a-zA-Z0-9\.]*)?)?(?:\+[a-zA-Z0-9\.]+)?$/s', json_decode($output)) );


		// -------------------
		// api.get.config
		$output = $this->utils->passthru( [
			PHP_BINARY,
			__DIR__.'/testData/apiBeforeSitemap/.px_execute.php' ,
			'/?PX=api.get.config' ,
		] );
		clearstatcache();

		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output->name, 'Pickles 2' );
		$this->assertEquals( $output->path_controot, '/' );


		// -------------------
		// api.get.sitemap
		$output = $this->utils->passthru( [
			PHP_BINARY,
			__DIR__.'/testData/apiBeforeSitemap/.px_execute.php' ,
			'/?PX=api.get.sitemap' ,
		] );
		clearstatcache();

		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertFalse( $output );

		// -------------------
		// api.get.page_info
		$output = $this->utils->passthru( [
			PHP_BINARY,
			__DIR__.'/testData/apiBeforeSitemap/.px_execute.php' ,
			'/?PX=api.get.page_info&path='.urlencode('/sample_pages/page2/') ,
		] );
		clearstatcache();

		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertFalse( $output );

		$output = $this->utils->passthru( [
			PHP_BINARY,
			__DIR__.'/testData/apiBeforeSitemap/.px_execute.php' ,
			'/?PX=api.get.page_info&path='.urlencode('/dynamicPath/aaaaa.html') ,
		] );
		clearstatcache();

		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertFalse( $output );

		$output = $this->utils->passthru( [
			PHP_BINARY,
			__DIR__.'/testData/apiBeforeSitemap/.px_execute.php' ,
			'/?PX=api.get.page_info&path='.urlencode('/dynamicPath/test1/') ,
		] );
		clearstatcache();

		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertFalse( $output );


		// -------------------
		// api.get.page_info
		$output = $this->utils->passthru( [
			PHP_BINARY,
			__DIR__.'/testData/apiBeforeSitemap/.px_execute.php' ,
			'/?PX=api.get.page_info&path='.urlencode('/sample_pages/page2/') ,
		] );
		clearstatcache();
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertFalse( $output );

		$output = $this->utils->passthru( [
			PHP_BINARY,
			__DIR__.'/testData/apiBeforeSitemap/.px_execute.php' ,
			'/?PX=api.get.page_info&path='.urlencode('/dynamicPath/aaaaa.html') ,
		] );
		clearstatcache();


		// -------------------
		// api.get.parent
		$output = $this->utils->passthru( [
			PHP_BINARY, __DIR__.'/testData/apiBeforeSitemap/.px_execute.php' ,
			'/sample_pages/?PX=api.get.parent' ,
		] );
		clearstatcache();
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertFalse( $output );

		$output = $this->utils->passthru( [
			PHP_BINARY, __DIR__.'/testData/apiBeforeSitemap/.px_execute.php' ,
			'/sample_pages/page2/2.html?PX=api.get.parent' ,
		] );
		clearstatcache();
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertFalse( $output );


		// -------------------
		// api.get.actors
		$output = $this->utils->passthru( [
			PHP_BINARY, __DIR__.'/testData/apiBeforeSitemap/.px_execute.php' ,
			'/sample_pages/page3/2.html?PX=api.get.actors' ,
		] );
		clearstatcache();
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertFalse( $output );


		// -------------------
		// api.get.role
		$output = $this->utils->passthru( [
			PHP_BINARY, __DIR__.'/testData/apiBeforeSitemap/.px_execute.php' ,
			'/sample_pages/page3/2-actor1.html?PX=api.get.role' ,
		] );
		clearstatcache();
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertFalse( $output );


		// -------------------
		// api.get.children
		$output = $this->utils->passthru( [
			PHP_BINARY, __DIR__.'/testData/apiBeforeSitemap/.px_execute.php' ,
			'/sample_pages/?PX=api.get.children' ,
		] );
		clearstatcache();
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertFalse( $output );


		// -------------------
		// api.get.bros
		$output = $this->utils->passthru( [
			PHP_BINARY, __DIR__.'/testData/apiBeforeSitemap/.px_execute.php' ,
			'/sample_pages/page1/1.html?PX=api.get.bros' ,
		] );
		clearstatcache();
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertFalse( $output );


		// -------------------
		// api.get.bros_next
		$output = $this->utils->passthru( [
			PHP_BINARY, __DIR__.'/testData/apiBeforeSitemap/.px_execute.php' ,
			'/sample_pages/page1/1.html?PX=api.get.bros_next' ,
		] );
		clearstatcache();
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertFalse( $output );


		// -------------------
		// api.get.bros_prev
		$output = $this->utils->passthru( [
			PHP_BINARY, __DIR__.'/testData/apiBeforeSitemap/.px_execute.php' ,
			'/sample_pages/page1/2.html?PX=api.get.bros_prev' ,
		] );
		clearstatcache();
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertFalse( $output );


		// -------------------
		// api.get.next
		$output = $this->utils->passthru( [
			PHP_BINARY, __DIR__.'/testData/apiBeforeSitemap/.px_execute.php' ,
			'/sample_pages/page1/1.html?PX=api.get.next' ,
		] );
		clearstatcache();
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertFalse( $output );


		// -------------------
		// api.get.prev
		$output = $this->utils->passthru( [
			PHP_BINARY, __DIR__.'/testData/apiBeforeSitemap/.px_execute.php' ,
			'/sample_pages/page1/2.html?PX=api.get.prev' ,
		] );
		clearstatcache();
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertFalse( $output );


		// -------------------
		// api.get.breadcrumb_array
		$output = $this->utils->passthru( [
			PHP_BINARY, __DIR__.'/testData/apiBeforeSitemap/.px_execute.php' ,
			'/sample_pages/page2/2.html?PX=api.get.breadcrumb_array' ,
		] );
		clearstatcache();
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertFalse( $output );


		// -------------------
		// api.get.dynamic_path_info
		$output = $this->utils->passthru( [
			PHP_BINARY, __DIR__.'/testData/apiBeforeSitemap/.px_execute.php' ,
			'/sample_pages/?PX=api.get.dynamic_path_info&path='.urlencode('/dynamicPath/param.html') ,
		] );
		clearstatcache();
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertFalse( $output );


		// -------------------
		// api.get.bind_dynamic_path_param
		$output = $this->utils->passthru( [
			PHP_BINARY, __DIR__.'/testData/apiBeforeSitemap/.px_execute.php' ,
			'/sample_pages/?PX=api.get.bind_dynamic_path_param&path='.urlencode('/dynamicPath/{*}').'&param='.urlencode(json_encode([''=>'abc.html'])) ,
		] );
		clearstatcache();
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertFalse( $output );

		$output = $this->utils->passthru( [
			PHP_BINARY, __DIR__.'/testData/apiBeforeSitemap/.px_execute.php' ,
			'/sample_pages/?PX=api.get.bind_dynamic_path_param&path='.urlencode('/dynamicPath/id_{$id}/name_{$name}/{*}').'&param='.urlencode(json_encode([''=>'abc.html','id'=>'id','name'=>'name'])) ,
		] );
		clearstatcache();
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertFalse( $output );


		// -------------------
		// api.get.path_homedir (deprecated)
		$output = $this->utils->passthru( [
			PHP_BINARY, __DIR__.'/testData/apiBeforeSitemap/.px_execute.php' ,
			'/?PX=api.get.path_homedir' ,
		] );
		clearstatcache();

		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( realpath($output), realpath(__DIR__.'/testData/apiBeforeSitemap/px-files') );
		$this->assertFalse( strpos($output, '\\') );


		// -------------------
		// api.get.realpath_homedir
		$output = $this->utils->passthru( [
			PHP_BINARY, __DIR__.'/testData/apiBeforeSitemap/.px_execute.php' ,
			'/?PX=api.get.realpath_homedir' ,
		] );
		clearstatcache();

		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( realpath($output), realpath(__DIR__.'/testData/apiBeforeSitemap/px-files') );
		$this->assertFalse( strpos($output, '\\') );


		// -------------------
		// api.get.path_controot
		$output = $this->utils->passthru( [
			PHP_BINARY, __DIR__.'/testData/apiBeforeSitemap/.px_execute.php' ,
			'/?PX=api.get.path_controot' ,
		] );
		clearstatcache();

		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output, '/' );


		// -------------------
		// api.get.path_docroot (deprecated)
		$output = $this->utils->passthru( [
			PHP_BINARY,
			__DIR__.'/testData/apiBeforeSitemap/.px_execute.php' ,
			'/?PX=api.get.path_docroot' ,
		] );
		clearstatcache();

		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( realpath($output), realpath(__DIR__.'/testData/apiBeforeSitemap') );
		$this->assertFalse( strpos($output, '\\') );

		// -------------------
		// api.get.realpath_docroot
		$output = $this->utils->passthru( [
			PHP_BINARY,
			__DIR__.'/testData/apiBeforeSitemap/.px_execute.php' ,
			'/?PX=api.get.realpath_docroot' ,
		] );
		clearstatcache();

		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( realpath($output), realpath(__DIR__.'/testData/apiBeforeSitemap') );
		$this->assertFalse( strpos($output, '\\') );


		// -------------------
		// api.get.path_content
		$output = $this->utils->passthru( [PHP_BINARY, __DIR__.'/testData/apiBeforeSitemap/.px_execute.php', '/?PX=api.get.path_content'] );
		clearstatcache();
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertNull( $output );


		// -------------------
		// api.get.path_files
		$output = $this->utils->passthru( [PHP_BINARY, __DIR__.'/testData/apiBeforeSitemap/.px_execute.php', '/?PX=api.get.path_files'] );
		clearstatcache();
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertFalse( $output );


		// -------------------
		// api.get.realpath_files
		$output = $this->utils->passthru( [PHP_BINARY, __DIR__.'/testData/apiBeforeSitemap/.px_execute.php', '/?PX=api.get.realpath_files'] );
		clearstatcache();
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertFalse( $output );


		// -------------------
		// api.get.path_files_cache
		$output = $this->utils->passthru( [PHP_BINARY, __DIR__.'/testData/apiBeforeSitemap/.px_execute.php', '/?PX=api.get.path_files_cache'] );
		clearstatcache();
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertFalse( $output );


		// -------------------
		// api.get.realpath_files_cache
		$output = $this->utils->passthru( [PHP_BINARY, __DIR__.'/testData/apiBeforeSitemap/.px_execute.php', '/?PX=api.get.realpath_files_cache'] );
		clearstatcache();
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertFalse( $output );


		// -------------------
		// api.get.realpath_files_private_cache
		$output = $this->utils->passthru( [PHP_BINARY, __DIR__.'/testData/apiBeforeSitemap/.px_execute.php', '/?PX=api.get.realpath_files_private_cache'] );
		clearstatcache();
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertFalse( $output );


		// -------------------
		// api.get.domain
		$output = $this->utils->passthru( [
			PHP_BINARY,
			__DIR__.'/testData/apiBeforeSitemap/.px_execute.php' ,
			'/?PX=api.get.domain' ,
		] );
		clearstatcache();

		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output, 'pickles2.pxt.jp' );


		// -------------------
		// api.get.directory_index
		$output = $this->utils->passthru( [
			PHP_BINARY,
			__DIR__.'/testData/apiBeforeSitemap/.px_execute.php' ,
			'/?PX=api.get.directory_index' ,
		] );
		clearstatcache();

		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output[0], 'index.html' );

		// -------------------
		// api.get.directory_index_primary
		$output = $this->utils->passthru( [
			PHP_BINARY,
			__DIR__.'/testData/apiBeforeSitemap/.px_execute.php' ,
			'/?PX=api.get.directory_index_primary' ,
		] );
		clearstatcache();

		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output, 'index.html' );


		// -------------------
		// api.get.path_proc_type
		$output = $this->utils->passthru( [
			PHP_BINARY,
			__DIR__.'/testData/apiBeforeSitemap/.px_execute.php' ,
			'/sample_pages/page2/?PX=api.get.path_proc_type' ,
		] );
		clearstatcache();

		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output, 'html' );

		$output = $this->utils->passthru( [
			PHP_BINARY,
			__DIR__.'/testData/apiBeforeSitemap/.px_execute.php' ,
			'/common/notexists.png?PX=api.get.path_proc_type' ,
		] );
		clearstatcache();

		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output, 'pass' );

		$output = $this->utils->passthru( [
			PHP_BINARY,
			__DIR__.'/testData/apiBeforeSitemap/.px_execute.php' ,
			'/vendor/autoload.php?PX=api.get.path_proc_type' ,
		] );
		clearstatcache();

		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output, 'ignore' );



		// -------------------
		// api.get.href
		$output = $this->utils->passthru( [
			PHP_BINARY,
			__DIR__.'/testData/apiBeforeSitemap/.px_execute.php' ,
			'/?PX=api.get.href&linkto='.urlencode('/sample_pages/page2/index.html') ,
		] );
		clearstatcache();
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertFalse( $output );






		// 後始末
		$output = $this->utils->passthru( [
			PHP_BINARY, __DIR__.'/testData/apiBeforeSitemap/.px_execute.php', '/?PX=clearcache'
		] );
		clearstatcache();
		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/apiBeforeSitemap/caches/p/' ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/apiBeforeSitemap/px-files/_sys/ram/caches/sitemaps/' ) );

		$output = $this->utils->passthru( [
			PHP_BINARY, __DIR__.'/testData/prevnext/.px_execute.php', '/?PX=clearcache'
		] );
	}//testPxApiGetAnything();


	/**
	 * /?PX=api.is.* から値を取得するテスト
	 */
	public function testPxApiIsAnything(){

		// -------------------
		// api.is.match_dynamic_path
		$output = $this->utils->passthru( [
			PHP_BINARY,
			__DIR__.'/testData/apiBeforeSitemap/.px_execute.php' ,
			'/?PX=api.is.match_dynamic_path&path='.urlencode('/dynamicPath/') ,
		] );
		clearstatcache();

		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertFalse( $output );

		$output = $this->utils->passthru( [
			PHP_BINARY,
			__DIR__.'/testData/apiBeforeSitemap/.px_execute.php' ,
			'/?PX=api.is.match_dynamic_path&path='.urlencode('/dynamicPath/aaa.html') ,
		] );
		clearstatcache();

		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertFalse( $output );


		// -------------------
		// api.is.page_in_breadcrumb
		$output = $this->utils->passthru( [
			PHP_BINARY,
			__DIR__.'/testData/apiBeforeSitemap/.px_execute.php' ,
			'/sample_pages/page2/?PX=api.is.page_in_breadcrumb&path='.urlencode('/') ,
		] );
		clearstatcache();

		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertFalse( $output );


		// -------------------
		// api.is.ignore_path
		$output = $this->utils->passthru( [
			PHP_BINARY,
			__DIR__.'/testData/apiBeforeSitemap/.px_execute.php' ,
			'/?PX=api.is.ignore_path&path='.urlencode('/sample_pages/page2/') ,
		] );
		clearstatcache();

		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertFalse( $output );

		$output = $this->utils->passthru( [
			PHP_BINARY,
			__DIR__.'/testData/apiBeforeSitemap/.px_execute.php' ,
			'/?PX=api.is.ignore_path&path='.urlencode('/vendor/') ,
		] );
		clearstatcache();

		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertTrue( $output );






		// 後始末
		$output = $this->utils->passthru( [
			PHP_BINARY, __DIR__.'/testData/apiBeforeSitemap/.px_execute.php', '/?PX=clearcache'
		] );
		clearstatcache();
		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/apiBeforeSitemap/caches/p/' ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/apiBeforeSitemap/px-files/_sys/ram/caches/sitemaps/' ) );
	}//testPxApiIsAnything();


	/**
	 * dataProvidor: ディレクトリ一覧
	 */
	public function confPathFilesProvider(){
		return array(
			array('common-ext-dot', '/common/{$dirname}/{$filename}.{$ext}.files/',                  '/',             '/sample_image.png', '/common/index.html.files/sample_image.png'),
			array('noslashes',      'common/{$dirname}/{$filename}.{$ext}.files',                    '/abc/def.html', '/sample_image.png', '/common/abc/def.html.files/sample_image.png'),
			array('manyslashes',    '/////common/////{$dirname}////{$filename}.{$ext}.files///////', '/',             '/sample_image.png', '/common/index.html.files/sample_image.png'),
			array('default',        '{$dirname}/{$filename}_files/',                                 '/',             '/sample_image.png', '/index_files/sample_image.png'),
		);
	}

}
