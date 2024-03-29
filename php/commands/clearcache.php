<?php
/**
 * PX Commands "clearcache"
 */
namespace picklesFramework2\commands;

/**
 * PX Commands "clearcache"
 *
 * <dl>
 * 	<dt>PX=clearcache</dt>
 * 		<dd>Pickles Framework 2 が生成したキャッシュファイルを削除します。</dd>
 * 	<dt>PX=clearcache.version</dt>
 * 		<dd>Pickles Framework のバージョン番号を JSON 形式の文字列で返します。</dd>
 * </dl>
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
	 * @param object $options プラグイン設定
	 */
	public static function register( $px = null, $options = null ){

		if( count(func_get_args()) <= 1 ){
			return __CLASS__.'::'.__FUNCTION__.'('.( is_array($px) ? json_encode($px) : '' ).')';
		}

		$px->pxcmd()->register('clearcache', function($px){
			(new self( $px ))->kick();
			exit;
		});
	}

	/**
	 * constructor
	 * @param object $px Picklesオブジェクト
	 */
	public function __construct( $px ){
		$this->px = $px;
		$this->path_homedir = $this->px->fs()->get_realpath( $this->px->get_realpath_homedir().'/' );
		$this->path_docroot = $this->px->fs()->get_realpath( $this->px->get_realpath_docroot().$this->px->get_path_controot().'/' );
		$this->path_public_caches = $this->px->fs()->get_realpath( $this->px->get_realpath_docroot().$this->px->get_path_controot().($this->px->conf()->public_cache_dir ?? "").'/' );

		$this->path_lockfile = $this->px->fs()->get_realpath( $this->px->get_realpath_homedir().'_sys/ram/publish/applock.txt' );
	}


	/**
	 * kick
	 */
	private function kick(){
		$pxcmd = $this->px->get_px_command();

		if( ($pxcmd[1] ?? null) == 'version' ){
			// 命令が clearcache.version の場合、バージョン番号を返す。
			$val = $this->px->get_version();
			@header('Content-type: application/json; charset=UTF-8');
			print json_encode($val);
			exit;
		}
		if( strlen($pxcmd[1] ?? "") ){
			// 命令が不明の場合、エラーを表示する。
			if( $this->px->req()->is_cmd() ){
				header('Content-type: text/plain;');
				print $this->px->pxcmd()->get_cli_header();
				print 'execute PX command => "?PX=clearcache"'."\n";
				print $this->px->pxcmd()->get_cli_footer();
			}else{
				$html = '<p>Go to <a href="?PX=clearcache">PX=clearcache</a> to clear caches.</p>'."\n";
				print $this->px->pxcmd()->wrap_gui_frame($html);
			}
			exit;
		}

		print $this->px->pxcmd()->get_cli_header();
		print 'pickles home directory: '.$this->path_homedir."\n";
		print 'pickles docroot directory: '.$this->path_docroot."\n";
		print 'pickles public cache directory: '.$this->path_public_caches."\n";
		print '------------------------'."\n";
		print "\n";
		if( $this->is_publish_locked() ){
			print 'publish is now locked.'."\n";
			print '  (lockfile updated: '.@date('c', filemtime($this->path_lockfile)).')'."\n";
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
		if( $this->px->site() ){
			$this->px->site()->__destruct(); // sitemap.sqlite を開放 (Windowsで、排他ロックされるためパブリッシュでエラーが起きる問題を回避するため)
		}
		print $this->cleanup_dir( $this->path_homedir.'_sys/ram/caches/' ).' items done.'."\n";
		print "\n";
		print '-- cleaning "publish"'."\n";
		print $this->cleanup_dir( $this->path_homedir.'_sys/ram/publish/' ).' items done.'."\n";
		print "\n";
		if( strlen( $this->px->conf()->public_cache_dir ?? "" ) ){
			print '-- cleaning "public caches"'."\n";
			print $this->cleanup_dir( $this->path_public_caches ).' items done.'."\n";
			print "\n";
		}
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

				$i = 0;
				print 'rmdir '.$this->px->fs()->get_realpath( $path.$localpath.$basename );
				while(1){
					$i ++;
					if( $this->px->fs()->rmdir($path.$localpath.$basename) ){
						break;
					}
					if($i > 5){
						print ' [FAILED]';
						break;
					}
					sleep(1);
				}
				print "\n";
				$count ++;

			}else{
				clearstatcache();
				if( $this->px->fs()->get_realpath($path.$localpath.$basename) == $this->path_lockfile ){
					// パブリッシュロックファイルは消さない
				}else{
					$i = 0;
					print 'rm '.$this->px->fs()->get_realpath( $path.$localpath.$basename );
					while(1){
						$i ++;
						if( $this->px->fs()->rm($path.$localpath.$basename) ){
							// NOTE: Windows で、 `sitemap.sqlite` が使用中のままとなり、削除に失敗する場合がある。
							// このとき `PHP Warning: Resource temporarily unavailable` が発生し、ユニットテストが失敗する。
							// 実害はほぼないので、ここ↑では `@` をつけて隠すことにした。
							break;
						}
						if($i > 5){
							print ' [FAILED]';
							break;
						}
						sleep(1);
					}
					print "\n";
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
