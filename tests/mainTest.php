<?php
/**
 * test for pickles2/px-fw-2.x
 */
class mainTest extends PHPUnit\Framework\TestCase{
	private $fs;

	public function setUp() : void{
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
		$this->assertEquals( $toppage_info['title'], '<HOME>' );
		$this->assertEquals( $toppage_info['path'], '/index.html' );
		$this->assertEquals( $_SERVER['HTTP_USER_AGENT'], '' );

		$this->assertEquals( $px->get_scheme(), 'http' );
		$this->assertEquals( $px->get_domain(), 'pickles2.pxt.jp' );

		$this->assertEquals( $px->lang(), 'ja' );
		$this->assertEquals( $px->set_lang('undefined-lang'), false );
		$this->assertEquals( $px->lang(), 'ja' );
		$this->assertEquals( $px->set_lang('en'), true );
		$this->assertEquals( $px->lang(), 'en' );


		// 動的なプロパティを登録する
		$custom_property = 'custom_property';
		$px->custom_property = $custom_property;
		$this->assertEquals( $px->custom_property, 'custom_property' );
		$px->conf = $custom_property;
		$this->assertEquals( $px->conf, 'custom_property' );


		// サブリクエストでキャッシュを消去
		$output = $px->internal_sub_request(
			'/index.html?PX=clearcache',
			array(),
			$vars
		);
		$error = $px->get_errors();
		$this->assertTrue( is_string($output) );
		$this->assertSame( 0, $vars ); // <- strict equals
		$this->assertSame( array(), $error );


		chdir($cd);
		$_SERVER['SCRIPT_FILENAME'] = $SCRIPT_FILENAME;

		$px->__destruct();// <- required on Windows
		unset($px);
	}

}
