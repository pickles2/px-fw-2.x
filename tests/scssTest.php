<?php
/**
 * test for pickles2/px-fw-2.x
 */
class scssTest extends PHPUnit_Framework_TestCase{
	private $fs;
	private $utils;

	public function setup(){
		mb_internal_encoding('UTF-8');
		$this->fs = new tomk79\filesystem();
		require_once(__DIR__.'/libs/utils.php');
		$this->utils = new \picklesFramework2\tests\utils();
	}



	/**
	 * ページ内索引自動生成テスト
	 */
	public function testScss(){
		$detect_order = 'UTF-8,eucJP-win,SJIS-win,EUC-JP,SJIS';

		$output = $this->utils->px_execute(
			'/standard/.px_execute.php',
			'/common/styles/test1.css'
		);
		// var_dump($output);
		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertEquals( preg_match( '/'.preg_quote('.test-a .test-b .test-b p {', '/').'/s', $output ), 1 );



		// 後始末
		// $this->assertTrue( false );
		$output = $this->utils->px_execute( '/standard/.px_execute.php', '/?PX=clearcache' );
		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );

	} // testScss()

}
