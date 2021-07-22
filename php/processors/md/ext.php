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
	 * @param object $options オプション
	 */
	public static function exec( $px = null, $options = null ){

		if( count(func_get_args()) <= 1 ){
			return __CLASS__.'::'.__FUNCTION__.'('.( is_array($px) ? json_encode($px) : '' ).')';
		}

		foreach( $px->bowl()->get_keys() as $key ){
			$src = $px->bowl()->pull( $key );

			if($key != 'head' && $key != 'foot'){
				$src = \Michelf\MarkdownExtra::defaultTransform($src);
			}

			$px->bowl()->replace( $src, $key );
		}

		return true;
	}
}
