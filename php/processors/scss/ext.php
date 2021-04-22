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
	 */
	public static function exec( $px ){
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
			$src = $scss->compile( $src );
			chdir( $tmp_current_dir );

			$px->bowl()->replace( $src, $key );
		}

		return true;
	}
}
