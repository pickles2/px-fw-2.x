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
	private $path_tmp_publish, $path_publish_dir, $path_docroot;

	private $paths_queue = array();
	private $paths_done = array();

	/**
	 * Before content function
	 */
	public static function funcs_before_content( $px ){
		$pxcmd = $px->get_px_command();
		if( $pxcmd[0] != 'publish' ){
			return;
		}
		$self = new self( $px );
		if( $pxcmd[1] == 'run' ){
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
		$this->path_docroot = $px->fs()->get_realpath( $px->get_path_docroot() ).DIRECTORY_SEPARATOR;
	}

	/**
	 * execute home
	 */
	private function exec_home(){
		header('Content-type: text/plain;');
		print 'pickles '.$this->px->get_version().' - publish'."\n";
		print @date('Y-m-d H:i:s')."\n";
		print '------------'."\n";
		print 'execute PX command => "?PX=publish.run"'."\n";
		print '------------'."\n";
		print 'end;'."\n";
	}

	/**
	 * execute publish
	 */
	private function exec_publish(){
		header('Content-type: text/plain;');
		print 'pickles '.$this->px->get_version().' - publish'."\n";
		print @date('Y-m-d H:i:s')."\n";
		print '------------'."\n";
		print 'pickles publish directory(tmp): '.$this->path_tmp_publish."\n";
		print 'pickles publish directory: '.$this->path_publish_dir."\n";
		print 'pickles docroot directory: '.$this->path_docroot."\n";
		print '------------'."\n";
		$validate = $this->validate();
		if( !$validate['status'] ){
			print $validate['message']."\n";
			print '------------'."\n";
			print 'end;'."\n";
			exit;
		}
		print '-- making list by Sitemap'."\n";
		$this->make_list_by_sitemap();
		print "\n";
		print '-- making list by Directory Scan'."\n";
		$this->make_list_by_dir_scan();
		print "\n";
		print '------------'."\n";
		print count( $this->paths_queue ).'件'."\n";
		print '------------'."\n";
		print "\n";
		print 'end;'."\n";
		print "\n";
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
			$this->add_queue( $href );
		}
		return true;
	}

	/**
	 * make list by directory scan
	 */
	private function make_list_by_dir_scan( $path = null ){
		$ls = $this->px->fs()->ls( $this->path_docroot.$path );
		foreach( $ls as $basename ){
			if( $this->px->fs()->is_dir( $this->path_docroot.$path.$basename ) ){
				$this->make_list_by_dir_scan( $path.$basename.DIRECTORY_SEPARATOR );
			}else{
				$extension = strtolower( pathinfo( $basename , PATHINFO_EXTENSION ) );
				$this->add_queue( '/'.$path.$basename );
			}
		}
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
