<?php
/**
 * PX Commands "phpinfo"
 */
namespace picklesFramework2\commands;

/**
 * PX Commands "phpinfo"
 *
 * <dl>
 * 	<dt>PX=phpinfo</dt>
 * 		<dd>`phpinfo()` の実行結果を表示します。</dd>
 * </dl>
 */
class phpinfo{

	/**
	 * Picklesオブジェクト
	 */
	private $px;

	/**
	 * Starting function
	 * @param object $px Picklesオブジェクト
	 * @param object $options プラグイン設定
	 */
	public static function register( $px = null, $options = null ){

		if( count(func_get_args()) <= 1 ){
			return __CLASS__.'::'.__FUNCTION__.'('.( is_array($px) ? json_encode($px) : '' ).')';
		}

		$px->pxcmd()->register('phpinfo', function($px){
			(new self( $px ))->kick();
			exit;
		});
	}

	/**
	 * constructor
	 * @param object $px Picklesオブジェクト
	 */
	public function __construct( $px ){
		$this->px = $px;
	}


	/**
	 * kick
	 * @return void
	 */
	private function kick(){
		phpinfo();
		exit;
	}
}
