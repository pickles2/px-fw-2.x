<?php
/**
 * PX Commands "encodingconverter"
 */
namespace tomk79\pickles2\outputfilter;

/**
 * PX Commands "encodingconverter"
 */
class encodingconverter{

	private $px;

	/**
	 * Starting function
	 */
	public static function output_filter( $px ){
		new self( $px );
	}

	/**
	 * constructor
	 */
	public function __construct( $px ){
		$this->px = $px;
		$src = $px->get_response_body();
		$src = $this->apply($src);
		$px->set_response_body( $src );
	}

	/**
	 * apply output filter
	 */
	private function apply($src){

		if( @strlen( $this->px->conf()->output_encoding ) ){
			$output_encoding = $this->px->conf()->output_encoding;
			if( !strlen($output_encoding) ){ $output_encoding = 'UTF-8'; }

			$extension = strtolower( pathinfo( $this->px->get_path_content() , PATHINFO_EXTENSION ) );
			switch( strtolower( $extension ) ){
				case 'css':
					//出力ソースの文字コード変換
					$src = preg_replace('/\@charset\s+"[a-zA-Z0-9\_\-\.]+"\;/si','@charset "'.htmlspecialchars($output_encoding).'";',$src);
					$src = $this->px->convert_encoding( $src, $output_encoding );
					break;
				case 'js':
					//出力ソースの文字コード変換
					$src = $this->px->convert_encoding( $src, $output_encoding );
					break;
				case 'html':
				case 'htm':
					//出力ソースの文字コード変換(HTML)
					$src = preg_replace('/(<meta\s+charset\=")[a-zA-Z0-9\_\-\.]+("\s*\/?'.'>)/si','$1'.htmlspecialchars($output_encoding).'$2',$src);
					$src = preg_replace('/(<meta\s+http\-equiv\="Content-Type"\s+content\="[a-zA-Z0-9\_\-\+]+\/[a-zA-Z0-9\_\-\+]+\;\s*charset\=)[a-zA-Z0-9\_\-\.]+("\s*\/?'.'>)/si','$1'.htmlspecialchars($output_encoding).'$2',$src);
					$src = $this->px->convert_encoding( $src, $output_encoding );
					break;
				default:
					$src = $this->px->convert_encoding( $src, $output_encoding );
					break;
			}

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
