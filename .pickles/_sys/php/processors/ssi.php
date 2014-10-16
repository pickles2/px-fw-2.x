<?php
/**
 * PX Commands "ssi"
 */
namespace pickles\processors\ssi;

/**
 * PX Commands "ssi"
 */
class ssi{

	private $px;

	/**
	 * Starting function
	 */
	public static function exec( $px ){
		$me = new self( $px );
		$keys = $px->get_content_keys();
		foreach( $keys as $key ){
			$src = $px->pull_content( $key );
			$src = $me->apply($src, $px->req()->get_request_file_path() );
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
	private function apply($src, $current_path){

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
				$href_incfile = trim($tmp_matched[2]);
				$realpath_incfile = null;
				$path_incfile = null;
				if( preg_match( '/^\//s', $href_incfile ) ){
					$realpath_incfile = $this->px->fs()->get_realpath( $this->px->get_path_docroot().$href_incfile );
					$path_incfile = $href_incfile;
				}else{
					$realpath_incfile = $this->px->fs()->get_realpath( $this->px->get_path_docroot().dirname($current_path).'/'.$href_incfile );
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
