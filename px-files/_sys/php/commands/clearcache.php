<?php
/**
 * PX Commands "clearcache"
 */
namespace picklesFramework2\commands;

/**
 * PX Commands "clearcache"
 */
class clearcache{

	/**
	 * Picklesオブジェクト
	 */
	private $px;

	/**
	 * ディレクトリ設定
	 */
	private $path_homedir, $path_docroot, $path_public_caches;

	/**
	 * パブリッシュロックファイルのパス
	 */
	private $path_lockfile;

	/**
	 * Starting function
	 * @param object $px Picklesオブジェクト
	 */
	public static function register( $px ){
		$px->pxcmd()->register('clearcache', function($px){
			(new self( $px ))->kick();
			exit;
		}, true);
	}

	/**
	 * constructor
	 * @param object $px Picklesオブジェクト
	 */
	public function __construct( $px ){
		$this->px = $px;
		$this->path_homedir = $this->px->fs()->get_realpath( $this->px->get_path_homedir().'/' );
		$this->path_docroot = $this->px->fs()->get_realpath( $this->px->get_path_docroot().$this->px->get_path_controot().'/' );
		$this->path_public_caches = $this->px->fs()->get_realpath( $this->px->get_path_docroot().$this->px->get_path_controot().$this->px->conf()->public_cache_dir.'/' );

		$this->path_lockfile = $this->px->fs()->get_realpath( $this->px->get_path_homedir().'_sys/ram/publish/applock.txt' );
	}


	/**
	 * kick
	 */
	private function kick(){
		print $this->px->pxcmd()->get_cli_header();
		print 'pickles home directory: '.$this->path_homedir."\n";
		print 'pickles docroot directory: '.$this->path_docroot."\n";
		print 'pickles public cache directory: '.$this->path_public_caches."\n";
		print '------------------------'."\n";
		print "\n";
		if( $this->is_publish_locked() ){
			print 'publish is now locked.'."\n";
			print '  (lockfile updated: '.@date('Y-m-d H:i:s', filemtime($this->path_lockfile)).')'."\n";
			print 'Try again later...'."\n";
			print 'exit.'."\n";
			print $this->px->pxcmd()->get_cli_footer();
			exit;
		}
		$this->exec();
		print "\n";
		print $this->px->pxcmd()->get_cli_footer();
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
	 * @param string $path クリーニング対象のパス
	 * @param string $localpath $pathの仮想のパス (再帰処理のために使用)
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
				clearstatcache();
				if( $this->px->fs()->get_realpath($path.$localpath.$basename) == $this->path_lockfile ){
					// パブリッシュロックファイルは消さない
				}else{
					$this->px->fs()->rm($path.$localpath.$basename);
					print 'rm '.$this->px->fs()->get_realpath( $path.$localpath.$basename )."\n";
					$count ++;
				}
			}
		}

		if( is_null($localpath) ){
			$this->px->fs()->save_file( $path.$localpath.'.gitkeep', '' );
		}
		return $count;
	}

	/**
	 * パブリッシュがロックされているか確認する。
	 * 
	 * @return bool ロック中の場合に `true`、それ以外の場合に `false` を返します。
	 */
	private function is_publish_locked(){
		$lockfilepath = $this->path_lockfile;
		$lockfile_expire = 60*30;//有効期限は30分

		#	PHPのFileStatusCacheをクリア
		clearstatcache();

		if( $this->px->fs()->is_file($lockfilepath) ){
			if( ( time() - filemtime($lockfilepath) ) > $lockfile_expire ){
				#	有効期限を過ぎていたら、ロックは成立する。
				return false;
			}
			return true;
		}
		return false;
	}//is_publish_locked()

}