<?php
/**
 * test for tomk79\PxFW-2.x
 */
class prevnextTest extends PHPUnit\Framework\TestCase{
	private $fs;

	public function setup() : void{
		mb_internal_encoding('UTF-8');
		$this->fs = new tomk79\filesystem();

		// 前処理
		$output = $this->passthru( [
			'php', __DIR__.'/testData/prevnext/.px_execute.php', '/?PX=clearcache'
		] );
	}


	/**
	 * 前のページ、次のページ をテスト
	 */
	public function testPrevNext(){
		// 最初と最後のページ
		$output = $this->passthru( [ 'php', __DIR__.'/testData/prevnext/.px_execute.php', '/?PX=api.get.prev' ] );
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		$this->assertFalse( $output );

		$output = $this->passthru( [ 'php', __DIR__.'/testData/prevnext/.px_execute.php', '/?PX=api.get.next' ] );
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output, 'Bros1' );

		$output = $this->passthru( [ 'php', __DIR__.'/testData/prevnext/.px_execute.php', '/last.html?PX=api.get.next' ] );
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		$this->assertFalse( $output );

		// 階層が深いページのテスト
		$output = $this->passthru( [ 'php', __DIR__.'/testData/prevnext/.px_execute.php', '/bros2/2/?PX=api.get.next' ] );
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output, 'Bros-2-2-3' );

		$output = $this->passthru( [ 'php', __DIR__.'/testData/prevnext/.px_execute.php', '/bros2/nextbros.html?PX=api.get.prev' ] );
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output, 'Bros-2-2-3-4-deep' );

		// list_flg がないページ
		$output = $this->passthru( [ 'php', __DIR__.'/testData/prevnext/.px_execute.php', '/bros3/2.html?PX=api.get.next' ] );
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output, 'Bros3-4' );

		$output = $this->passthru( [ 'php', __DIR__.'/testData/prevnext/.px_execute.php', '/bros3/4.html?PX=api.get.prev' ] );
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output, 'Bros3-2' );

		$output = $this->passthru( [ 'php', __DIR__.'/testData/prevnext/.px_execute.php', '/bros3/2.html?PX=api.get.next&filter=false' ] );
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output, 'Bros3-3' );

		$output = $this->passthru( [ 'php', __DIR__.'/testData/prevnext/.px_execute.php', '/bros3/4.html?PX=api.get.prev&filter=false' ] );
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output, 'Bros3-3' );

		// スキップするテスト
		// popup と alias
		$output = $this->passthru( [ 'php', __DIR__.'/testData/prevnext/.px_execute.php', '/skips/normalpage.html?PX=api.get.next' ] );
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output, 'NormalPage2' );

		$output = $this->passthru( [ 'php', __DIR__.'/testData/prevnext/.px_execute.php', '/skips/normalpage-2.html?PX=api.get.next' ] );
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output, 'NormalPage3' );

		$output = $this->passthru( [ 'php', __DIR__.'/testData/prevnext/.px_execute.php', '/skips/normalpage-3.html?PX=api.get.prev' ] );
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output, 'NormalPage2' );

		$output = $this->passthru( [ 'php', __DIR__.'/testData/prevnext/.px_execute.php', '/skips/normalpage-2.html?PX=api.get.prev' ] );
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output, 'NormalPage1' );




		// 後始末
		$output = $this->passthru( [
			'php', __DIR__.'/testData/prevnext/.px_execute.php', '/?PX=clearcache'
		] );

		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/prevnext/caches/p/' ) );

	} // testPrevNext()

	/**
	 * 前の兄弟ページ、次の兄弟ページ をテスト
	 */
	public function testBrosPrevBrosNext(){
		// 最初と最後のページ
		$output = $this->passthru( [ 'php', __DIR__.'/testData/prevnext/.px_execute.php', '/?PX=api.get.bros_prev' ] );
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		$this->assertFalse( $output );

		$output = $this->passthru( [ 'php', __DIR__.'/testData/prevnext/.px_execute.php', '/?PX=api.get.bros_next' ] );
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		$this->assertFalse( $output );

		$output = $this->passthru( [ 'php', __DIR__.'/testData/prevnext/.px_execute.php', '/last.html?PX=api.get.bros_prev' ] );
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output, 'DynamicPath' );

		$output = $this->passthru( [ 'php', __DIR__.'/testData/prevnext/.px_execute.php', '/last.html?PX=api.get.bros_next' ] );
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		$this->assertFalse( $output );

		// 標準的な兄弟ページ
		$output = $this->passthru( [ 'php', __DIR__.'/testData/prevnext/.px_execute.php', '/bros1/2.html?PX=api.get.bros_prev' ] );
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		$this->assertFalse( $output );

		$output = $this->passthru( [ 'php', __DIR__.'/testData/prevnext/.px_execute.php', '/bros1/2.html?PX=api.get.bros_next' ] );
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output, 'Bros1-3' );

		$output = $this->passthru( [ 'php', __DIR__.'/testData/prevnext/.px_execute.php', '/bros1/4.html?PX=api.get.bros_prev' ] );
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output, 'Bros1-3' );

		$output = $this->passthru( [ 'php', __DIR__.'/testData/prevnext/.px_execute.php', '/bros1/4.html?PX=api.get.bros_next' ] );
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		$this->assertFalse( $output );

		// list_flg がない兄弟ページ
		$output = $this->passthru( [ 'php', __DIR__.'/testData/prevnext/.px_execute.php', '/bros3/2.html?PX=api.get.bros_next' ] );
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output, 'Bros3-4' );

		$output = $this->passthru( [ 'php', __DIR__.'/testData/prevnext/.px_execute.php', '/bros3/4.html?PX=api.get.bros_prev' ] );
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output, 'Bros3-2' );

		$output = $this->passthru( [ 'php', __DIR__.'/testData/prevnext/.px_execute.php', '/bros3/2.html?PX=api.get.bros_next&filter=false' ] );
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output, 'Bros3-3' );

		$output = $this->passthru( [ 'php', __DIR__.'/testData/prevnext/.px_execute.php', '/bros3/4.html?PX=api.get.bros_prev&filter=false' ] );
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output, 'Bros3-3' );

		// スキップするテスト
		// popup と alias
		$output = $this->passthru( [ 'php', __DIR__.'/testData/prevnext/.px_execute.php', '/skips/normalpage.html?PX=api.get.bros_next' ] );
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output, 'NormalPage2' );

		$output = $this->passthru( [ 'php', __DIR__.'/testData/prevnext/.px_execute.php', '/skips/normalpage-2.html?PX=api.get.bros_next' ] );
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output, 'NormalPage3' );

		$output = $this->passthru( [ 'php', __DIR__.'/testData/prevnext/.px_execute.php', '/skips/normalpage-3.html?PX=api.get.bros_prev' ] );
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output, 'NormalPage2' );

		$output = $this->passthru( [ 'php', __DIR__.'/testData/prevnext/.px_execute.php', '/skips/normalpage-2.html?PX=api.get.bros_prev' ] );
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output, 'NormalPage1' );




		// 後始末
		$output = $this->passthru( [
			'php', __DIR__.'/testData/prevnext/.px_execute.php', '/?PX=clearcache'
		] );

		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/prevnext/caches/p/' ) );

	} // testBrosPrevBrosNext()




	/**
	 * PHPがエラー吐いてないか確認しておく。
	 */
	private function common_error( $output ){
		if( preg_match('/'.preg_quote('Parse error:', '/').'/si', $output) ){ return false; }
		if( preg_match('/'.preg_quote('Fatal error:', '/').'/si', $output) ){ return false; }
		if( preg_match('/'.preg_quote('Warning:', '/').'/si', $output) ){ return false; }
		if( preg_match('/'.preg_quote('Notice:', '/').'/si', $output) ){ return false; }
		return true;
	}


	/**
	 * コマンドを実行し、標準出力値を返す
	 * @param array $ary_command コマンドのパラメータを要素として持つ配列
	 * @return string コマンドの標準出力値
	 */
	private function passthru( $ary_command ){
		set_time_limit(60*10);
		$cmd = array();
		foreach( $ary_command as $row ){
			$param = escapeshellcmd($row);
			array_push( $cmd, $param );
		}
		$cmd = implode( ' ', $cmd );
		ob_start();
		passthru( $cmd );
		$bin = ob_get_clean();
		set_time_limit(30);
		return $bin;
	}

}
