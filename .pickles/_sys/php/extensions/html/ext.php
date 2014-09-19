<?php
/**
 * extention "*.html"
 */
namespace pickles\extensions\html;

/**
 * extention "*.html" class
 */
class ext{
	public static function exec( $px, $src, $contents_key ){
		$src .= '<p>processed by HTML extention.</p>';
		return $src;
	}
}
