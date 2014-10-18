<?php
/**
 * PX Commands "publish"
 */
namespace pickles\commands;

/**
 * PX Commands "publish"
 */
class publish{

	private $px, $site;
	private $path_tmp_publish, $path_publish_dir, $domain, $path_docroot;
	private $path_region, $param_path_region;

	private $paths_queue = array();
	private $paths_done = array();

	/**
	 * Before content function
	 */
	public static function regist( $px ){
		$pxcmd = $px->get_px_command();
		if( @$pxcmd[0] != 'publish' ){
			return;
		}
		$self = new self( $px );
		if( @$pxcmd[1] == 'run' ){
			$self->exec_publish();
		}else{
			$self->exec_home();
		}
		exit;
	}

	/**
	 * constructor
	 */
	public function __construct( $px ){
		$this->px = $px;

		$this->path_tmp_publish = $px->fs()->get_realpath( $px->get_path_homedir().'_sys/ram/publish/' ).DIRECTORY_SEPARATOR;
		if( $this->get_path_publish_dir() !== false ){
			$this->path_publish_dir = $this->get_path_publish_dir();
		}
		$this->domain = $px->get_domain();
		$this->path_docroot = $px->get_path_controot();

		$this->path_region = $this->px->req()->get_request_file_path();
		$this->path_region = preg_replace('/^\/+/s','/',$this->path_region);
		$this->path_region = preg_replace('/\/'.$this->px->get_directory_index_preg_pattern().'$/s','/',$this->path_region);
		$func_check_param_path = function($path){
			if( !preg_match('/^\//', $path) ){
				return false;
			}
			$path = preg_replace('/(?:\/|\\\\)/', '/', $path);
			if( preg_match('/(?:^|\/)\.{1,2}(?:$|\/)/', $path) ){
				return false;
			}
			return true;
		};
		$param_path_region = $this->px->req()->get_param('path_region');
		if( strlen( $param_path_region ) && $param_path_region != $this->path_region && $func_check_param_path( $param_path_region ) ){
			$this->path_region = $param_path_region;
			$this->param_path_region = $param_path_region;
		}
	}

	/**
	 * print CLI header
	 */
	private function cli_header(){
		ob_start();
		print 'pickles '.$this->px->get_version().' - publish'."\n";
		print 'PHP '.phpversion()."\n";
		print @date('Y-m-d H:i:s')."\n";
		print '------------'."\n";
		print 'publish directory(tmp): '.$this->path_tmp_publish."\n";
		print 'publish directory: '.$this->path_publish_dir."\n";
		print 'domain: '.$this->domain."\n";
		print 'docroot directory: '.$this->path_docroot."\n";
		print 'region: '.$this->path_region."\n";
		print '------------'."\n";
		flush();
		return ob_get_clean();
	}

	/**
	 * print CLI footer
	 */
	private function cli_footer(){
		ob_start();
		print '------------'."\n";
		print "\n";
		print @date('Y-m-d H:i:s')."\n";
		print 'end;'."\n";
		print "\n";
		return ob_get_clean();
	}

	/**
	 * report
	 */
	private function cli_report(){
		$cnt_queue = count( $this->paths_queue );
		$cnt_done = count( $this->paths_done );
		ob_start();
		print $cnt_done.'/'.($cnt_queue+$cnt_done)."\n";
		print 'queue: '.$cnt_queue.' / done: '.$cnt_done."\n";
		return ob_get_clean();
	}

	/**
	 * execute home
	 */
	private function exec_home(){
		header('Content-type: text/plain;');
		print $this->cli_header();
		print 'execute PX command => "?PX=publish.run"'."\n";
		print $this->cli_footer();
		exit;
	}

