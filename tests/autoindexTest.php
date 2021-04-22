<?php
/**
 * test for pickles2/px-fw-2.x
 */
class autoindexTest extends PHPUnit\Framework\TestCase{
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
	public function testAutoIndex(){
		$detect_order = 'UTF-8,eucJP-win,SJIS-win,EUC-JP,SJIS';

		$output = $this->utils->px_execute(
			'/standard/.px_execute.php',
			'/sample_pages/page1/3.html'
		);
		// var_dump($output);
		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertEquals( preg_match( '/'.preg_quote('<h2>markdown記法のテストページです。</h2>', '/').'/s', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('<span id="hash_見出し文字"></span><h3>見出し文字</h3>', '/').'/s', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('<span id="hash_0_見出し文字"></span><h3>見出し文字</h3>', '/').'/s', $output ), 0 );
		$this->assertEquals( preg_match( '/'.preg_quote('<span id="hash_1_見出し文字"></span><h3>見出し文字</h3>', '/').'/s', $output ), 0 );
		$this->assertEquals( preg_match( '/'.preg_quote('<span id="hash_2_見出し文字"></span><h3>見出し文字</h3>', '/').'/s', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('<span id="hash_3_3_見出し文字"></span><h3>3_見出し文字</h3>', '/').'/s', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('<span id="hash_4_2_見出し文字"></span><h3>2_見出し文字</h3>', '/').'/s', $output ), 1 );



		// 後始末
		// $this->assertTrue( false );
		$output = $this->utils->px_execute( '/standard/.px_execute.php', '/?PX=clearcache' );
		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );

	} // testAutoIndex()

}
