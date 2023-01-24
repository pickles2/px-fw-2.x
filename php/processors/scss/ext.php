<?php
/**
 * processor "*.scss"
 */
namespace picklesFramework2\processors\scss;

/**
 * processor "*.scss" class
 */
class ext{

	/**
	 * 変換処理の実行
	 * @param object $px Picklesオブジェクト
	 * @param object $options オプション
	 */
	public static function exec( $px = null, $options = null ){

		if( count(func_get_args()) <= 1 ){
			return __CLASS__.'::'.__FUNCTION__.'('.( is_array($px) ? json_encode($px) : '' ).')';
		}

		foreach( $px->bowl()->get_keys() as $key ){
			$src = $px->bowl()->pull( $key );

			$tmp_current_dir = realpath('./');
			chdir( dirname( $_SERVER['SCRIPT_FILENAME'] ) );
			$scss = null;
			if (class_exists('\ScssPhp\ScssPhp\Compiler')) {
				$scss = new \ScssPhp\ScssPhp\Compiler();
			} elseif (class_exists('\Leafo\ScssPhp\Compiler')) {
				$scss = new \Leafo\ScssPhp\Compiler();
			}else{
				trigger_error('SCSS Proccessor is NOT available.');
				continue;
			}

			if( is_callable( array( $scss, 'compileString' ) ) ){
				$src = $scss->compileString( $src )->getCss();
			}else{
				$src = $scss->compile( $src ); // 古い $scss への配慮
			}

			chdir( $tmp_current_dir );

			$px->bowl()->replace( $src, $key );
		}

		return true;
	}
}
