<?php
/**
 * PX Commands "clearcache"
 */
namespace picklesFramework2\commands;

/**
 * PX Commands "clearcache"
 */
class clearcache{

	private $px;
	private $path_homedir, $path_docroot, $path_public_caches;

	/**
	 * Starting function
	 */
	public static function regist( $px ){
		$pxcmd = $px->get_px_command();
		if( $pxcmd[0] != 'clearcache' ){
			return;
		}
		(new self( $px ))->kick();
		exit;
	}

	/**
	 * constructor
	 */
	public function __construct( $px ){
		$this->px = $px;
		$this->path_homedir = $this->px->fs()->get_realpath( $this->px->get_path_homedir().'/' );
		$this->path_docroot = $this->px->fs()->get_realpath( $this->px->get_path_docroot().$this->px->get_path_controot().'/' );
		$this->path_public_caches = $this->px->fs()->get_realpath( $this->px->get_path_docroot().$this->px->get_path_controot().$this->px->conf()->public_cache_dir.'/' );
	}


	/**
	 * kick
	 */
	private function kick(){
		header('Content-type: text/plain;');
		print "\n";
		print "\n";
		print 'pickles '.$this->px->get_version().' - clearcache'."\n";
		print @date('Y-m-d H:i:s')."\n";
		print '------------------------'."\n";
		print 'pickles home directory: '.$this->path_homedir."\n";
		print 'pickles docroot directory: '.$this->path_docroot."\n";
		print 'pickles public cache directory: '.$this->path_public_caches."\n";
		print '------------------------'."\n";
		print "\n";
		$this->exec();
		print "\n";
		print '------------------------'."\n";
		print "\n";
		print 'end;'."\n";
		print @date('Y-m-d H:i:s')."\n";
		print "\n";
		exit;
	}

	/**
	 * execution: clearcache
	 * 外部から直接呼び出すときはこちら。
	 */
	public function exec(){
		print '-- cleaning "caches"'."\n";
		print $this->cleanup_dir( $this->path_homedir.'_sys/ram/caches/' ).' items done.'."\n";
		print "\n";
		print '-- cleaning "publish"'."\n";
		print $this->cleanup_dir( $this->path_homedir.'_sys/ram/publish/' ).' items done.'."\n";
		print "\n";
		print '-- cleaning "public caches"'."\n";
		print $this->cleanup_dir( $this->path_public_caches ).' items done.'."\n";
		print "\n";
		return true;
	}

	/**
	 * ディレクトリをクリーニング
	 */
	private function cleanup_dir( $path, $localpath = null ){
		$count = 0;
		$ls = $this->px->fs()->ls($path.$localpath);
		foreach( $ls as $basename ){
			if( $localpath.$basename == '.gitkeep' ){
				continue;
			}
			if( $this->px->fs()->is_dir($path.$localpath.$basename) ){
				$count += $this->cleanup_dir( $path, $localpath.$basename.DIRECTORY_SEPARATOR );
				$this->px->fs()->rmdir($path.$localpath.$basename);
				print 'rmdir '.$this->px->fs()->get_realpath( $path.$localpath.$basename )."\n";
				$count ++;
			}else{
				$this->px->fs()->rm($path.$localpath.$basename);
				print 'rm '.$this->px->fs()->get_realpath( $path.$localpath.$basename )."\n";
				$count ++;
			}
		}

		if( is_null($localpath) ){
			$this->px->fs()->save_file( $path.$localpath.'.gitkeep', '' );
		}
		return $count;
	}
}