	/**
	 * execute publish
	 */
	private function exec_publish(){
		header('Content-type: text/plain;');
		print $this->cli_header();

		$this->clearcache();
		print '------------'."\n";
		$validate = $this->validate();
		if( !$validate['status'] ){
			print $validate['message']."\n";
			print $this->cli_footer();
			exit;
		}
		print "\n";
		print '-- making list by Sitemap'."\n";
		$this->make_list_by_sitemap();
		print "\n";
		print '-- making list by Directory Scan'."\n";
		$this->make_list_by_dir_scan();
		print "\n";
		print '------------'."\n";
		print $this->cli_report();
		print '------------'."\n";
		while(1){
			flush();
			foreach( $this->paths_queue as $path=>$val ){break;}
			print '------------'."\n";
			print $path."\n";

			$ext = strtolower( pathinfo( $path , PATHINFO_EXTENSION ) );
			$proc_type = $this->px->get_path_proc_type( $path );
			switch( $proc_type ){
				case 'direct':
					// direct
					print $ext.' -> direct'."\n";
					$this->px->fs()->mkdir_r( dirname( $this->path_tmp_publish.'/htdocs'.$path ) );
					$this->px->fs()->copy( dirname($_SERVER['SCRIPT_FILENAME']).$path , $this->path_tmp_publish.'/htdocs'.$path );
					break;

				default:
					// pickles execute
					print $ext.' -> '.$proc_type."\n";
					$bin = $this->passthru( array(
						$this->px->conf()->commands->php ,
						$_SERVER['SCRIPT_FILENAME'] ,
						'-o' , 'json' ,// output as JSON
						$path ,
					) );
					$bin = json_decode($bin);

					if( $bin->status >= 500 ){
					}elseif( $bin->status >= 400 ){
					}elseif( $bin->status >= 300 ){
					}elseif( $bin->status >= 200 ){
						$this->px->fs()->mkdir_r( dirname( $this->path_tmp_publish.'/htdocs'.$path ) );
						$this->px->fs()->save_file( $this->path_tmp_publish.'/htdocs'.$path, base64_decode( @$bin->body_base64 ) );
						foreach( $bin->relatedlinks as $link ){
							$link = $this->px->fs()->get_realpath( $link, dirname($path) );
							if( $this->px->fs()->is_dir( $this->px->get_path_docroot().'/'.$link ) ){
								$this->make_list_by_dir_scan( $link.'/' );
							}else{
								$this->add_queue( $link );
							}
						}
					}elseif( $bin->status >= 100 ){
					}

					break;
			}

			if( !empty( $this->path_publish_dir ) ){
				// パブリッシュ先ディレクトリに都度コピー
				if( $this->px->fs()->is_file( $this->path_publish_dir.$path ) ){
					$this->px->fs()->mkdir_r( dirname( $this->path_publish_dir.$path ) );
					$this->px->fs()->copy(
						$this->path_tmp_publish.'/htdocs'.$path ,
						$this->path_publish_dir.$path
					);
					print ' -> copied to publish dir'."\n";
				}
			}

			unset($this->paths_queue[$path]);
			$this->paths_done[$path] = true;
			print $this->cli_report();

			if( !count( $this->paths_queue ) ){
				break;
			}
		}

		print "\n";
		print 'done.'."\n";
		print "\n";

		if( !empty( $this->path_publish_dir ) ){
			// パブリッシュ先ディレクトリを同期
			print "\n";
			print '-- syncing to publish dir...'."\n";
			$this->px->fs()->sync_dir(
				$this->path_tmp_publish.'/htdocs'.$this->path_region ,
				$this->path_publish_dir.$this->path_region
			);
		}
		print "\n";

		print $this->cli_footer();
		exit;
	}

	/**
	 * コマンドを実行し、標準出力値を返す
	 * @param array $ary_command コマンドのパラメータを要素として持つ配列
	 * @return string コマンドの標準出力値
	 */
	private function passthru( $ary_command ){
		$cmd = array();
		foreach( $ary_command as $row ){
			$param = '"'.addslashes($row).'"';
			array_push( $cmd, $param );
		}
		$cmd = implode( ' ', $cmd );
		ob_start();
		passthru( $cmd );
		$bin = ob_get_clean();
		return $bin;
	}

