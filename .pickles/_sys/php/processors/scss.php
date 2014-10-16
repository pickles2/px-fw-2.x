<?php
/**
 * processor "*.scss"
 */
namespace pickles\processors\scss;

/**
 * processor "*.scss" class
 */
class ext{
	public static function exec( $px ){
		foreach( $px->bowl()->get_keys() as $key ){
			$src = $px->bowl()->pull( $key );

			$tmp_current_dir = realpath('./');
			chdir( dirname( $_SERVER['SCRIPT_FILENAME'] ) );
			$scss = new \scssc();
			$src = $scss->compile( $src );
			chdir( $tmp_current_dir );

			$src = $px->bowl()->replace( $src, $key );
		}

		return true;
	}
}
