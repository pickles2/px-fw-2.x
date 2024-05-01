<?php
/**
 * test for tomk79\PxFW-2.x
 */
class prevnextTest extends PHPUnit\Framework\TestCase{
	private $fs;
	private $utils;

	public function setup() : void{
		mb_internal_encoding('UTF-8');
		$this->fs = new tomk79\filesystem();
		require_once(__DIR__.'/libs/utils.php');
		$this->utils = new \picklesFramework2\tests\utils();

		// 前処理
		$output = $this->utils->passthru( [
			PHP_BINARY, __DIR__.'/testData/prevnext/.px_execute.php', '/?PX=clearcache'
		] );
	}


	/**
	 * 前のページ、次のページ をテスト
	 */
	public function testPrevNext(){
		// 最初と最後のページ
		$output = $this->utils->passthru( [ PHP_BINARY, __DIR__.'/testData/prevnext/.px_execute.php', '/?PX=api.get.prev' ] );
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertFalse( $output );

		$output = $this->utils->passthru( [ PHP_BINARY, __DIR__.'/testData/prevnext/.px_execute.php', '/?PX=api.get.next' ] );
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output, 'Bros1' );

		$output = $this->utils->passthru( [ PHP_BINARY, __DIR__.'/testData/prevnext/.px_execute.php', '/last.html?PX=api.get.next' ] );
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertFalse( $output );

