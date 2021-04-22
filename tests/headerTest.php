<?php
/**
 * test for pickles2/px-fw-2.x
 */
class headerTest extends PHPUnit_Framework_TestCase{
	private $fs;
	private $utils;

	public function setup(){
		mb_internal_encoding('UTF-8');
		$this->fs = new tomk79\filesystem();
		require_once(__DIR__.'/libs/utils.php');
		$this->utils = new \picklesFramework2\tests\utils();
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
		$output = $this->utils->px_execute( '/standard/.px_execute.php', '/?PX=clearcache' );
		clearstatcache();
		// var_dump($output);
		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/' ) );

	}

}
