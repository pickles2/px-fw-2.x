<?php
/**
 * PX Commands "autoindex"
 */
namespace tomk79\pickles2\autoindex;

/**
 * PX Commands "autoindex"
 */
class autoindex{

	private $px;

	/**
	 * Starting function
	 */
	public static function funcs_starting( $px ){
		$autoindex = new self( $px );
		$px->autoindex = $autoindex;
	}

	/**
	 * constructor
	 */
	public function __construct( $px ){
		$this->px = $px;
	}

}
