<?php
/**
 * PX Commands "clearcache"
 */
namespace pickles\commands;

/**
 * PX Commands "clearcache"
 */
class clearcache{

	private $px;
	private $path_homedir, $path_docroot;

	/**
	 * Starting function
	 */
	public static function funcs_starting( $px ){
		$pxcmd = $px->get_px_command();
		if( $pxcmd[0] != 'clearcache' ){
			return;
		}
		new self( $px );
		exit;
	}

	/**
	 * constructor
	 */
	public function __construct( $px ){
		$this->px = $px;
		header('Content-type: text/plain;');
		$this->path_homedir = $px->fs()->get_realpath( $px->get_path_homedir() ).DIRECTORY_SEPARATOR;
		$this->path_docroot = $px->fs()->get_realpath( $px->get_path_docroot() ).DIRECTORY_SEPARATOR;

		print "\n";
		print "\n";
		print 'pickles '.$px->get_version().' - clearcache'."\n";
		print @date('Y-m-d H:i:s')."\n";
		print '------------------------'."\n";
		print 'pickles home directory: '.$this->path_homedir."\n";
		print 'pickles docroot directory: '.$this->path_docroot."\n";
		print '------------------------'."\n";
		print "\n";
		print '-- cleaning "caches"'."\n";
		print $this->cleanup_dir( $this->path_homedir.'_sys/ram/caches/' ).' items done.'."\n";
		print "\n";
		print '-- cleaning "publish"'."\n";
		print $this->cleanup_dir( $this->path_homedir.'_sys/ram/publish/' ).' items done.'."\n";
		print "\n";
		print '------------------------'."\n";
		print "\n";
		print 'end;'."\n";
		print @date('Y-m-d H:i:s')."\n";
		print "\n";
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
