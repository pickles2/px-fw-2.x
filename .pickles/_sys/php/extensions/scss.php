<?php
/**
 * extention "*.scss"
 */
namespace pickles\extensions;

/**
 * extention "*.scss" class
 */
class scss{
	public static function exec( $px, $src, $contents_key ){
		$tmp_current_dir = realpath('./');
		chdir( dirname( $_SERVER['DOCUMENT_ROOT'].$px->get_path_docroot().$px->req()->get_request_file_path() ) );
		$scss = new \scssc();
		$src = $scss->compile( $src );
		chdir( $tmp_current_dir );
		return $src;
	}
}
