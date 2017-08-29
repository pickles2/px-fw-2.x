<?php
/**
 * test for pickles2/px-fw-2.x
 */
class mainTest extends PHPUnit_Framework_TestCase{
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
		$SCRIPT_FILENAME = $_SERVER['SCRIPT_FILENAME'];
		chdir(__DIR__.'/testData/standard/');
		$_SERVER['SCRIPT_FILENAME'] = __DIR__.'/testData/standard/.px_execute.php';

		$px = new picklesFramework2\px('./px-files/');
		$toppage_info = $px->site()->get_page_info('');
		// var_dump($toppage_info);
		$this->assertEquals( $toppage_info['title'], '<HOME>' );
		$this->assertEquals( $toppage_info['path'], '/index.html' );
		$this->assertEquals( $_SERVER['HTTP_USER_AGENT'], '' );


		// サブリクエストでキャッシュを消去
		$output = $px->internal_sub_request(
			'/index.html?PX=clearcache',
			array(),
			$vars
		);
		$error = $px->get_errors();
		// var_dump($output);
		// var_dump($vars);
		// var_dump($error);
		$this->assertTrue( is_string($output) );
		$this->assertSame( 0, $vars ); // <- strict equals
		$this->assertSame( array(), $error );


		chdir($cd);
		$_SERVER['SCRIPT_FILENAME'] = $SCRIPT_FILENAME;

		$px->__destruct();// <- required on Windows
		unset($px);
	}

}
