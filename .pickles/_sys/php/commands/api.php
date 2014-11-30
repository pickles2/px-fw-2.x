<?php
/**
 * PX Commands "api"
 */
namespace picklesFramework2\commands;

/**
 * PX Commands "api"
 */
class api{

	private $px;
	private $path_homedir, $path_docroot, $path_public_caches;

	/**
	 * Starting function
	 */
	public static function register( $px ){
		$px->pxcmd()->register('api', function($px){
			(new self( $px ))->kick();
			exit;
		}, true);
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
		print $this->px->pxcmd()->get_cli_header();
		print "\n";
		print 'now under development.'."\n";
		print "\n";
		print $this->px->pxcmd()->get_cli_footer();
		exit;
	}

}
