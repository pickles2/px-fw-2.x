<?php
/**
 * test for tomk79\PxFW-2.x
 */
class configTest extends PHPUnit\Framework\TestCase{
	private $fs;

	public function setup() : void{
		mb_internal_encoding('UTF-8');
		$this->fs = new tomk79\filesystem();
	}


	/**
	 * $conf->path_cache を変更して値の変化を確認するテスト
	 */
	public function testConfPathCache(){

		$conf_path = __DIR__.'/testData/configtest/px-files/config.php';

		// -------------------
		// api.get.path_files
		clearstatcache();
		$this->fs->copy( __DIR__.'/testData/configtest/px-files/config_sample/default.php', $conf_path );
		clearstatcache();
		$output = $this->passthru( ['php', __DIR__.'/testData/configtest/.px_execute.php', '/?PX=api.get.path_files&path_resource='.urlencode('/test.png')] );
		$this->assertTrue( $this->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( $output, '/index.html.files.callback/test.png' );


		// 後始末
		$output = $this->passthru( [
			'php', __DIR__.'/testData/configtest/.px_execute.php', '/?PX=clearcache'
		] );
		clearstatcache();
		$this->assertTrue( $this->common_error( $output ) );
		$this->assertFalse( is_dir( __DIR__.'/testData/configtest/caches/p/' ) );
		$this->assertFalse( is_dir( __DIR__.'/testData/configtest/px-files/_sys/ram/caches/sitemaps/' ) );
	}//testConfPathCache();







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
