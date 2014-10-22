<?php
/**
 * PX Commands "config"
 */
namespace picklesFramework2\commands;

/**
 * PX Commands "config"
 */
class config{

	private $px;

	/**
	 * Starting function
	 */
	public static function regist( $px ){
		$pxcmd = $px->get_px_command();
		if( $pxcmd[0] != 'config' ){
			return;
		}
		(new self( $px ))->kick();
		exit;
	}

	/**
	 * constructor
	 */
	public function __construct( $px ){
		$this->px = $px;
	}


	/**
	 * kick
	 */
	private function kick(){
		$conf = clone $this->px->conf();
		$this->header();

		var_dump( $conf );

		$this->footer();
		exit;
	}

	/**
	 * ヘッダーを出力
	 */
	private function header(){
		@header('Content-type: text/plain');

		print 'pickles '.$this->px->get_version().''."\n";
		print 'PHP '.phpversion()."\n";
		print @date('Y-m-d H:i:s')."\n";
		print '------------'."\n";

	}

	/**
	 * フッターを出力
	 */
	private function footer(){
		echo ''."\n";
	}


}
