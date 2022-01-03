<?php
/**
 * PX Commands "encodingconverter"
 */
namespace picklesFramework2\processors\encodingconverter;

/**
 * PX Commands "encodingconverter"
 */
class encodingconverter{

	/**
	 * Picklesオブジェクト
	 */
	private $px;

	/**
	 * 出力エンコード
	 */
	private $output_encoding;

	/**
	 * 出力改行コード
	 */
	private $output_eol_coding;

	/**
	 * 変換対象の拡張子
	 */
	private $ext;

	/**
	 * Starting function
	 * @param object $px Picklesオブジェクト
	 * @param object $options オプション
	 * <dl>
	 * <dt>$options->output_encoding</dt>
	 * 	<dd>出力エンコーディング。<code>mb_convert_encoding()</code> が取り扱えるエンコード名で指定します。</dd>
	 * <dt>$options->output_eol_coding</dt>
	 * 	<dd>出力改行コード。文字列で、<code>crlf</code>、<code>cr</code>、または <code>lf</code> のいずれかで指定します。</dd>
	 * <dt>$options->ext (v2.0.40 で追加)</dt>
	 * 	<dd>対象とする拡張子。文字列 または 配列で複数指定できます。</dd>
	 * </dl>
	 * 
	 * 省略時は、`$px->conf()` から、同名の値を参照します。
	 * 
	 * どちらにも設定がない場合、encodingconverter は変換を実行しません。
	 */
	public static function exec( $px = null, $options = null ){

		if( count(func_get_args()) <= 1 ){
			return __CLASS__.'::'.__FUNCTION__.'('.( is_array($px) ? json_encode($px) : '' ).')';
		}

		$me = new self( $px, $options );
		$px->bowl()->each( array($me, 'apply') );
	}

	/**
	 * constructor
	 * @param object $px Picklesオブジェクト
	 * @param object $options オプション
	 * 
	 * `exec()` が受け取った値が転送されます。
	 */
	public function __construct( $px, $options = null ){
		$this->px = $px;

		if( is_object($options) ){
			if( property_exists($options, 'output_encoding') && strlen(''.$options->output_encoding) ){
				$this->output_encoding = $options->output_encoding;
			}
			if( property_exists($options, 'output_eol_coding') && strlen(''.$options->output_eol_coding) ){
				$this->output_eol_coding = $options->output_eol_coding;
			}
			if( property_exists($options, 'ext') && $options->ext ){
				if( is_string($options->ext) ){
					$this->ext = array($options->ext);
				}elseif( is_array($options->ext) ){
					$this->ext = $options->ext;
				}
			}
		}

		// オプションなしの場合、$px->conf() を参照
		$conf = $this->px->conf();
		if( !strlen(''.$this->output_encoding) && property_exists($conf, 'output_encoding') ){
			$this->output_encoding = $conf->output_encoding;
		}
		if( !strlen(''.$this->output_eol_coding) && property_exists($conf, 'output_eol_coding') ){
			$this->output_eol_coding = $conf->output_eol_coding;
		}

		// それでも設定なしならデフォルト値
		if( !strlen(''.$this->output_encoding) ){
			$this->output_encoding = 'UTF-8';
		}
		if( !strlen(''.$this->output_eol_coding) ){
			$this->output_eol_coding = 'crlf';
		}

	}

	/**
	 * apply output filter
	 * @param string $src HTML, CSS, JavaScriptなどの出力コード
	 * @return string 加工後の出力コード
	 */
	public function apply($src){

		if( is_array( $this->ext ) ){
			// 対象の拡張子か調べる (pickles2/px-fw-2.x v2.0.40 で追加されたオプション)
			$ext = $this->px->get_path_proc_type( $this->px->req()->get_request_file_path() );
			$ext = strtolower($ext);
			$is_target = false;
			foreach($this->ext as $target_ext){
				if($ext == strtolower($target_ext)){
					$is_target = true;
					break;
				}
			}
			if(!$is_target){
				return $src;
			}
		}

		if( strlen( ''.$this->output_encoding ) ){
			$output_encoding = $this->output_encoding;
			if( !strlen(''.$output_encoding) ){ $output_encoding = 'UTF-8'; }

			//出力ソースの文字コード変換(HTML)
			$src = preg_replace('/\@charset\s+"[a-zA-Z0-9\_\-\.]+"\;/si','@charset "'.htmlspecialchars(''.$output_encoding).'";',$src);
			$src = preg_replace('/\@charset\s+\'[a-zA-Z0-9\_\-\.]+\'\;/si','@charset \''.htmlspecialchars(''.$output_encoding).'\';',$src);
			$src = preg_replace('/(<meta\s+charset\=")[a-zA-Z0-9\_\-\.]+("\s*\/?'.'>)/si','$1'.htmlspecialchars(''.$output_encoding).'$2',$src);
			$src = preg_replace('/(<meta\s+http\-equiv\="Content-Type"\s+content\="[a-zA-Z0-9\_\-\+]+\/[a-zA-Z0-9\_\-\+]+\;\s*charset\=)[a-zA-Z0-9\_\-\.]+("\s*\/?'.'>)/si','$1'.htmlspecialchars(''.$output_encoding).'$2',$src);
			$src = $this->px->convert_encoding( $src, $output_encoding );
		}

		if( strlen( ''.$this->output_eol_coding ) ){
			//出力ソースの改行コード変換
			$eol_code = "\r\n";
			switch( strtolower( $this->output_eol_coding ) ){
				case 'cr':     $eol_code = "\r"; break;
				case 'lf':     $eol_code = "\n"; break;
				case 'crlf':
				default:       $eol_code = "\r\n"; break;
			}
			$src = preg_replace('/\r\n|\r|\n/si', $eol_code, $src);
		}

		return $src;
	}

}
