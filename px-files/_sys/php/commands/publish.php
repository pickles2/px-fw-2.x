<?php
/**
 * PX Commands "publish"
 */
namespace picklesFramework2\commands;

/**
 * PX Commands "publish"
 */
class publish{

	/**
	 * Picklesオブジェクト
	 */
	private $px;

	/**
	 * サイトオブジェクト
	 */
	private $site;

	/**
	 * パス設定
	 */
	private $path_tmp_publish, $path_publish_dir, $path_docroot;

	/**
	 * ドメイン設定
	 */
	private $domain;

	/**
	 * パブリッシュ範囲設定
	 */
	private $path_region, $param_path_region;

	/**
	 * ロックファイルの格納パス
	 */
	private $path_lockfile;

	/**
	 * 処理待ちのパス一覧
	 */
	private $paths_queue = array();

	/**
	 * 処理済みのパス一覧
	 */
	private $paths_done = array();

	/**
	 * Before content function
	 * @param object $px Picklesオブジェクト
	 */
	public static function register( $px ){
		$px->pxcmd()->register('publish', function($px){
			$pxcmd = $px->get_px_command();
			$self = new self( $px );
			if( @$pxcmd[1] == 'run' ){
				$self->exec_publish();
			}else{
				$self->exec_home();
			}
			exit;
		});
	}

