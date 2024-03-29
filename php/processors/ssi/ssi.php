<?php
/**
 * PX Commands "ssi"
 */
namespace picklesFramework2\processors\ssi;

/**
 * PX Commands "ssi"
 */
class ssi{

	/**
	 * Picklesオブジェクト
	 */
	private $px;

	/**
	 * Starting function
	 * @param object $px Picklesオブジェクト
	 * @param object $options オプション
	 */
	public static function exec( $px = null, $options = null ){

		if( count(func_get_args()) <= 1 ){
			return __CLASS__.'::'.__FUNCTION__.'('.( is_array($px) ? json_encode($px) : '' ).')';
		}

		$me = new self( $px );
		$px->bowl()->each( array($me, 'apply') );
	}

	/**
	 * constructor
	 * @param object $px Picklesオブジェクト
	 */
	public function __construct( $px ){
		$this->px = $px;
	}

	/**
	 * apply output filter
	 * @param string $src HTML, CSS, JavaScriptなどの出力コード
	 * @param string $current_path コンテンツのカレントディレクトリパス
	 * @return string 加工後の出力コード
	 */
	public function apply($src, $current_path = null){
		if( is_null($current_path) ){
			$current_path = $this->px->req()->get_request_file_path();
		}

		if( !$this->px->is_publish_tool() ){

			// SSI命令の解決
			$tmp_src = $src;
			$src = '';
			while(1){
				$tmp_preg_pattern = '/^(.*?)'.preg_quote('<!--#include','/').'\s+virtual\=\"(.*?)\"\s*'.preg_quote('-->','/').'(.*)$/s';
				if( !preg_match($tmp_preg_pattern, $tmp_src, $tmp_matched) ){
					$src .= $tmp_src;
					break;
				}
				$src .= $tmp_matched[1];

				// SSI先のファイルを読み込む
				$href_incfile = $this->px->fs()->normalize_path( trim($tmp_matched[2]) );
				$realpath_incfile = null;
				$path_incfile = null;
				if( preg_match( '/^\//s', $href_incfile ) ){
					$realpath_incfile = $this->px->fs()->get_realpath( $this->px->get_realpath_docroot().$href_incfile );
					$path_incfile = $href_incfile;
				}else{
					$realpath_incfile = $this->px->fs()->get_realpath( $this->px->get_realpath_docroot().dirname($current_path).'/'.$href_incfile );
					$path_incfile = $this->px->fs()->get_realpath( $href_incfile, dirname($current_path) );
				}
				$ssi_content = $this->px->fs()->read_file( $realpath_incfile );
				$ssi_content = $this->apply( $ssi_content, $path_incfile );
				$src .= $ssi_content;

				$tmp_src = $tmp_matched[3];
				continue;
			}

		}

		return $src;
	}

}
