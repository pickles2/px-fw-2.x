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
		print $this->px->pxcmd()->get_cli_header();

		var_dump( $conf );

		print $this->px->pxcmd()->get_cli_footer();
		exit;
	}

}