	/**
	 * constructor
	 * @param object $px Picklesオブジェクト
	 */
	public function __construct( $px ){
		$this->px = $px;

		$this->path_tmp_publish = $px->fs()->get_realpath( $px->get_path_homedir().'_sys/ram/publish/' );
		$this->path_lockfile = $this->path_tmp_publish.'applock.txt';
		if( $this->get_path_publish_dir() !== false ){
			$this->path_publish_dir = $this->get_path_publish_dir();
		}
		$this->domain = $px->conf()->domain;
		$this->path_docroot = $px->conf()->path_controot;

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
		print $this->px->pxcmd()->get_cli_header();
		print 'publish directory(tmp): '.$this->path_tmp_publish."\n";
		print 'lockfile: '.$this->path_lockfile."\n";
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
		print $this->px->pxcmd()->get_cli_footer();
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
		if( $this->px->req()->is_cmd() ){
			header('Content-type: text/plain;');
			print $this->cli_header();
			print 'execute PX command => "?PX=publish.run"'."\n";
			print $this->cli_footer();
		}else{
			$html = '';

			if(!is_dir($this->path_tmp_publish)){
				$html .= '<div class="unit">'."\n";
				$html .= '	<p class="error">パブリッシュ先一時ディレクトリが存在しません。</p>'."\n";
				$html .= '	<ul><li style="word-break:break-all;">'.htmlspecialchars( $this->path_tmp_publish ).'</li></ul>'."\n";
				$html .= '</div><!-- /.unit -->'."\n";
			}elseif(!is_writable($this->path_tmp_publish)){
				$html .= '<div class="unit">'."\n";
				$html .= '	<p class="error">パブリッシュ先一時ディレクトリに書き込み許可がありません。</p>'."\n";
				$html .= '	<ul><li style="word-break:break-all;">'.htmlspecialchars( $this->path_tmp_publish ).'</li></ul>'."\n";
				$html .= '</div><!-- /.unit -->'."\n";
			}elseif( strlen($this->path_publish_dir) && !is_dir($this->path_publish_dir) ){
				$html .= '<div class="unit">'."\n";
				$html .= '	<p class="error">パブリッシュ先ディレクトリが存在しません。</p>'."\n";
				$html .= '	<ul><li style="word-break:break-all;">'.htmlspecialchars( $this->px->dbh()->get_realpath( $this->path_publish_dir ).'/' ).'</li></ul>'."\n";
				$html .= '</div><!-- /.unit -->'."\n";
			}elseif( strlen($this->path_publish_dir) && !is_writable($this->path_publish_dir) ){
				$html .= '<div class="unit">'."\n";
				$html .= '	<p class="error">パブリッシュ先ディレクトリに書き込み許可がありません。</p>'."\n";
				$html .= '	<ul><li style="word-break:break-all;">'.htmlspecialchars( $this->px->dbh()->get_realpath( $this->path_publish_dir ).'/' ).'</li></ul>'."\n";
				$html .= '</div><!-- /.unit -->'."\n";
			}elseif( $this->is_locked() ){
				$html .= '<div class="unit">'."\n";
				$html .= '	<p>パブリッシュは<strong>ロックされています</strong>。</p>'."\n";
				$html .= '	<p>'."\n";
				$html .= '		現在、他のプロセスでパブリッシュが実行中である可能性があります。<br />'."\n";
				$html .= '		しばらく待ってから、リロードしてください。<br />'."\n";
				$html .= '	</p>'."\n";
				$html .= '	<p>'."\n";
				$html .= '		ロックファイルの内容を下記に示します。<br />'."\n";
				$html .= '	</p>'."\n";
				$html .= '	<blockquote><pre>'.htmlspecialchars( $this->px->fs()->read_file( $this->path_lockfile ) ).'</pre></blockquote>'."\n";
				$html .= '	<p>'."\n";
				$html .= '		ロックファイルは下記の時刻に更新されました。<br />'."\n";
				$html .= '	</p>'."\n";
				$html .= '	<blockquote><pre>'.htmlspecialchars( date( 'Y-m-d H:i:s', filemtime( $this->path_lockfile ) ) ).'</pre></blockquote>'."\n";
				$html .= '	<p>'."\n";
				$html .= '		ロックファイルは、次のパスに存在します。<br />'."\n";
				$html .= '	</p>'."\n";
				$html .= '	<blockquote><pre>'.htmlspecialchars( realpath( $this->path_lockfile ) ).'</pre></blockquote>'."\n";
				$html .= '</div><!-- /.unit -->'."\n";
			}else{
				ob_start(); ?>
<script type="text/javascript">
function cont_EditPublishTargetPath(){
	$('.cont_publish_target_path_preview').hide();
	$('.cont_publish_target_path_editor').show();
}
function cont_EditPublishTargetPathApply(formElm){
	var path = $('input[name=path]', formElm).val();
	window.location.href = <?= json_encode($this->px->get_path_controot()) ?> + path + '?PX=publish&path_region='+encodeURIComponent(path);
}
</script>
<div class="unit">
	<p>プロジェクト『<?= htmlspecialchars($this->px->conf()->name) ?>』をパブリッシュします。</p>
	<table class="def" style="width:100%;">
		<colgroup><col width="30%" /><col width="70%" /></colgroup>
		<tr>
			<th style="word-break:break-all;">publish directory(tmp)</th>
			<td style="word-break:break-all;"><?= htmlspecialchars($this->path_tmp_publish) ?></td>
		</tr>
		<tr>
			<th style="word-break:break-all;">publish directory</th>
			<td style="word-break:break-all;"><?= htmlspecialchars($this->path_publish_dir) ?></td>
		</tr>
		<tr>
			<th style="word-break:break-all;">domain</th>
			<td style="word-break:break-all;"><?= htmlspecialchars($this->domain) ?></td>
		</tr>
		<tr>
			<th style="word-break:break-all;">docroot directory</th>
			<td style="word-break:break-all;"><?= htmlspecialchars($this->path_docroot) ?></td>
		</tr>
		<tr>
			<th style="word-break:break-all;">region</th>
			<td style="word-break:break-all;">
				<div class="cont_publish_target_path_preview">
					<div style="word-break:break-all;"><?= htmlspecialchars($this->path_region) ?></div>
					<div class="small"><a href="javascript:cont_EditPublishTargetPath();" class="icon">変更する</a></div>
				</div>
				<div class="cont_publish_target_path_editor" style="display:none;">
					<form action="?" method="get" onsubmit="cont_EditPublishTargetPathApply(this); return false;" class="inline">
						<input type="text" name="path" size="25" style="max-width:70%;" value="<?= htmlspecialchars($this->path_region) ?>" />
						<input type="submit" style="width:20%;" value="変更する" />
					</form>
				</div>
			</td>
		</tr>
	</table>
</div>
<div class="unit">
	<p>次のボタンをクリックしてパブリッシュを実行してください。</p>
	<form action="?" method="get" target="_blank">
	<p class="center"><button class="xlarge">パブリッシュを実行する</button></p>
	<div><input type="hidden" name="PX" value="publish.run" /><input type="hidden" name="path_region" value="<?= htmlspecialchars($this->px->req()->get_param('path_region')) ?>" /></div>
	</form>
</div>
<?php
				$html .= ob_get_clean();
			}

			print $this->px->pxcmd()->wrap_gui_frame($html);
		}
		exit;
	}