		// 階層が深いページのテスト
		$output = $this->utils->passthru( [ PHP_BINARY, __DIR__.'/testData/prevnext/.px_execute.php', '/bros2/2/?PX=api.get.next' ] );
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output, 'Bros-2-2-3' );

		$output = $this->utils->passthru( [ PHP_BINARY, __DIR__.'/testData/prevnext/.px_execute.php', '/bros2/nextbros.html?PX=api.get.prev' ] );
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output, 'Bros-2-2-3-4-deep' );

		// list_flg がないページ
		$output = $this->utils->passthru( [ PHP_BINARY, __DIR__.'/testData/prevnext/.px_execute.php', '/bros3/2.html?PX=api.get.next' ] );
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output, 'Bros3-4' );

		$output = $this->utils->passthru( [ PHP_BINARY, __DIR__.'/testData/prevnext/.px_execute.php', '/bros3/4.html?PX=api.get.prev' ] );
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output, 'Bros3-2' );

		$output = $this->utils->passthru( [ PHP_BINARY, __DIR__.'/testData/prevnext/.px_execute.php', '/bros3/2.html?PX=api.get.next&filter=false' ] );
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output, 'Bros3-3' );

		$output = $this->utils->passthru( [ PHP_BINARY, __DIR__.'/testData/prevnext/.px_execute.php', '/bros3/4.html?PX=api.get.prev&filter=false' ] );
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output, 'Bros3-3' );

		// スキップするテスト
		// popup と alias
		$output = $this->utils->passthru( [ PHP_BINARY, __DIR__.'/testData/prevnext/.px_execute.php', '/skips/normalpage.html?PX=api.get.next' ] );
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output, 'NormalPage2' );

		$output = $this->utils->passthru( [ PHP_BINARY, __DIR__.'/testData/prevnext/.px_execute.php', '/skips/normalpage-2.html?PX=api.get.next' ] );
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output, 'NormalPage3' );

		$output = $this->utils->passthru( [ PHP_BINARY, __DIR__.'/testData/prevnext/.px_execute.php', '/skips/normalpage-3.html?PX=api.get.prev' ] );
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output, 'NormalPage2' );

		$output = $this->utils->passthru( [ PHP_BINARY, __DIR__.'/testData/prevnext/.px_execute.php', '/skips/normalpage-2.html?PX=api.get.prev' ] );
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output, 'NormalPage1' );


		// 後始末
		$output = $this->utils->passthru( [
			PHP_BINARY, __DIR__.'/testData/prevnext/.px_execute.php', '/?PX=clearcache'
		] );

		clearstatcache();
		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/prevnext/caches/p/' ) );

	}

	/**
	 * 前の兄弟ページ、次の兄弟ページ をテスト
	 */
	public function testBrosPrevBrosNext(){
		// 最初と最後のページ
		$output = $this->utils->passthru( [ PHP_BINARY, __DIR__.'/testData/prevnext/.px_execute.php', '/?PX=api.get.bros_prev' ] );
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertFalse( $output );

		$output = $this->utils->passthru( [ PHP_BINARY, __DIR__.'/testData/prevnext/.px_execute.php', '/?PX=api.get.bros_next' ] );
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertFalse( $output );

		$output = $this->utils->passthru( [ PHP_BINARY, __DIR__.'/testData/prevnext/.px_execute.php', '/last.html?PX=api.get.bros_prev' ] );
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output, 'DynamicPath' );

		$output = $this->utils->passthru( [ PHP_BINARY, __DIR__.'/testData/prevnext/.px_execute.php', '/last.html?PX=api.get.bros_next' ] );
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertFalse( $output );

		// 標準的な兄弟ページ
		$output = $this->utils->passthru( [ PHP_BINARY, __DIR__.'/testData/prevnext/.px_execute.php', '/bros1/2.html?PX=api.get.bros_prev' ] );
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertFalse( $output );

		$output = $this->utils->passthru( [ PHP_BINARY, __DIR__.'/testData/prevnext/.px_execute.php', '/bros1/2.html?PX=api.get.bros_next' ] );
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output, 'Bros1-3' );

		$output = $this->utils->passthru( [ PHP_BINARY, __DIR__.'/testData/prevnext/.px_execute.php', '/bros1/4.html?PX=api.get.bros_prev' ] );
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output, 'Bros1-3' );

		$output = $this->utils->passthru( [ PHP_BINARY, __DIR__.'/testData/prevnext/.px_execute.php', '/bros1/4.html?PX=api.get.bros_next' ] );
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertFalse( $output );

		// list_flg がない兄弟ページ
		$output = $this->utils->passthru( [ PHP_BINARY, __DIR__.'/testData/prevnext/.px_execute.php', '/bros3/2.html?PX=api.get.bros_next' ] );
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output, 'Bros3-4' );

		$output = $this->utils->passthru( [ PHP_BINARY, __DIR__.'/testData/prevnext/.px_execute.php', '/bros3/4.html?PX=api.get.bros_prev' ] );
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output, 'Bros3-2' );

		$output = $this->utils->passthru( [ PHP_BINARY, __DIR__.'/testData/prevnext/.px_execute.php', '/bros3/2.html?PX=api.get.bros_next&filter=false' ] );
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output, 'Bros3-3' );

		$output = $this->utils->passthru( [ PHP_BINARY, __DIR__.'/testData/prevnext/.px_execute.php', '/bros3/4.html?PX=api.get.bros_prev&filter=false' ] );
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output, 'Bros3-3' );

		// スキップするテスト
		// popup と alias
		$output = $this->utils->passthru( [ PHP_BINARY, __DIR__.'/testData/prevnext/.px_execute.php', '/skips/normalpage.html?PX=api.get.bros_next' ] );
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output, 'NormalPage2' );

		$output = $this->utils->passthru( [ PHP_BINARY, __DIR__.'/testData/prevnext/.px_execute.php', '/skips/normalpage-2.html?PX=api.get.bros_next' ] );
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output, 'NormalPage3' );

		$output = $this->utils->passthru( [ PHP_BINARY, __DIR__.'/testData/prevnext/.px_execute.php', '/skips/normalpage-3.html?PX=api.get.bros_prev' ] );
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output, 'NormalPage2' );

		$output = $this->utils->passthru( [ PHP_BINARY, __DIR__.'/testData/prevnext/.px_execute.php', '/skips/normalpage-2.html?PX=api.get.bros_prev' ] );
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output, 'NormalPage1' );




		// 後始末
		$output = $this->utils->passthru( [
			PHP_BINARY, __DIR__.'/testData/prevnext/.px_execute.php', '/?PX=clearcache'
		] );

		clearstatcache();
		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/prevnext/caches/p/' ) );

	}

}
