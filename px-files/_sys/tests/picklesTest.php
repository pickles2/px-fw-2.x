<?php
/**
 * test for tomk79\PxFW-2.x
 */
class picklesTest extends PHPUnit_Framework_TestCase{
	private $fs;

	public function setup(){
		mb_internal_encoding('UTF-8');
		$this->fs = new tomk79\filesystem();
	}


	/**
	 * 普通に実行してみるテスト
	 */
	public function testStandard(){
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/' ,
		] );
		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertEquals( 1, preg_match('/'.preg_quote('<h2>テストページ</h2><p>このページは /index.html です。</p>', '/').'/s', $output) );
		$this->assertEquals( 0, preg_match('/'.preg_quote('<span id="hash_', '/').'/s', $output) );

		// 後始末
		$output = $this->passthru( [
			'php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=clearcache'
		] );

		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );

	}

	/**
	 * アプリケーションロックのテスト
	 */
	public function testAppLock(){
		// ロックする
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/applock/lock.html' ,
		] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/applock/' ) );
		$this->assertTrue( is_file( __DIR__.'/testData/standard/px-files/_sys/ram/applock/testAppLock.lock.txt' ) );
		$this->assertEquals( 1, preg_match('/'.preg_quote('testAppLock [SUCCESS]', '/').'/s', $output) );

		// ロックされてて動かない
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/applock/lock.html' ,
		] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/applock/' ) );
		$this->assertTrue( is_file( __DIR__.'/testData/standard/px-files/_sys/ram/applock/testAppLock.lock.txt' ) );
		$this->assertEquals( 1, preg_match('/'.preg_quote('testAppLock [FAILED]', '/').'/s', $output) );

		// ロックを解除
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/applock/unlock.html' ,
		] );
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/applock/' ) );
		$this->assertFalse( is_file( __DIR__.'/testData/standard/px-files/_sys/ram/applock/testAppLock.lock.txt' ) );

		// 後始末
		$output = $this->passthru( [
			'php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=clearcache'
		] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/' ) );
	}

	/**
	 * $px->site()->set_page_info() を実行してみるテスト
	 */
	public function testStandardSetPageInfo(){
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/site/set_page_info.html' ,
		] );
		clearstatcache();

		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertEquals( 'naked', $output );


		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/site/no_page.html' ,
		] );
		clearstatcache();

		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertEquals( 'no_page.html', $output );

		// 後始末
		$output = $this->passthru( [
			'php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=clearcache'
		] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/' ) );
	}



	/**
	 * 遠いディレクトリからコマンドラインで実行してみるテスト
	 */
	public function testStandardCmdExecByFarDirectory(){
		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/contents/get_path_docroot.html' ,
		] );
		clearstatcache();

		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertEquals( '<pre>'.$this->fs->normalize_path( __DIR__.'/testData/standard/' ).'</pre>', $output );

		$cd = realpath('.');
		chdir(__DIR__.'/');
		$output = $this->passthru( [
			'php',
			'./testData/standard/.px_execute.php' ,
			'/contents/get_path_docroot.html' ,
		] );
		clearstatcache();
		chdir($cd);

		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertEquals( '<pre>'.$this->fs->normalize_path( __DIR__.'/testData/standard/' ).'</pre>', $output );

		$cd = realpath('.');
		chdir(__DIR__.'/testData/standard/');
		$output = $this->passthru( [
			'php',
			'./.px_execute.php' ,
			'/contents/get_path_docroot.html' ,
		] );
		clearstatcache();
		chdir($cd);

		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertEquals( '<pre>'.$this->fs->normalize_path( __DIR__.'/testData/standard/' ).'</pre>', $output );


		// 後始末
		$output = $this->passthru( [
			'php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=clearcache'
		] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/' ) );
	}


	/**
	 * コンフィグ項目をコマンドラインオプションで上書きするテスト
	 */
	public function testOverrideConfig(){

		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=api.get.config' ,
		] );
		clearstatcache();
		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output->name, 'Pickles 2' );
		$this->assertEquals( $output->path_controot, '/' );
		$this->assertEquals( $output->commands->php, 'php' );
		$this->assertNull( @$output->commands->dummy );

		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'-c', json_encode(array(
				'name'=>'overRide sitename',
				'commands'=>array(
					'php'=>'/path/to/command/php',
					'dummy'=>'/path/to/command/dummy',
				),
			)) ,
			'/?PX=api.get.config' ,
		] );
		clearstatcache();
		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output->name, 'overRide sitename' );
		$this->assertEquals( $output->path_controot, '/' );
		$this->assertEquals( $output->commands->php, '/path/to/command/php' );
		$this->assertEquals( $output->commands->dummy, '/path/to/command/dummy' );


		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'-c', json_encode(array(
				'name'=>'overRide sitename',
				'commands'=>array(),//← commonds->* ではなく、commands自体が置き換えられる。
			)) ,
			'/?PX=api.get.config' ,
		] );
		clearstatcache();
		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( $output->name, 'overRide sitename' );
		$this->assertEquals( $output->path_controot, '/' );
		$this->assertNull( @$output->commands->php );
		$this->assertNull( @$output->commands->dummy );


		// 後始末
		$output = $this->passthru( [
			'php', __DIR__.'/testData/standard/.px_execute.php', '/?PX=clearcache'
		] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/caches/p/' ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/standard/px-files/_sys/ram/caches/sitemaps/' ) );
	}


	/**
	 * 文字コード・改行コード変換テスト
	 */
	public function testEncodingConverter(){
		$detect_order = 'UTF-8,eucJP-win,SJIS-win,EUC-JP,SJIS';

		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/encodingconverter/.px_execute.php' ,
			'/' ,
		] );
		clearstatcache();

		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertEquals( preg_match( '/'.preg_quote('<meta charset="Shift_JIS" />', '/').'/s', $output ), 1 );
		$this->assertEquals( preg_match( '/\r\n/s', $output ), 1 );
		// var_dump(mb_detect_encoding($output, $detect_order));
		$this->assertEquals( @strtolower('SJIS-win'), @strtolower(mb_detect_encoding($output, $detect_order)) );


		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/encodingconverter/.px_execute.php' ,
			'/common/test.css' ,
		] );
		clearstatcache();

		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertEquals( preg_match( '/'.preg_quote('@charset "EUC-JP"', '/').'/s', $output ), 1 );
		$this->assertEquals( preg_match( '/\r\n/s', $output ), 0 );
		$this->assertEquals( preg_match( '/\n/s', $output ), 1 );
		$this->assertEquals( preg_match( '/\r/s', $output ), 0 );
		// var_dump(mb_detect_encoding($output, $detect_order));
		$this->assertEquals( @strtolower('eucJP-win'), @strtolower(mb_detect_encoding($output, $detect_order)) );


		$output = $this->passthru( [
			'php',
			__DIR__.'/testData/encodingconverter/.px_execute.php' ,
			'/common/test.js' ,
		] );
		clearstatcache();

		// var_dump($output);
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertEquals( preg_match( '/\r\n/s', $output ), 0 );
		$this->assertEquals( preg_match( '/\n/s', $output ), 0 );
		$this->assertEquals( preg_match( '/\r/s', $output ), 1 );
		// var_dump(mb_detect_encoding($output, $detect_order));
		$this->assertEquals( @strtolower('utf-8'), @strtolower(mb_detect_encoding($output, $detect_order)) );


		// 後始末
		// $this->assertTrue( false );
		$output = $this->passthru( [
			'php', __DIR__.'/testData/encodingconverter/.px_execute.php', '/?PX=clearcache'
		] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertTrue( !is_dir( __DIR__.'/testData/encodingconverter/caches/p/' ) );

	}





	/**
	 * PHPがエラー吐いてないか確認しておく。
	 */
	private function common_error( $output ){
		if( preg_match('/'.preg_quote('Fatal', '/').'/si', $output) ){ return false; }
		if( preg_match('/'.preg_quote('Warning', '/').'/si', $output) ){ return false; }
		if( preg_match('/'.preg_quote('Notice', '/').'/si', $output) ){ return false; }
		return true;
	}


	/**
	 * コマンドを実行し、標準出力値を返す
	 * @param array $ary_command コマンドのパラメータを要素として持つ配列
	 * @return string コマンドの標準出力値
	 */
	private function passthru( $ary_command ){
		$cmd = array();
		foreach( $ary_command as $row ){
			$param = '"'.addslashes($row).'"';
			array_push( $cmd, $param );
		}
		$cmd = implode( ' ', $cmd );
		ob_start();
		passthru( $cmd );
		$bin = ob_get_clean();
		return $bin;
	}

}
