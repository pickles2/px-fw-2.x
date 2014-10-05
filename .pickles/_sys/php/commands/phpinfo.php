<?php
/**
 * PX Commands "phpinfo"
 */
namespace pickles\commands;

/**
 * PX Commands "phpinfo"
 */
class phpinfo{

	private $px;

	/**
	 * Starting function
	 */
	public static function funcs_starting( $px ){
		$pxcmd = $px->get_px_command();
		if( $pxcmd[0] != 'phpinfo' ){
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
		phpinfo();
		exit;
	}
}
