<?php
/**
 * test for pickles2/px-fw-2.x
 */
class encodingConverterTest extends PHPUnit\Framework\TestCase{
	private $fs;
	private $utils;

	public function setup() : void{
		mb_internal_encoding('UTF-8');
		$this->fs = new tomk79\filesystem();
		require_once(__DIR__.'/libs/utils.php');
		$this->utils = new \picklesFramework2\tests\utils();
	}



	/**
	 * 文字コード・改行コード変換テスト
	 */
	public function testEncodingConverter(){
		$detect_order = 'UTF-8,eucJP-win,SJIS-win,cp932,EUC-JP,SJIS';

		$output = $this->utils->px_execute(
			'/encodingconverter/.px_execute.php',
			'/'
		);
		// var_dump($output);
		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertEquals( preg_match( '/'.preg_quote('<meta charset="Shift_JIS" />', '/').'/s', $output ), 1 );
		$this->assertEquals( preg_match( '/\r\n/s', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote(mb_convert_encoding('<p>美しき日本語ウェブの世界</p>', 'Shift_JIS', 'utf-8'), '/').'/s', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('<p>美しき日本語ウェブの世界</p>', '/').'/s', $output ), 0 );
		// var_dump(mb_detect_encoding($output, $detect_order));
		$this->assertMatchesRegularExpression( '/^(?:SJIS\-win|cp932)$/i', mb_detect_encoding($output, $detect_order) );


		$output = $this->utils->px_execute(
			'/encodingconverter/.px_execute.php',
			'/common/test.css'
		);
		// var_dump($output);
		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertEquals( preg_match( '/'.preg_quote('@charset "EUC-JP"', '/').'/s', $output ), 1 );
		$this->assertEquals( preg_match( '/\r\n/s', $output ), 0 );
		$this->assertEquals( preg_match( '/\n/s', $output ), 1 );
		$this->assertEquals( preg_match( '/\r/s', $output ), 0 );
		$this->assertEquals( preg_match( '/'.preg_quote('<'.'?php', '/').'/s', $output ), 0 );
		$this->assertEquals( preg_match( '/'.preg_quote(mb_convert_encoding('美しき日本語ウェブの世界', 'eucJP-win', 'utf-8'), '/').'/s', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('美しき日本語ウェブの世界', '/').'/s', $output ), 0 );
		// var_dump(mb_detect_encoding($output, $detect_order));
		$this->assertEquals( strtolower('eucJP-win'), strtolower(mb_detect_encoding($output, $detect_order)) );


		$output = $this->utils->px_execute(
			'/encodingconverter/.px_execute.php',
			'/common/test.js'
		);
		// var_dump($output);
		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertEquals( preg_match( '/\r\n/s', $output ), 0 );
		$this->assertEquals( preg_match( '/\n/s', $output ), 0 );
		$this->assertEquals( preg_match( '/\r/s', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote(mb_convert_encoding('美しき日本語ウェブの世界', 'SJIS-win', 'utf-8'), '/').'/s', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('美しき日本語ウェブの世界', '/').'/s', $output ), 0 );
		// var_dump(mb_detect_encoding($output, $detect_order));
		$this->assertMatchesRegularExpression( '/^(?:SJIS\-win|cp932)$/i', mb_detect_encoding($output, $detect_order) );


		// 後始末
		// $this->assertTrue( false );
		$output = $this->utils->px_execute( '/encodingconverter/.px_execute.php', '/?PX=clearcache' );
		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/encodingconverter/caches/p/' ) );

	}// testEncodingConverter()


	/**
	 * direct と pass のテスト
	 */
	public function testEncodingConverterDirectAndPass(){
		$detect_order = 'UTF-8,eucJP-win,SJIS-win,cp932,EUC-JP,SJIS';

		$output = $this->utils->px_execute(
			'/encodingconverter/.px_execute.php',
			'/common/direct/test.css'
		);
		// var_dump($output);
		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertEquals( preg_match( '/'.preg_quote('@charset "utf-8"', '/').'/s', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('<'.'?php', '/').'/s', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('美しき日本語ウェブの世界', '/').'/s', $output ), 1 );
		// var_dump(mb_detect_encoding($output, $detect_order));
		$this->assertEquals( strtolower('utf-8'), strtolower(mb_detect_encoding($output, $detect_order)) );



		$output = $this->utils->px_execute(
			'/encodingconverter/.px_execute.php',
			'/common/direct/test2.css'
		);
		// var_dump($output);
		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertEquals( preg_match( '/\r\n/s', $output ), 0 );
		$this->assertEquals( preg_match( '/\n/s', $output ), 1 );
		$this->assertEquals( preg_match( '/\r/s', $output ), 0 );
		$this->assertEquals( preg_match( '/'.preg_quote('@charset "EUC-JP"', '/').'/s', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('<'.'?php', '/').'/s', $output ), 0 );
		$this->assertEquals( preg_match( '/'.preg_quote(mb_convert_encoding('美しき日本語ウェブの世界', 'eucJP-win', 'utf-8'), '/').'/s', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('美しき日本語ウェブの世界', '/').'/s', $output ), 0 );
		// var_dump(mb_detect_encoding($output, $detect_order));
		$this->assertEquals( strtolower('eucJP-win'), strtolower(mb_detect_encoding($output, $detect_order)) );



		$output = $this->utils->px_execute(
			'/encodingconverter/.px_execute.php',
			'/common/pass/test.css'
		);
		// var_dump($output);
		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertEquals( preg_match( '/'.preg_quote('@charset "utf-8"', '/').'/s', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('<'.'?php', '/').'/s', $output ), 1 );
		$this->assertEquals( preg_match( '/'.preg_quote('美しき日本語ウェブの世界', '/').'/s', $output ), 1 );
		// var_dump(mb_detect_encoding($output, $detect_order));
		$this->assertEquals( strtolower('utf-8'), strtolower(mb_detect_encoding($output, $detect_order)) );



		$output2 = $this->utils->px_execute(
			'/encodingconverter/.px_execute.php',
			'/common/pass/test2.css.css'
		);
		// var_dump($output);
		$this->assertTrue( $this->utils->common_error( $output2 ) );
		$this->assertEquals( $output2, $output );



		// 後始末
		// $this->assertTrue( false );
		$output = $this->utils->px_execute( '/encodingconverter/.px_execute.php', '/?PX=clearcache' );
		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/encodingconverter/caches/p/' ) );

	} // testEncodingConverterDirectAndPass()

}
