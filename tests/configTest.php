<?php
/**
 * test for tomk79\PxFW-2.x
 */
class configTest extends PHPUnit\Framework\TestCase{
	private $fs;
	private $utils;

	public function setup() : void{
		mb_internal_encoding('UTF-8');
		$this->fs = new tomk79\filesystem();
		require_once(__DIR__.'/libs/utils.php');
		$this->utils = new \picklesFramework2\tests\utils();
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
		$output = $this->utils->passthru( ['php', __DIR__.'/testData/configtest/.px_execute.php', '/?PX=api.get.path_files&path_resource='.urlencode('/test.png')] );
		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		// var_dump($output);
		$this->assertEquals( $output, '/index.html.files.callback/test.png' );


		// 後始末
		$output = $this->utils->passthru( [
			'php', __DIR__.'/testData/configtest/.px_execute.php', '/?PX=clearcache'
		] );
		clearstatcache();
		$this->assertTrue( $this->utils->common_error( $output ) );
		$this->assertFalse( is_dir( __DIR__.'/testData/configtest/caches/p/' ) );
		$this->assertFalse( is_dir( __DIR__.'/testData/configtest/px-files/_sys/ram/caches/sitemaps/' ) );
	} // testConfPathCache();

}
