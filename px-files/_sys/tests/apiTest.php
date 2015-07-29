<?php
/**
 * test for tomk79\PxFW-2.x
 */
class apiTest extends PHPUnit_Framework_TestCase{
	private $fs;

	public function setup(){
		mb_internal_encoding('UTF-8');
		$this->fs = new tomk79\filesystem();
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
		$this->assertEquals( 1, preg_match('/^[0-9]+\.[0-9]+\.[0-9]+(?:\-(?:alpha|beta|rc)(?:\.[a-zA-Z0-9][a-zA-Z0-9\.]*)?)?(?:\+[a-zA-Z0-9\.]+)?$/s', json_decode($output)) );


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
		// api.get.parent
		$output = $this->passthru( [
			'php', __DIR__.'/testData/standard/.px_execute.php' ,
			'/sample_pages/?PX=api.get.parent' ,
		] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( $output, '' );

		$output = $this->passthru( [
			'php', __DIR__.'/testData/standard/.px_execute.php' ,
			'/sample_pages/page2/2.html?PX=api.get.parent' ,
		] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( $output, ':auto_page_id.22' );


		// -------------------
		// api.get.children
		$output = $this->passthru( [
			'php', __DIR__.'/testData/standard/.px_execute.php' ,
			'/sample_pages/?PX=api.get.children' ,
		] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( $output[0], ':auto_page_id.16' );
		$this->assertEquals( $output[5], ':auto_page_id.21' );
		$this->assertEquals( count($output), 6 );

		$output = $this->passthru( [
			'php', __DIR__.'/testData/prevnext/.px_execute.php' ,
			'/bros3/?PX=api.get.children' ,
		] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( $output[0], 'Bros3-2' );
		$this->assertEquals( $output[2], 'Bros3-6' );
		$this->assertEquals( count($output), 3 );

		$output = $this->passthru( [
			'php', __DIR__.'/testData/prevnext/.px_execute.php' ,
			'/bros3/?PX=api.get.children&filter=false' ,
		] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( $output[0], 'Bros3-2' );
		$this->assertEquals( $output[4], 'Bros3-6' );
		$this->assertEquals( count($output), 5 );


		// -------------------
		// api.get.bros
		$output = $this->passthru( [
			'php', __DIR__.'/testData/standard/.px_execute.php' ,
			'/sample_pages/page1/1.html?PX=api.get.bros' ,
		] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( $output[0], ':auto_page_id.16' );
		$this->assertEquals( $output[5], ':auto_page_id.21' );
		$this->assertEquals( count($output), 6 );

		$output = $this->passthru( [
			'php', __DIR__.'/testData/prevnext/.px_execute.php' ,
			'/bros3/3.html?PX=api.get.bros' ,
		] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( $output[0], 'Bros3-2' );
		$this->assertEquals( $output[2], 'Bros3-6' );
		$this->assertEquals( count($output), 3 );

		$output = $this->passthru( [
			'php', __DIR__.'/testData/prevnext/.px_execute.php' ,
			'/bros3/3.html?PX=api.get.bros&filter=false' ,
		] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( $output[0], 'Bros3-2' );
		$this->assertEquals( $output[4], 'Bros3-6' );
		$this->assertEquals( count($output), 5 );


		// -------------------
		// api.get.bros_next
		$output = $this->passthru( [
			'php', __DIR__.'/testData/standard/.px_execute.php' ,
			'/sample_pages/page1/1.html?PX=api.get.bros_next' ,
		] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( $output, ':auto_page_id.17' );


		// -------------------
		// api.get.bros_prev
		$output = $this->passthru( [
			'php', __DIR__.'/testData/standard/.px_execute.php' ,
			'/sample_pages/page1/2.html?PX=api.get.bros_prev' ,
		] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( $output, ':auto_page_id.16' );


		// -------------------
		// api.get.next
		$output = $this->passthru( [
			'php', __DIR__.'/testData/standard/.px_execute.php' ,
			'/sample_pages/page1/1.html?PX=api.get.next' ,
		] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( $output, ':auto_page_id.17' );


		// -------------------
		// api.get.prev
		$output = $this->passthru( [
			'php', __DIR__.'/testData/standard/.px_execute.php' ,
			'/sample_pages/page1/2.html?PX=api.get.prev' ,
		] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( $output, ':auto_page_id.16' );


		// -------------------
		// api.get.breadcrumb_array
		$output = $this->passthru( [
			'php', __DIR__.'/testData/standard/.px_execute.php' ,
			'/sample_pages/page2/2.html?PX=api.get.breadcrumb_array' ,
		] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( $output[0], '' );
		$this->assertEquals( $output[1], ':auto_page_id.22' );
		$this->assertEquals( count($output), 2 );


		// -------------------
		// api.get.dynamic_path_info
		$output = $this->passthru( [
			'php', __DIR__.'/testData/standard/.px_execute.php' ,
			'/sample_pages/?PX=api.get.dynamic_path_info&path='.urlencode('/dynamicPath/param.html') ,
		] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( $output->path, '/dynamicPath/' );
		$this->assertEquals( $output->path_original, '/dynamicPath/{*}' );
		$this->assertEquals( $output->id, ':auto_page_id.11' );

		// -------------------
		// api.get.bind_dynamic_path_param
		$output = $this->passthru( [
			'php', __DIR__.'/testData/standard/.px_execute.php' ,
			'/sample_pages/?PX=api.get.bind_dynamic_path_param&path='.urlencode('/dynamicPath/{*}').'&param='.urlencode(json_encode([''=>'abc.html'])) ,
		] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( $output, '/dynamicPath/abc.html' );

		$output = $this->passthru( [
			'php', __DIR__.'/testData/standard/.px_execute.php' ,
			'/sample_pages/?PX=api.get.bind_dynamic_path_param&path='.urlencode('/dynamicPath/id_{$id}/name_{$name}/{*}').'&param='.urlencode(json_encode([''=>'abc.html','id'=>'id','name'=>'name'])) ,
		] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( $output, '/dynamicPath/id_id/name_name/abc.html' );


		// -------------------
		// api.get.path_homedir
		$output = $this->passthru( [
			'php', __DIR__.'/testData/standard/.px_execute.php' ,
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
			'php', __DIR__.'/testData/standard/.px_execute.php' ,
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
		// api.get.path_content
		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=api.get.path_content'] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( $output, '/index.html' );

		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/dynamicPath/param.html?PX=api.get.path_content'] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( $output, '/dynamicPath/index.html' );

		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/sample_pages/page1/3.html?PX=api.get.path_content'] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( $output, '/sample_pages/page1/3.html.md' );

		// -------------------
		// api.get.path_files
		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=api.get.path_files'] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( $output, '/index_files/' );

		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=api.get.path_files&path_resource='.urlencode('dummypath/sample.png')] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( $output, '/index_files/dummypath/sample.png' );


		// -------------------
		// api.get.realpath_files
		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=api.get.realpath_files'] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( realpath($output), realpath(__DIR__.'/testData/standard/index_files/') );

		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=api.get.realpath_files&path_resource='.urlencode('dummypath/sample.png')] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( realpath($output), realpath(__DIR__.'/testData/standard/index_files/dummypath/sample.png') );


		// -------------------
		// api.get.path_files_cache
		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=api.get.path_files_cache'] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( $output, '/caches/c/index_files/' );

		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=api.get.path_files_cache&path_resource='.urlencode('dummypath/sample.png')] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( $output, '/caches/c/index_files/dummypath/sample.png' );


		// -------------------
		// api.get.realpath_files_cache
		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=api.get.realpath_files_cache'] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( realpath($output), realpath(__DIR__.'/testData/standard/caches/c/index_files/') );

		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=api.get.realpath_files_cache&path_resource='.urlencode('dummypath/sample.png')] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( realpath($output), realpath(__DIR__.'/testData/standard/caches/c/index_files/dummypath/sample.png') );


		// -------------------
		// api.get.realpath_files_private_cache
		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=api.get.realpath_files_private_cache'] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( realpath($output), realpath(__DIR__.'/testData/standard/px-files/_sys/ram/caches/c/index_files/') );

		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=api.get.realpath_files_private_cache&path_resource='.urlencode('dummypath/sample.png')] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( realpath($output), realpath(__DIR__.'/testData/standard/px-files/_sys/ram/caches/c/index_files/dummypath/sample.png') );


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
		$this->assertEquals( $output, 'pickles2.pxt.jp' );


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

		$output = $this->passthru( [
			'php', __DIR__.'/testData/prevnext/.px_execute.php', '/?PX=clearcache'
		] );
	}//testPxApiGetAnything();


	/**
	 * /?PX=api.is.* から値を取得するテスト
	 */
	public function testPxApiIsAnything(){

		// -------------------
		// api.is.match_dynamic_path
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=api.is.match_dynamic_path&path='.urlencode('/dynamicPath/') ,
		] );
		clearstatcache();

		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertTrue( $output );

		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=api.is.match_dynamic_path&path='.urlencode('/dynamicPath/aaa.html') ,
		] );
		clearstatcache();

		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertTrue( $output );


		// -------------------
		// api.is.page_in_breadcrumb
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/sample_pages/page2/?PX=api.is.page_in_breadcrumb&path='.urlencode('/') ,
		] );
		clearstatcache();

		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertTrue( $output );

		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/sample_pages/page2/1.htm?PX=api.is.page_in_breadcrumb&path='.urlencode('/sample_pages/page2/') ,
		] );
		clearstatcache();

		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertTrue( $output );


		// -------------------
		// api.is.ignore_path
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=api.is.ignore_path&path='.urlencode('/sample_pages/page2/') ,
		] );
		clearstatcache();

		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertFalse( $output );

		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=api.is.ignore_path&path='.urlencode('/vendor/') ,
		] );
		clearstatcache();

		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertTrue( $output );






		// 後始末
		$output = $this->passthru( [
			'php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=clearcache'
		] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/' ) );
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


	/**
	 * $conf->path_cache を変更して値の変化を確認するテスト
	 * @depends testPxApiGetAnything
	 * @depends testPxApiIsAnything
	 * @dataProvider confPathFilesProvider
	 */
	public function testConfPathCache($test_name, $conf_path_files, $page_path, $resource_path, $result){

		// -------------------
		// api.get.path_files
		clearstatcache();
		$this->fs->save_file( __DIR__.'/testData/standard/px-files/config_ex/path_files.txt', $conf_path_files );
		clearstatcache();
		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', $page_path.'?PX=api.get.path_files&path_resource='.urlencode($resource_path)] );
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( $output, $result );

		// -------------------
		// api.get.realpath_files
		$output = $this->passthru( ['php', __DIR__.'/testData/standard/.px_execute.php', $page_path.'?PX=api.get.realpath_files&path_resource='.urlencode($resource_path)] );
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( $this->fs->get_realpath(realpath('/').$output), $this->fs->get_realpath(__DIR__.'/testData/standard'.$result) );



		// 後始末
		$output = $this->passthru( [
			'php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=clearcache'
		] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertFalse( is_dir( __DIR__.'/testData/standard/caches/p/' ) );
		$this->assertFalse( is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/' ) );
	}//testConfPathCache();







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
