<?php
/**
 * processor "*.md"
 */
namespace picklesFramework2\processors\md;

/**
 * processor "*.md" class
 */
class ext{

	/**
	 * 変換処理の実行
	 * @param object $px Picklesオブジェクト
	 */
	public static function exec( $px ){

		foreach( $px->bowl()->get_keys() as $key ){
			$src = $px->bowl()->pull( $key );

			$src = \Michelf\MarkdownExtra::defaultTransform($src);

			$src = $px->bowl()->replace( $src, $key );
		}

		return true;
	}
}