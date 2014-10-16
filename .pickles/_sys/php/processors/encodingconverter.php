<?php
/**
 * PX Commands "encodingconverter"
 */
namespace pickles\processors\encodingconverter;

/**
 * PX Commands "encodingconverter"
 */
class encodingconverter{

	private $px;

	/**
	 * Starting function
	 */
	public static function exec( $px ){
		$me = new self( $px );
		$keys = $px->get_content_keys();
		foreach( $keys as $key ){
			$src = $px->pull_content( $key );
			$src = $me->apply($src);
			$px->replace_content( $src, $key );
		}
	}

	/**
	 * constructor
	 */
	public function __construct( $px ){
		$this->px = $px;
	}

	/**
	 * apply output filter
	 */
	private function apply($src){

		if( @strlen( $this->px->conf()->output_encoding ) ){
			$output_encoding = $this->px->conf()->output_encoding;
			if( !strlen($output_encoding) ){ $output_encoding = 'UTF-8'; }

			//出力ソースの文字コード変換(HTML)
			$src = preg_replace('/\@charset\s+"[a-zA-Z0-9\_\-\.]+"\;/si','@charset "'.htmlspecialchars($output_encoding).'";',$src);
			$src = preg_replace('/\@charset\s+\'[a-zA-Z0-9\_\-\.]+\'\;/si','@charset \''.htmlspecialchars($output_encoding).'\';',$src);
			$src = preg_replace('/(<meta\s+charset\=")[a-zA-Z0-9\_\-\.]+("\s*\/?'.'>)/si','$1'.htmlspecialchars($output_encoding).'$2',$src);
			$src = preg_replace('/(<meta\s+http\-equiv\="Content-Type"\s+content\="[a-zA-Z0-9\_\-\+]+\/[a-zA-Z0-9\_\-\+]+\;\s*charset\=)[a-zA-Z0-9\_\-\.]+("\s*\/?'.'>)/si','$1'.htmlspecialchars($output_encoding).'$2',$src);
			$src = $this->px->convert_encoding( $src, $output_encoding );

		}

		if( @strlen( $this->px->conf()->output_eol_coding ) ){
			//出力ソースの改行コード変換
			$eol_code = "\r\n";
			switch( strtolower( $this->px->conf()->output_eol_coding ) ){
				case 'cr':     $eol_code = "\r"; break;
				case 'lf':     $eol_code = "\n"; break;
				case 'crlf':
				default:       $eol_code = "\r\n"; break;
			}
			$src = preg_replace('/\r\n|\r|\n/si',$eol_code,$src);
		}

		return $src;
	}

}
