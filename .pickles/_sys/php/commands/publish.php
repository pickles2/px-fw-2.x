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

	private $paths_queue = array();
	private $paths_done = array();

	/**
	 * Before content function
	 */
	public static function funcs_before_content( $px ){
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
			$this->path_publish_dir = $px->fs()->get_realpath( $px->conf()->path_publish_dir ).DIRECTORY_SEPARATOR;
		}
		$this->domain = $px->get_domain();
		$this->path_docroot = $px->get_path_docroot();
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
		return ob_get_clean();
	}

	/**
	 * print CLI footer
	 */
	private function cli_footer(){
		ob_start();
		print '------------'."\n";
		print "\n";
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
		print 'publish directory(tmp): '.$this->path_tmp_publish."\n";
		print 'publish directory: '.$this->path_publish_dir."\n";
		print 'domain: '.$this->domain."\n";
		print 'docroot directory: '.$this->path_docroot."\n";
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
			foreach( $this->paths_queue as $path=>$val ){break;}
			print '------------'."\n";
			print $path."\n";

			$ext = strtolower( pathinfo( $path , PATHINFO_EXTENSION ) );
			$proc_type = $this->px->get_path_proc_type( $path );
			switch( $proc_type ){
				case 'process':
					// pickles execute
					print $ext.' -> '.$proc_type."\n";
					ob_start();
					passthru( implode( ' ', array(
						$this->px->conf()->commands->php ,
						$_SERVER['SCRIPT_FILENAME'] ,
						'-o' , 'json' ,// output as JSON
						$path ,
					) ) );
					$bin = ob_get_clean();
					$bin = json_decode($bin);
					$this->px->fs()->mkdir_r( dirname( $this->path_tmp_publish.$path ) );
					$this->px->fs()->save_file( $this->path_tmp_publish.$path, base64_decode( @$bin->body_base64 ) );
					break;
				case 'copy':
				default:
					// copy
					print $ext.' -> copy'."\n";
					$this->px->fs()->mkdir_r( dirname( $this->path_tmp_publish.$path ) );
					$this->px->fs()->copy( dirname($_SERVER['SCRIPT_FILENAME']).$path , $this->path_tmp_publish.$path );
					break;
			}

			unset($this->paths_queue[$path]);
			$this->paths_done[$path] = true;
			print $this->cli_report();

			if( !count( $this->paths_queue ) ){
				break;
			}
		}


		print $this->cli_footer();
		exit;
	}

	/**
	 * validate
	 */
	private function validate(){
		$rtn = array('status'=>true, 'message'=>'');
		return $rtn;
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
		$extensions = array_keys( get_object_vars( $this->px->conf()->funcs->extensions ) );
		foreach( $extensions as $key=>$val ){
			$extensions[$key] = preg_quote($val);
		}
		$preg_exts = '('.implode( '|', $extensions ).')';

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
				if( !$this->px->get_path_proc_type( $tmp_localpath ) == 'process' ){
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
	 * パブリッシュ先ディレクトリを取得
	 */
	private function get_path_publish_dir(){
		if( @!strlen( $this->px->conf()->path_publish_dir ) ){
			return false;
		}
		return $this->px->fs()->get_realpath( $this->px->conf()->path_publish_dir );
	}

}
