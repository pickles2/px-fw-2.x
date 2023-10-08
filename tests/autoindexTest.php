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
		$output = $this->utils->px_execute(
			'/standard/.px_execute.php',
			'/sample_pages/page1/3.html'
		);

		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertEquals( preg_match( '/'.preg_quote('<div style="margin:2em 5%; border:1px solid #bbb8; padding:1em; background:rgba(124, 124, 124, 0.05); border-radius:5px;">', '/').'/s', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('<div style="margin:0; padding:0; text-align:center; font-weight:bold;">INDEX</div>', '/').'/s', $output ), 1 );
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
	}

	/**
	 * classオプションを与えるテスト
	 */
	public function testAutoIndexClassOption(){
		$orig_options = file_get_contents(__DIR__.'/testData/standard/px-files/config_ex/autoindex_options.array');

		file_put_contents(__DIR__.'/testData/standard/px-files/config_ex/autoindex_options.array', '<?php'."\n".'return array(\'class\' => \'px2-index-list\');');

		$output = $this->utils->px_execute(
			'/standard/.px_execute.php',
			'/sample_pages/page1/3.html'
		);

		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertEquals( preg_match( '/'.preg_quote('<div class="px2-index-list">', '/').'/s', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('<div class="px2-index-list__title">INDEX</div>', '/').'/s', $output ), 1 );
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
		file_put_contents(__DIR__.'/testData/standard/px-files/config_ex/autoindex_options.array', $orig_options);
		$output = $this->utils->px_execute( '/standard/.px_execute.php', '/?PX=clearcache' );
		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );
	}
}