	/**
	 * execute publish
	 */
	private function exec_publish(){
		header('Content-type: text/plain;');
		print $this->cli_header();

		$validate = $this->validate();
		if( !$validate['status'] ){
			print $validate['message']."\n";
			print $this->cli_footer();
			exit;
		}
		flush();
		if( !$this->lock() ){//ロック
			print '------'."\n";
			print 'publish is now locked.'."\n";
			print '  (lockfile updated: '.@date('Y-m-d H:i:s', filemtime($this->path_lockfile)).')'."\n";
			print 'Try again later...'."\n";
			print 'exit.'."\n";
			print $this->cli_footer();
			exit;
		}

		$this->clearcache();
		print '------------'."\n";

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

		$this->log(array(
			'* datetime' ,
			'* path' ,
			'* proc_type' ,
			'* status code'
		));

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
					$this->px->fs()->mkdir_r( dirname( $this->path_tmp_publish.'/htdocs'.$this->path_docroot.$path ) );
					$this->px->fs()->copy( dirname($_SERVER['SCRIPT_FILENAME']).$path , $this->path_tmp_publish.'/htdocs'.$this->path_docroot.$path );
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
					if( !is_object($bin) ){
						$this->alert_log(array( @date('Y-m-d H:i:s'), $path, 'unknown server error' ));
					}elseif( $bin->status >= 500 ){
						$this->alert_log(array( @date('Y-m-d H:i:s'), $path, 'status: '.$bin->status ));
					}elseif( $bin->status >= 400 ){
						$this->alert_log(array( @date('Y-m-d H:i:s'), $path, 'status: '.$bin->status ));
					}elseif( $bin->status >= 300 ){
						$this->alert_log(array( @date('Y-m-d H:i:s'), $path, 'status: '.$bin->status ));
					}elseif( $bin->status >= 200 ){
						$this->px->fs()->mkdir_r( dirname( $this->path_tmp_publish.'/htdocs'.$this->path_docroot.$path ) );
						$this->px->fs()->save_file( $this->path_tmp_publish.'/htdocs'.$this->path_docroot.$path, base64_decode( @$bin->body_base64 ) );
						foreach( $bin->relatedlinks as $link ){
							$link = $this->px->fs()->get_realpath( $link, dirname($this->path_docroot.$path).'/' );
							$link = $this->px->fs()->normalize_path( $link );
							$tmp_link = preg_replace( '/^'.preg_quote($this->px->get_path_controot(), '/').'/s', '/', $link );
							if( $this->px->fs()->is_dir( $this->px->get_path_docroot().'/'.$link ) ){
								$this->make_list_by_dir_scan( $tmp_link.'/' );
							}else{
								$this->add_queue( $tmp_link );
							}
						}
					}elseif( $bin->status >= 100 ){
						$this->alert_log(array( @date('Y-m-d H:i:s'), $path, 'status: '.$bin->status ));
					}

					break;
			}

			$this->log(array(
				@date('Y-m-d H:i:s') ,
				$path ,
				$proc_type ,
				$bin->status
			));

			if( !empty( $this->path_publish_dir ) ){
				// パブリッシュ先ディレクトリに都度コピー
				if( $this->px->fs()->is_file( $this->path_publish_dir.$this->path_docroot.$path ) ){
					$this->px->fs()->mkdir_r( dirname( $this->path_publish_dir.$this->path_docroot.$path ) );
					$this->px->fs()->copy(
						$this->path_tmp_publish.'/htdocs'.$this->path_docroot.$path ,
						$this->path_publish_dir.$this->path_docroot.$path
					);
					print ' -> copied to publish dir'."\n";
				}
			}

			unset($this->paths_queue[$path]);
			$this->paths_done[$path] = true;
			print $this->cli_report();

