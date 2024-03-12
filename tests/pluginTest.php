<?php
/**
 * test for tomk79\PxFW-2.x
 */
class pluginTest extends PHPUnit\Framework\TestCase{
	private $fs;
	private $utils;

	public function setup() : void{
		mb_internal_encoding('UTF-8');
		$this->fs = new tomk79\filesystem();
		require_once(__DIR__.'/libs/utils.php');
		$this->utils = new \picklesFramework2\tests\utils();
	}


	/**
	 * API疎通確認
	 */
	public function testBefore(){

		$output = $this->utils->passthru( [
			'php',
			__DIR__.'/testData/standard/.px_execute.php' ,
			'/?PX=test-px2plugin' ,
		] );
		clearstatcache();

		$this->assertTrue( $this->utils->common_error( $output ) );
		$output = json_decode($output);
		$this->assertEquals( 1, preg_match('/p\/bs_4\/?$/s', $output->realpath_plugin_files) );

	}
}
