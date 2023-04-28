<?php
/**
 * test for pickles2/px-fw-2.x
 */
class autoindexTest extends PHPUnit\Framework\TestCase{
	private $fs;
	private $utils;

	public function setup() : void{
		mb_internal_encoding('UTF-8');
		$this->fs = new tomk79\filesystem();
		require_once(__DIR__.'/libs/utils.php');
		$this->utils = new \picklesFramework2\tests\utils();
	}



	/**
	 * ページ内索引自動生成テスト
	 */
	public function testAutoIndex(){
		$detect_order = 'UTF-8,eucJP-win,SJIS-win,cp932,EUC-JP,SJIS';

		$output = $this->utils->px_execute(
			'/standard/.px_execute.php',
			'/sample_pages/page1/3.html'
		);

		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertEquals( preg_match( '/'.preg_quote('<h2 id="hash_markdown記法のテストページです。">markdown記法のテストページです。</h2>', '/').'/s', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('<h3 id="hash_見出し文字">見出し文字</h3>', '/').'/s', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('<h3 id="hash_0_見出し文字">見出し文字</h3>', '/').'/s', $output ), 0 );
		$this->assertEquals( preg_match( '/'.preg_quote('<h3 id="hash_1_見出し文字">見出し文字</h3>', '/').'/s', $output ), 0 );
		$this->assertEquals( preg_match( '/'.preg_quote('<h3 id="hash_2_見出し文字">見出し文字</h3>', '/').'/s', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('<h3 id="hash_3_3_見出し文字">3_見出し文字</h3>', '/').'/s', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('<h3 id="hash_4_2_見出し文字">2_見出し文字</h3>', '/').'/s', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('<a href="#cont-already-set-id">もともとid属性を持っている</a>', '/').'/s', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('<h2 id="cont-already-set-id">もともとid属性を持っている</h2>', '/').'/s', $output ), 1 );


		// 後始末
		$output = $this->utils->px_execute( '/standard/.px_execute.php', '/?PX=clearcache' );
		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );

	} // testAutoIndex()

}