			$this->touch_lockfile();

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
				$this->path_tmp_publish.'/htdocs'.$this->path_docroot.$this->path_region ,
				$this->path_publish_dir.$this->path_docroot.$this->path_region
			);
		}
		print "\n";

		$this->unlock();//ロック解除

		print $this->cli_footer();
		exit;
	}

	/**
	 * パブリッシュログ
	 * @param array $row ログデータ
	 * @return bool ログ書き込みの成否
	 */
	private function log( $row ){
		$path_logfile = $this->path_tmp_publish.'publish_log.csv';
		return error_log( $this->px->fs()->mk_csv( array($row) ), 3, $path_logfile );
	}

	/**
	 * パブリッシュアラートログ
	 * @param array $row ログデータ
	 * @return bool ログ書き込みの成否
	 */
	private function alert_log( $row ){
		$path_logfile = $this->path_tmp_publish.'alert_log.csv';
		return error_log( $this->px->fs()->mk_csv( array($row) ), 3, $path_logfile );
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
		(new clearcache( $this->px ))->exec();
	}

	/**
	 * make list by sitemap
	 */
	private function make_list_by_sitemap(){
		$sitemap = $this->px->site()->get_sitemap();
		foreach( $sitemap as $page_info ){
			$href = $this->px->href( $page_info['path'] );
			$href = preg_replace( '/\/$/s', '/'.$this->px->get_directory_index_primary(), $href );
			$href = preg_replace( '/^'.preg_quote($this->px->get_path_controot(), '/').'/s', '/', $href );
			$this->add_queue( $href );
		}
		return true;
	}

	/**
	 * make list by directory scan
	 * @param string $path パス
	 */
	private function make_list_by_dir_scan( $path = null ){
		$process = array_keys( get_object_vars( $this->px->conf()->funcs->processor ) );
		foreach( $process as $key=>$val ){
			$process[$key] = preg_quote($val);
		}
		$preg_exts = '('.implode( '|', $process ).')';

		$realpath = $this->px->fs()->get_realpath('./'.$path);
		$ls = $this->px->fs()->ls( $realpath );
		if( $this->px->is_ignore_path( './'.$path ) ){
			return true;
		}
		foreach( $ls as $basename ){
			if( $this->px->fs()->is_dir( $realpath.$basename ) ){
				$this->make_list_by_dir_scan( $path.$basename.DIRECTORY_SEPARATOR );
			}else{
				$tmp_localpath = $this->px->fs()->get_realpath('/'.$path.$basename);
				$tmp_localpath = preg_replace( '/\.'.$preg_exts.'$/is', '', $tmp_localpath );
				if( $this->px->get_path_proc_type( $tmp_localpath ) == 'ignore' || $this->px->get_path_proc_type( $tmp_localpath ) == 'direct' ){
					$tmp_localpath = $this->px->fs()->get_realpath('/'.$path.$basename);
				}
				$tmp_localpath = $this->px->fs()->normalize_path( $tmp_localpath );
				$this->add_queue( $tmp_localpath );
			}
		}
		return true;
	}

	/**
	 * add queue
	 * @param string $path 対象のパス
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
	 * @param string $path 対象のパス
	 */
	private function is_region_path( $path ){
		$path = $this->px->fs()->get_realpath( '/'.$path );
		if( $this->px->fs()->is_dir('./'.$path) ){
			$path .= '/';
		}
		$path = $this->px->fs()->normalize_path($path);
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
		$tmp_path = $this->px->fs()->get_realpath( $this->px->conf()->path_publish_dir.'/' );
		if( !$this->px->fs()->is_dir( $tmp_path ) ){
			return false;
		}
		if( !$this->px->fs()->is_writable( $tmp_path ) ){
			return false;
		}
		return $tmp_path;
	}// get_path_publish_dir()

	/**
	 * パブリッシュをロックする。
	 * 
	 * @return bool ロック成功時に `true`、失敗時に `false` を返します。
	 */
	private function lock(){
		$lockfilepath = $this->path_lockfile;
		$timeout_limit = 5;

		if( !@is_dir( dirname( $lockfilepath ) ) ){
			$this->px->fs()->mkdir_r( dirname( $lockfilepath ) );
		}

		#	PHPのFileStatusCacheをクリア
		clearstatcache();

		$i = 0;
		while( $this->is_locked() ){
			$i ++;
			if( $i >= $timeout_limit ){
				return false;
				break;
			}
			sleep(1);

			#	PHPのFileStatusCacheをクリア
			clearstatcache();
		}
		$src = '';
		$src .= 'ProcessID='.getmypid()."\r\n";
		$src .= @date( 'Y-m-d H:i:s' , time() )."\r\n";
		$RTN = $this->px->fs()->save_file( $lockfilepath , $src );
		return	$RTN;
	}//lock()

	/**
	 * パブリッシュがロックされているか確認する。
	 * 
	 * @return bool ロック中の場合に `true`、それ以外の場合に `false` を返します。
	 */
	private function is_locked(){
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
	}//is_locked()

	/**
	 * パブリッシュロックを解除する。
	 * 
	 * @return bool ロック解除成功時に `true`、失敗時に `false` を返します。
	 */
	private function unlock(){
		$lockfilepath = $this->path_lockfile;

		#	PHPのFileStatusCacheをクリア
		clearstatcache();

		return @unlink( $lockfilepath );
	}//unlock()

	/**
	 * パブリッシュロックファイルの更新日を更新する。
	 * 
	 * @return bool 成功時に `true`、失敗時に `false` を返します。
	 */
	private function touch_lockfile(){
		$lockfilepath = $this->path_lockfile;

		#	PHPのFileStatusCacheをクリア
		clearstatcache();
		if( !is_file( $lockfilepath ) ){
			return false;
		}

		return touch( $lockfilepath );
	}//touch_lockfile()

}