	/**
	 * validate
	 */
	private function validate(){
		$rtn = array('status'=>true, 'message'=>'');
		return $rtn;
	}

	/**
	 * clearcache
	 */
	private function clearcache(){
		(new \pickles\commands\clearcache( $this->px ))->exec();
	}

	/**
	 * make list by sitemap
	 */
	private function make_list_by_sitemap(){
		$sitemap = $this->px->site()->get_sitemap();
		foreach( $sitemap as $page_info ){
			$href = $this->px->href( $page_info['path'] );
			$href = preg_replace( '/\/$/s', '/'.$this->px->get_directory_index_primary(), $href );
			$href = preg_replace( '/^'.preg_quote($this->path_docroot, '/').'/s', '/', $href );
			$this->add_queue( $href );
		}
		return true;
	}

	/**
	 * make list by directory scan
	 */
	private function make_list_by_dir_scan( $path = null ){
		$process = array_keys( get_object_vars( $this->px->conf()->funcs->processor ) );
		foreach( $process as $key=>$val ){
			$process[$key] = preg_quote($val);
		}
		$preg_exts = '('.implode( '|', $process ).')';

		$ls = $this->px->fs()->ls( './'.$path );
		if( $this->px->is_ignore_path( './'.$path ) ){
			return true;
		}
		foreach( $ls as $basename ){
			if( $this->px->fs()->is_dir( './'.$path.$basename ) ){
				$this->make_list_by_dir_scan( $path.$basename.DIRECTORY_SEPARATOR );
			}else{
				$tmp_localpath = $this->px->fs()->get_realpath('/'.$path.$basename);
				$tmp_localpath = preg_replace( '/\.'.$preg_exts.'$/is', '', $tmp_localpath );
				if( $this->px->get_path_proc_type( $tmp_localpath ) == 'ignore' || $this->px->get_path_proc_type( $tmp_localpath ) == 'direct' ){
					$tmp_localpath = $this->px->fs()->get_realpath('/'.$path.$basename);
				}
				$this->add_queue( $tmp_localpath );
			}
		}
		return true;
	}

	/**
	 * add queue
	 */
	private function add_queue( $path ){
		$path = $this->px->fs()->normalize_path( $this->px->fs()->get_realpath( $path, $this->path_docroot ) );

		if( $this->px->is_ignore_path( $path ) ){
			// 対象外
			return false;
		}
		if( !$this->is_region_path( $path ) ){
			// 範囲外
			return false;
		}
		if( array_key_exists($path, $this->paths_queue) ){
			// 登録済み
			return false;
		}
		if( array_key_exists($path, $this->paths_done) ){
			// 処理済み
			return false;
		}
		$this->paths_queue[$path] = true;
		print 'added queue - "'.$path.'"'."\n";
		return true;
	}

	/**
	 * パブリッシュ範囲内か調べる
	 */
	private function is_region_path( $path ){
		$path = $this->px->fs()->get_realpath( '/'.$path );
		if( $this->px->fs()->is_dir('./'.$path) ){
			$path .= '/';
		}
		if( preg_match( '/^'.preg_quote( $this->path_region, '/' ).'/s' , $path ) ){
			return true;
		}
		return false;
	}// is_region_path()


	/**
	 * パブリッシュ先ディレクトリを取得
	 */
	private function get_path_publish_dir(){
		if( @!strlen( $this->px->conf()->path_publish_dir ) ){
			return false;
		}
		$tmp_path = $this->px->fs()->get_realpath( $this->px->conf()->path_publish_dir ).DIRECTORY_SEPARATOR;
		if( !$this->px->fs()->is_dir( $tmp_path ) ){
			return false;
		}
		if( !$this->px->fs()->is_writable( $tmp_path ) ){
			return false;
		}
		return $tmp_path;
	}// get_path_publish_dir()

}
