<?php
/**
 * extention "*.html"
 */
namespace pickles\extensions\html;

/**
 * extention "*.html" class
 */
class ext{
	public static function exec( $px, $src_content, $path_content ){
		$src_content .= '<p>processed by HTML extention.</p>';
		return $src_content;
	}
}
