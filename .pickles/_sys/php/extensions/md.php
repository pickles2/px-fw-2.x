<?php
/**
 * extention "*.md"
 */
namespace pickles\extensions;

/**
 * extention "*.md" class
 */
class md{
	public static function exec( $px, $src, $contents_key ){
		$src = \Michelf\MarkdownExtra::defaultTransform($src);
		return $src;
	}
}
