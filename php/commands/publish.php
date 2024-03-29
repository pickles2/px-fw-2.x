<?php
/**
 * PX Commands "publish"
 */
namespace picklesFramework2\commands;

/**
 * PX Commands "publish"
 *
 * <dl>
 * 	<dt>PX=publish</dt>
 * 		<dd>パブリッシュのホーム画面を表示します。</dd>
 * 	<dt>PX=publish.run</dt>
 * 		<dd>パブリッシュを実行します。</dd>
 * 	<dt>PX=publish.version</dt>
 * 		<dd>Pickles Framework のバージョン番号を JSON 形式の文字列で返します。</dd>
 * </dl>
 */
class publish{

	/** Picklesオブジェクト */
	private $px;

	/** プラグイン設定 */
	private $plugin_conf;

	/** パス設定 */
	private $path_tmp_publish, $path_publish_dir, $path_controot;

	/** ドメイン設定 */
	private $domain;

	/** パブリッシュ範囲設定 */
	private $paths_region = array();

	/** パブリッシュ対象外範囲設定 */
	private $paths_ignore = array();

	/** キャッシュを消去しないフラグ */
	private $flg_keep_cache = false;

	/** ロックファイルの格納パス */
	private $path_lockfile;

	/** 処理待ちのパス一覧 */
	private $paths_queue = array();

	/** 処理済みのパス一覧 */
	private $paths_done = array();

	/** Extension をマッチさせる正規表現 */
	private $preg_exts;

	/**
	 * Before content function
	 * @param object $px Picklesオブジェクト
	 * @param object $options プラグイン設定
	 * ```
	 * {
	 * 	"paths_ignore": [
	 * 		// パブリッシュ対象から常に除外するパスを設定する。
	 * 		// (ここに設定されたパスは、動的なプレビューは可能)
	 * 		"/sample_pages/no_publish/*"
	 * 	]
	 * }
	 * ```
	 */
	public static function register( $px = null, $options = null ){

		if( count(func_get_args()) <= 1 ){
			return __CLASS__.'::'.__FUNCTION__.'('.( is_array($px) ? json_encode($px) : '' ).')';
		}

		// プラグイン設定の初期化
		if( !is_object($options) ){
			$options = json_decode('{}');
		}
		if( !isset($options->paths_ignore) || !is_array($options->paths_ignore) ){
			$options->paths_ignore = array();
		}

		$self = new self( $px, $options );

		$px->pxcmd()->register('publish', array($self, 'exec_home'));
	}

	/**
	 * constructor
	 * @param object $px Picklesオブジェクト
	 * @param object $options プラグイン設定
	 */
	public function __construct( $px, $options ){
		$this->px = $px;
		$this->plugin_conf = $options;

		$this->path_tmp_publish = $px->fs()->get_realpath( $px->get_realpath_homedir().'_sys/ram/publish/' );
		$this->path_lockfile = $this->path_tmp_publish.'applock.txt';
		if( $this->get_path_publish_dir() !== false ){
			$this->path_publish_dir = $this->get_path_publish_dir();
		}
		$this->domain = $px->conf()->domain;
		$this->path_controot = $px->conf()->path_controot;

		// Extensionをマッチさせる正規表現
		$process = array_keys( get_object_vars( $this->px->conf()->funcs->processor ) );
		foreach( $process as $key=>$val ){
			$process[$key] = preg_quote($val);
		}
		$this->preg_exts = '('.implode( '|', $process ).')';

		// パブリッシュ対象範囲
		$this->paths_region = array();
		$param_path_region = $this->px->req()->get_param('path_region');
		if( strlen($param_path_region ?? '') ){
			array_push( $this->paths_region, $param_path_region );
		}
		$param_paths_region = $this->px->req()->get_param('paths_region');
		if( is_array($param_paths_region ?? null) ){
			$this->paths_region = array_merge( $this->paths_region, $param_paths_region );
		}
		if( !count($this->paths_region) ){
			$path_region = $this->px->req()->get_request_file_path();
			$path_region = preg_replace('/^\\/*/is','/',$path_region);
			$path_region = preg_replace('/\/'.$this->px->get_directory_index_preg_pattern().'$/s','/',$path_region);
			array_push( $this->paths_region, $path_region );
		}

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
		foreach( $this->paths_region as $tmp_key => $tmp_localpath_region ){
			if( !$func_check_param_path( $tmp_localpath_region ) ){
				unset($this->paths_region[$tmp_key]);
				continue;
			}
		}

		foreach( $this->paths_region as $tmp_key => $tmp_localpath_region ){
			// 2重拡張子の2つ目を削除
			if( !is_dir('./'.$tmp_localpath_region) && preg_match( '/\.'.$this->preg_exts.'\.'.$this->preg_exts.'$/is', $tmp_localpath_region ) ){
				$this->paths_region[$tmp_key] = preg_replace( '/\.'.$this->preg_exts.'$/is', '', $tmp_localpath_region );
			}
			// 先頭がスラッシュじゃない場合は追加する
			$this->paths_region[$tmp_key] = preg_replace( '/^\\/*/is', '/', $this->paths_region[$tmp_key] );
		}
		unset(
			$path_region,
			$param_path_region,
			$param_paths_region,
			$func_check_param_path,
			$tmp_localpath_region,
			$tmp_key );

		// キャッシュを消去しないフラグ
		$this->flg_keep_cache = !!$this->px->req()->get_param('keep_cache');

		// パブリッシュ対象外の範囲
		$this->paths_ignore = $this->px->req()->get_param('paths_ignore');
		if( is_string($this->paths_ignore) ){
			$this->paths_ignore = array( $this->paths_ignore );
		}
		if( !is_array($this->paths_ignore) ){
			$this->paths_ignore = array();
		}
		foreach( $this->paths_ignore as $tmp_key => $tmp_localpath_region ){
			// 先頭がスラッシュじゃない場合は追加する
			$this->paths_ignore[$tmp_key] = preg_replace( '/^\\/*/is', '/', $this->paths_ignore[$tmp_key] );
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
		print 'docroot directory: '.$this->path_controot."\n";
		print 'ignore: '.join(', ', $this->plugin_conf->paths_ignore)."\n";
		print 'region: '.join(', ', $this->paths_region)."\n";
		print 'ignore (tmp): '.join(', ', $this->paths_ignore)."\n";
		print 'keep cache: '.($this->flg_keep_cache ? 'true' : 'false')."\n";
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
	 * @param object $px Picklesオブジェクト
	 */
	public function exec_home( $px ){
		$pxcmd = $this->px->get_px_command();

		if( ($pxcmd[1] ?? null) == 'version' ){
			// 命令が publish.version の場合、バージョン番号を返す。
			$val = $this->px->get_version();
			@header('Content-type: application/json; charset=UTF-8');
			print json_encode($val);
			exit;
		}
		if( ($pxcmd[1] ?? null) == 'run' ){
			// 命令が publish.run の場合、実行する。
			$this->exec_publish( $px );
			exit;
		}
		if( strlen($pxcmd[1] ?? "") ){
			// 命令が不明の場合、エラーを表示する。
			if( $this->px->req()->is_cmd() ){
				header('Content-type: text/plain;');
				print $this->cli_header();
				print 'execute PX command => "?PX=publish.run"'."\n";
				print $this->cli_footer();
			}else{
				$html = '<p>Go to <a href="?PX=publish">PX=publish</a> to execute publish.</p>'."\n";
				print $this->px->pxcmd()->wrap_gui_frame($html);
			}
			exit;
		}

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
				$html .= '	<ul><li style="word-break:break-all;">'.htmlspecialchars( $this->path_tmp_publish ?? "" ).'</li></ul>'."\n";
				$html .= '</div><!-- /.unit -->'."\n";
			}elseif(!is_writable($this->path_tmp_publish)){
				$html .= '<div class="unit">'."\n";
				$html .= '	<p class="error">パブリッシュ先一時ディレクトリに書き込み許可がありません。</p>'."\n";
				$html .= '	<ul><li style="word-break:break-all;">'.htmlspecialchars( $this->path_tmp_publish ?? "" ).'</li></ul>'."\n";
				$html .= '</div><!-- /.unit -->'."\n";
			}elseif( strlen($this->path_publish_dir ?? "") && !is_dir($this->path_publish_dir) ){
				$html .= '<div class="unit">'."\n";
				$html .= '	<p class="error">パブリッシュ先ディレクトリが存在しません。</p>'."\n";
				$html .= '	<ul><li style="word-break:break-all;">'.htmlspecialchars( ''.$this->px->dbh()->get_realpath( $this->path_publish_dir ).'/' ).'</li></ul>'."\n";
				$html .= '</div><!-- /.unit -->'."\n";
			}elseif( strlen($this->path_publish_dir ?? "") && !is_writable($this->path_publish_dir) ){
				$html .= '<div class="unit">'."\n";
				$html .= '	<p class="error">パブリッシュ先ディレクトリに書き込み許可がありません。</p>'."\n";
				$html .= '	<ul><li style="word-break:break-all;">'.htmlspecialchars( ''.$this->px->dbh()->get_realpath( $this->path_publish_dir ).'/' ).'</li></ul>'."\n";
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
				$html .= '	<blockquote><pre>'.htmlspecialchars( $this->px->fs()->read_file( $this->path_lockfile ) ?? "" ).'</pre></blockquote>'."\n";
				$html .= '	<p>'."\n";
				$html .= '		ロックファイルは下記の時刻に更新されました。<br />'."\n";
				$html .= '	</p>'."\n";
				$html .= '	<blockquote><pre>'.htmlspecialchars( ''.date( 'c', filemtime( $this->path_lockfile ) ) ?? "" ).'</pre></blockquote>'."\n";
				$html .= '	<p>'."\n";
				$html .= '		ロックファイルは、次のパスに存在します。<br />'."\n";
				$html .= '	</p>'."\n";
				$html .= '	<blockquote><pre>'.htmlspecialchars( ''.realpath( $this->path_lockfile ) ).'</pre></blockquote>'."\n";
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
	path = path.replace( new RegExp('^\\/'), '' );
	window.location.href = window.location.origin + "<?= $this->px->get_path_controot() ?>" + path + '?PX=publish&path_region='+encodeURIComponent(path);
}
</script>
<div class="unit">
	<p>プロジェクト『<?= htmlspecialchars($this->px->conf()->name ?? "") ?>』をパブリッシュします。</p>
	<table class="def" style="width:100%;">
		<colgroup><col width="30%" /><col width="70%" /></colgroup>
		<tr>
			<th style="word-break:break-all;">publish directory(tmp)</th>
			<td style="word-break:break-all;"><?= htmlspecialchars($this->path_tmp_publish ?? "") ?></td>
		</tr>
		<tr>
			<th style="word-break:break-all;">publish directory</th>
			<td style="word-break:break-all;"><?= htmlspecialchars($this->path_publish_dir ?? "") ?></td>
		</tr>
		<tr>
			<th style="word-break:break-all;">domain</th>
			<td style="word-break:break-all;"><?= htmlspecialchars($this->domain ?? "") ?></td>
		</tr>
		<tr>
			<th style="word-break:break-all;">docroot directory</th>
			<td style="word-break:break-all;"><?= htmlspecialchars($this->path_controot ?? "") ?></td>
		</tr>
		<tr>
			<th style="word-break:break-all;">region</th>
			<td style="word-break:break-all;">
				<div class="cont_publish_target_path_preview">
					<div style="word-break:break-all;"><?= htmlspecialchars($this->paths_region[0] ?? "") ?></div>
					<div class="small"><a href="javascript:cont_EditPublishTargetPath();" class="icon">変更する</a></div>
				</div>
				<div class="cont_publish_target_path_editor" style="display:none;">
					<form action="?" method="get" onsubmit="cont_EditPublishTargetPathApply(this); return false;" class="inline">
						<input type="text" name="path" size="25" style="max-width:70%;" value="<?= htmlspecialchars($this->paths_region[0] ?? "") ?>" />
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
	<div><input type="hidden" name="PX" value="publish.run" /><input type="hidden" name="path_region" value="<?= htmlspecialchars($this->px->req()->get_param('path_region') ?? "") ?>" /></div>
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
	 * @param object $px Picklesオブジェクト
	 */
	private function exec_publish( $px ){
		header('Content-type: text/plain;');
		$total_time = time();
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
			print '  (lockfile updated: '.@date('c', filemtime($this->path_lockfile)).')'."\n";
			print 'Try again later...'."\n";
			print 'exit.'."\n";
			print $this->cli_footer();
			exit;
		}
		print "\n";
		print "\n";

		print '============'."\n";
		print '## Clearing caches'."\n";
		print "\n";
		$this->clearcache();
		print "\n";

		// make instance $site
		$this->px->set_site( new \picklesFramework2\site($this->px) );

		print '============'."\n";
		print '## Making list'."\n";
		print "\n";
		print '-- making list by Sitemap'."\n";
		$this->make_list_by_sitemap();
		print "\n";
		print '-- making list by Directory Scan'."\n";
		foreach( $this->get_region_root_path() as $path_region ){
			$this->make_list_by_dir_scan( $path_region );
		}
		print "\n";
		print '============'."\n";
		print '## Start publishing'."\n";
		print "\n";
		print $this->cli_report();
		print "\n";

		file_put_contents($this->path_tmp_publish.'timelog.txt', 'Started at: '.date('c', $total_time)."\n", FILE_APPEND); // 2020-04-01 @tomk79 記録するようにした。

		while(1){
			set_time_limit(5*60);
			flush();
			if( !count( $this->paths_queue ) ){
				break;
			}
			foreach( $this->paths_queue as $path=>$val ){break;}
			print '------------'."\n";
			print $path."\n";
			$path_type = $this->px->get_path_type( $path );
			if( $path_type != 'normal' && $path_type !== false ){
				// 物理ファイルではないものはスキップ
				print ' -> Non file URL.'."\n";

			}elseif( $this->px->fs()->is_dir(dirname($_SERVER['SCRIPT_FILENAME']).$path) ){
				// ディレクトリを処理
				$this->px->fs()->mkdir( $this->path_tmp_publish.'/htdocs'.$this->path_controot.$path );
				print ' -> A directory.'."\n";

			}else{
				// ファイルを処理
				$ext = strtolower( pathinfo( $path , PATHINFO_EXTENSION ) );
				$proc_type = $this->px->get_path_proc_type( $path );
				$status_code = null;
				$status_message = null;
				$errors = array();
				$microtime = microtime(true);
				switch( $proc_type ){
					case 'pass':
						// pass
						print $ext.' -> '.$proc_type."\n";
						if( !$this->px->fs()->mkdir_r( dirname( $this->path_tmp_publish.'/htdocs'.$this->path_controot.$path ) ) ){
							$status_code = 500;
							$this->alert_log(array( @date('c'), $path, 'FAILED to making parent directory.' ));
							break;
						}
						if( !$this->px->fs()->copy( dirname($_SERVER['SCRIPT_FILENAME']).$path , $this->path_tmp_publish.'/htdocs'.$this->path_controot.$path ) ){
							$status_code = 500;
							$this->alert_log(array( @date('c'), $path, 'FAILED to copying file.' ));
							break;
						}
						$status_code = 200;
						break;

					case 'direct':
					default:
						// pickles execute
						print $ext.' -> '.$proc_type."\n";

						$bin = $this->px->internal_sub_request( $path, array('output'=>'json'), $return_var );
						if( !is_object($bin) ){
							$bin = new \stdClass;
							$bin->status = 500;
							$tmp_err_msg = 'Unknown server error';
							$tmp_err_msg .= "\n".'PHP returned status code "'.$return_var.'" on exit. There is a possibility of "Parse Error" or "Fatal Error" was occured.';
							$tmp_err_msg .= "\n".'Hint: Normally, "Pickles 2" content files are parsed as PHP scripts. If you are using "<'.'?", "<'.'?php", "<'.'%", or "<'.'?=" unintentionally in contents, might be the cause.';
							$bin->message = $tmp_err_msg;
							$bin->errors = array();
							// $bin->errors = array($tmp_err_msg);
							$bin->relatedlinks = array();
							$bin->body_base64 = base64_encode('');
							// $bin->body_base64 = base64_encode($tmp_err_msg);
							unset($tmp_err_msg);
						}
						$status_code = $bin->status ?? null;
						$status_message = $bin->message ?? null;
						$errors = $bin->errors ?? null;
						if( $bin->status >= 500 ){
							$this->alert_log(array( @date('c'), $path, 'status: '.$bin->status.' '.$bin->message ));
						}elseif( $bin->status >= 400 ){
							$this->alert_log(array( @date('c'), $path, 'status: '.$bin->status.' '.$bin->message ));
						}elseif( $bin->status >= 300 ){
							$this->alert_log(array( @date('c'), $path, 'status: '.$bin->status.' '.$bin->message ));
						}elseif( $bin->status >= 200 ){
							// 200 番台は正常
						}elseif( $bin->status >= 100 ){
							$this->alert_log(array( @date('c'), $path, 'status: '.$bin->status.' '.$bin->message ));
						}else{
							$this->alert_log(array( @date('c'), $path, 'Unknown status code.' ));
						}

						// コンテンツの書き出し処理
						// エラーが含まれている場合でも、得られたコンテンツを出力する。
						$this->px->fs()->mkdir_r( dirname( $this->path_tmp_publish.'/htdocs'.$this->path_controot.$path ) );
						$this->px->fs()->save_file( $this->path_tmp_publish.'/htdocs'.$this->path_controot.$path, base64_decode( $bin->body_base64 ?? null ) );
						foreach( $bin->relatedlinks as $link ){
							$link = $this->px->fs()->get_realpath( $link, dirname($this->path_controot.$path).'/' );
							$link = $this->px->fs()->normalize_path( $link );
							$tmp_link = preg_replace( '/^'.preg_quote($this->px->get_path_controot(), '/').'/s', '/', $link );
							if( $this->px->fs()->is_dir( $this->px->get_realpath_docroot().'/'.$link ) ){
								$this->make_list_by_dir_scan( $tmp_link.'/' );
							}else{
								$this->add_queue( $tmp_link );
							}
						}

						// エラーメッセージを alert_log に追記
						if( is_array( $bin->errors ) && count( $bin->errors ) ){
							foreach( $bin->errors as $tmp_error_row ){
								$this->alert_log(array( @date('c'), $path, $tmp_error_row ));
							}
						}

						break;
				}

				$str_errors = '';
				if( is_array($errors) && count($errors) ){
					$str_errors .= count($errors).' errors: ';
					$str_errors .= implode(', ', $errors).';';
				}
				$this->log(array(
					@date('c') ,
					$path ,
					$proc_type ,
					$status_code ,
					$status_message ,
					$str_errors,
					(file_exists($this->path_tmp_publish.'/htdocs'.$this->path_controot.$path) ? filesize($this->path_tmp_publish.'/htdocs'.$this->path_controot.$path) : false),
					microtime(true)-$microtime
				));

			}

			if( !empty( $this->path_publish_dir ) ){
				// パブリッシュ先ディレクトリに都度コピー
				if( $this->px->fs()->is_file( $this->path_tmp_publish.'/htdocs'.$this->path_controot.$path ) ){
					$this->px->fs()->mkdir_r( dirname( $this->path_publish_dir.$this->path_controot.$path ) );
					$this->px->fs()->copy(
						$this->path_tmp_publish.'/htdocs'.$this->path_controot.$path ,
						$this->path_publish_dir.$this->path_controot.$path
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

		if( !empty( $this->path_publish_dir ) ){
			// パブリッシュ先ディレクトリを同期
			print '============'."\n";
			print '## Sync to publish directory.'."\n";
			print "\n";
			set_time_limit(30*60);
			foreach( $this->paths_region as $path_region ){
				$this->sync_dir(
					$this->path_tmp_publish.'/htdocs'.$this->path_controot ,
					$this->path_publish_dir.$this->path_controot ,
					$path_region
				);
			}
		}
		print "\n";
		print '============'."\n";
		print '## done.'."\n";
		print "\n";

		$path_logfile = $this->path_tmp_publish.'alert_log.csv';
		clearstatcache();
		if( $this->px->fs()->is_file( $path_logfile ) ){
			sleep(1);
			$alert_log = $this->px->fs()->read_csv( $path_logfile );
			array_shift( $alert_log );
			$alert_total_count = count($alert_log);
			$max_preview_count = 20;
			$alert_header = '************************* '.$alert_total_count.' ALERTS ******';
			print $alert_header."\n";
			print "\n";
			$counter = 0;
			foreach( $alert_log as $key=>$row ){
				$counter ++;
				$tmp_number = '  ['.($key+1).'] ';
				print $tmp_number;
				print preg_replace('/(\r\n|\r|\n)/s', '$1'.str_pad('', strlen($tmp_number ?? ""), ' '), $row[2])."\n";
				print str_pad('', strlen($tmp_number ?? ""), ' ').'  in '.$row[1]."\n";
				if( $counter >= $max_preview_count ){ break; }
			}
			if( $alert_total_count > $max_preview_count ){
				print '  [etc...]'."\n";
			}
			print "\n";
			print '    You got total '.$alert_total_count.' alerts.'."\n";
			print '    see more: '.realpath($path_logfile)."\n";
			print str_pad('', strlen($alert_header ?? ""), '*')."\n";
			print "\n";
		}

		$end_time = time();
		print 'Total Time: '.($end_time - $total_time).' sec.'."\n";
		file_put_contents($this->path_tmp_publish.'timelog.txt', 'Ended at: '.date('c', $end_time)."\n", FILE_APPEND); // 2020-04-01 @tomk79 記録するようにした。
		file_put_contents($this->path_tmp_publish.'timelog.txt', 'Total Time: '.($end_time - $total_time).' sec'."\n", FILE_APPEND); // 2020-04-01 @tomk79 記録するようにした。
		print "\n";

		$this->unlock();//ロック解除

		print $this->cli_footer();
		exit;
	}

	/**
	 * ディレクトリを同期する。
	 *
	 * @param string $path_sync_from 同期元のルートディレクトリ
	 * @param string $path_sync_to 同期先のルートディレクトリ
	 * @param string $path_region ルート以下のパス
	 * @return bool 常に `true` を返します。
	 */
	private function sync_dir( $path_sync_from , $path_sync_to, $path_region ){
		print 'Copying files and directories...';
		$this->sync_dir_copy_r( $path_sync_from , $path_sync_to, $path_region );
		print "\n";
		print 'Deleting removed files and directories...';
		$this->sync_dir_compare_and_cleanup( $path_sync_to , $path_sync_from, $path_region );
		print "\n";
		return true;
	}

	/**
	 * ディレクトリを複製する(下層ディレクトリも全てコピー)
	 *
	 * ただし、ignore指定されているパスに対しては操作を行わない。
	 *
	 * @param string $from コピー元ファイルのパス
	 * @param string $to コピー先のパス
	 * @param string $path_region ルート以下のパス
	 * @param int $perm 保存するファイルに与えるパーミッション
	 * @return bool 成功時に `true`、失敗時に `false` を返します。
	 */
	private function sync_dir_copy_r( $from, $to, $path_region, $perm = null ){
		static $count = 0;
		if( $count%100 == 0 ){print '.';}
		$count ++;

		$from = $this->px->fs()->localize_path($from);
		$to   = $this->px->fs()->localize_path($to  );
		$path_region = $this->px->fs()->localize_path($path_region);

		$result = true;

		if( $this->px->fs()->is_file( $from.$path_region ) ){
			if( $this->px->fs()->mkdir_r( dirname( $to.$path_region ) ) ){
				if( $this->px->is_ignore_path( $path_region ) ){
					// ignore指定されているパスには、操作しない。
				}elseif( !$this->is_region_path( $path_region ) ){
					// 範囲外のパスには、操作しない。
				}else{
					if( !$this->px->fs()->copy( $from.$path_region , $to.$path_region , $perm ) ){
						$result = false;
					}
				}
			}else{
				$result = false;
			}
		}elseif( $this->px->fs()->is_dir( $from.$path_region ) ){
			if( !$this->px->fs()->is_dir( $to.$path_region ) ){
				if( $this->px->is_ignore_path( $path_region ) ){
					// ignore指定されているパスには、操作しない。
				}elseif( !$this->is_region_path( $path_region ) ){
					// 範囲外のパスには、操作しない。
				}else{
					if( !$this->px->fs()->mkdir_r( $to.$path_region ) ){
						$result = false;
					}
				}
			}
			$itemlist = $this->px->fs()->ls( $from.$path_region );
			foreach( $itemlist as $Line ){
				if( $Line == '.' || $Line == '..' ){ continue; }
				if( $this->px->fs()->is_dir( $from.$path_region.DIRECTORY_SEPARATOR.$Line ) ){
					if( $this->px->fs()->is_file( $to.$path_region.DIRECTORY_SEPARATOR.$Line ) ){
						continue;
					}elseif( !$this->px->fs()->is_dir( $to.$path_region.DIRECTORY_SEPARATOR.$Line ) ){
						if( $this->px->is_ignore_path( $path_region.DIRECTORY_SEPARATOR.$Line ) ){
							// ignore指定されているパスには、操作しない。
						}elseif( !$this->is_region_path( $path_region.DIRECTORY_SEPARATOR.$Line ) ){
							// 範囲外のパスには、操作しない。
						}else{
							if( !$this->px->fs()->mkdir_r( $to.$path_region.DIRECTORY_SEPARATOR.$Line ) ){
								$result = false;
							}
						}
					}
					if( !$this->sync_dir_copy_r( $from , $to, $path_region.DIRECTORY_SEPARATOR.$Line , $perm ) ){
						$result = false;
					}
					continue;
				}elseif( $this->px->fs()->is_file( $from.$path_region.DIRECTORY_SEPARATOR.$Line ) ){
					if( !$this->sync_dir_copy_r( $from, $to, $path_region.DIRECTORY_SEPARATOR.$Line , $perm ) ){
						$result = false;
					}
					continue;
				}
			}
		}

		return $result;
	}

	/**
	 * ディレクトリの内部を比較し、$comparisonに含まれない要素を$targetから削除する。
	 *
	 * ただし、ignore指定されているパスに対しては操作を行わない。
	 *
	 * @param string $target クリーニング対象のディレクトリパス
	 * @param string $comparison 比較するディレクトリのパス
	 * @param string $path_region ルート以下のパス
	 * @return bool 成功時 `true`、失敗時 `false` を返します。
	 */
	private function sync_dir_compare_and_cleanup( $target , $comparison, $path_region ){
		static $count = 0;
		if( $count%100 == 0 ){print '.';}
		$count ++;

		if( is_null( $comparison ) || is_null( $target ) ){
			return false;
		}

		$target = $this->px->fs()->localize_path($target);
		$comparison = $this->px->fs()->localize_path($comparison);
		$path_region = $this->px->fs()->localize_path($path_region);
		$flist = array();

		// 先に、ディレクトリ内をスキャンする
		if( $this->px->fs()->is_dir( $target.$path_region ) ){
			$flist = $this->px->fs()->ls( $target.$path_region );
			foreach ( $flist as $Line ){
				if( $Line == '.' || $Line == '..' ){ continue; }
				$this->sync_dir_compare_and_cleanup( $target , $comparison, $path_region.DIRECTORY_SEPARATOR.$Line );
			}
			$flist = $this->px->fs()->ls( $target.$path_region );
		}


		if( !file_exists( $comparison.$path_region ) && file_exists( $target.$path_region ) ){
			if( $this->px->is_ignore_path( $path_region ) ){
				// ignore指定されているパスには、操作しない。
				return true;
			}elseif( !$this->is_region_path( $path_region ) ){
				// 範囲外のパスには、操作しない。
				return true;
			}
			if( $this->px->fs()->is_dir( $target.$path_region ) ){
				if( !count($flist) ){
					// ディレクトリの場合は、内容が空でなければ削除しない。
					$this->px->fs()->rm( $target.$path_region );
				}
			}else{
				$this->px->fs()->rm( $target.$path_region );
			}

			return true;
		}

		return true;
	}


	/**
	 * パブリッシュログ
	 * @param array $row ログデータ
	 * @return bool ログ書き込みの成否
	 */
	private function log( $row ){
		$path_logfile = $this->path_tmp_publish.'publish_log.csv';
		if( !is_file( $path_logfile ) ){
			error_log( $this->px->fs()->mk_csv( array(array(
				'datetime' ,
				'path' ,
				'proc_type' ,
				'status_code' ,
				'status_message' ,
				'errors' ,
				'filesize',
				'proc_microtime'
			)) ), 3, $path_logfile );
			clearstatcache();
		}
		return error_log( $this->px->fs()->mk_csv( array($row) ), 3, $path_logfile );
	}

	/**
	 * パブリッシュアラートログ
	 * @param array $row ログデータ
	 * @return bool ログ書き込みの成否
	 */
	private function alert_log( $row ){
		$path_logfile = $this->path_tmp_publish.'alert_log.csv';
		if( !is_file( $path_logfile ) ){
			error_log( $this->px->fs()->mk_csv( array(array(
				'datetime' ,
				'path' ,
				'error_message'
			)) ), 3, $path_logfile );
			clearstatcache();
		}
		return error_log( $this->px->fs()->mk_csv( array($row) ), 3, $path_logfile );
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

		// キャッシュを消去
		if( !$this->flg_keep_cache ){
			(new \picklesFramework2\commands\clearcache( $this->px ))->exec();
		}else{
			// 一時パブリッシュディレクトリをクリーニング
			echo '-- cleaning "publish"'."\n";
			$this->cleanup_tmp_publish_dir( $this->path_tmp_publish );
		}

		return true;
	}

	/**
	 * 一時パブリッシュディレクトリをクリーニング
	 * @param string $path クリーニング対象のパス
	 * @param string $localpath $pathの仮想のパス (再帰処理のために使用)
	 */
	private function cleanup_tmp_publish_dir( $path, $localpath = null ){
		$count = 0;
		$ls = $this->px->fs()->ls($path.$localpath);
		foreach( $ls as $basename ){
			if( $localpath.$basename == '.gitkeep' ){
				continue;
			}
			if( $this->px->fs()->is_dir($path.$localpath.$basename) ){
				$count += $this->cleanup_tmp_publish_dir( $path, $localpath.$basename.DIRECTORY_SEPARATOR );

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
	 * make list by sitemap
	 *
	 * @return bool 常に `true` を返します。
	 */
	private function make_list_by_sitemap(){
		$sitemap = $this->px->site()->get_sitemap();
		foreach( $sitemap as $page_info ){
			set_time_limit(30);
			$href = $this->px->href( $page_info['path'] );
			if( preg_match('/^(?:[a-zA-Z0-9]+\:)?\/\//', $href) ){
				// プロトコル名、またはドメイン名から始まるリンク先はスキップ
				continue;
			}
			$href = preg_replace( '/\/$/s', '/'.$this->px->get_directory_index_primary(), $href );
			$href = preg_replace( '/^'.preg_quote($this->px->get_path_controot(), '/').'/s', '/', $href );
			$this->add_queue( $href );
		}
		return true;
	}

	/**
	 * make list by directory scan
	 *
	 * @param string $path ファイル または ディレクトリ のパス
	 * @return bool 常に真
	 */
	private function make_list_by_dir_scan( $path = null ){

		$realpath = $this->px->fs()->get_realpath('./'.$path);

		if( !file_exists( $realpath ) ){
			// 直にファイルが存在しない場合、2重拡張子のファイルを検索
			$tmp_process = array_keys( get_object_vars( $this->px->conf()->funcs->processor ) );
			foreach( $tmp_process as $tmp_ext ){
				if( $this->px->fs()->is_file( $realpath.'.'.$tmp_ext ) ){
					$realpath = $realpath.'.'.$tmp_ext;
					break;
				}
			}
			unset($tmp_process, $tmp_ext);
		}

		if( $this->px->fs()->is_file( $realpath ) ){
			$tmp_localpath = $this->px->fs()->get_realpath('/'.$path);
			if( preg_match( '/\.'.$this->preg_exts.'\.'.$this->preg_exts.'$/is', $tmp_localpath ) ){
				$tmp_localpath = preg_replace( '/\.'.$this->preg_exts.'$/is', '', $tmp_localpath );
			}
			if( $this->px->get_path_proc_type( $tmp_localpath ) == 'ignore' || $this->px->get_path_proc_type( $tmp_localpath ) == 'pass' ){
				$tmp_localpath = $this->px->fs()->get_realpath('/'.$path);
			}
			$tmp_localpath = $this->px->fs()->normalize_path( $tmp_localpath );
			$this->add_queue( $tmp_localpath );
			return true;
		}

		$ls = $this->px->fs()->ls( $realpath );
		if( !is_array($ls) ){
			$ls = array();
		}
		// ↓ `/index.html` がignoreされている場合に、
		// 　ディレクトリスキャンがキャンセルされてしまう問題があり、
		// 　ここでの評価はしないことにした。
		// 　※add_queue()で評価しているので、結果問題なし。
		// if( $this->px->is_ignore_path( './'.$path ) ){
		// 	return true;
		// }


		foreach( $this->px->conf()->paths_proc_type as $row => $type ){
			// $conf->paths_proc_type の設定から、
			// 明らかに除外できると判断できるディレクトリは再帰処理をキャンセルする。
			// 設定値の末尾が `/*` で終わっている ignore 指定の行は、 "ディレクトリ以下すべて除外" と断定し、
			// これにマッチしたディレクトリをキャンセルの対象とする。
			if( !is_string($row) ){
				continue;
			}
			if( $type != 'ignore' ){
				continue;
			}
			if( strrpos($row, '/*') !== strlen($row)-2 ){
				continue;
			}
			$preg_pattern = preg_quote($this->px->fs()->normalize_path($this->px->fs()->get_realpath($row)), '/');
			$realpath_controot = $this->px->fs()->normalize_path( $this->px->fs()->get_realpath( $this->px->get_path_docroot().$this->px->get_path_controot() ) );
			if( preg_match('/\*/',$preg_pattern) ){
				// ワイルドカードが使用されている場合
				$preg_pattern = preg_quote($row,'/');
				$preg_pattern = preg_replace('/'.preg_quote('\*','/').'/','(?:.*?)',$preg_pattern);//ワイルドカードをパターンに反映
			}elseif(is_dir($realpath_controot.$row)){
				$preg_pattern = preg_quote($this->px->fs()->normalize_path($this->px->fs()->get_realpath($row)).'/','/');
			}elseif(is_file($realpath_controot.$row)){
				$preg_pattern = preg_quote($this->px->fs()->normalize_path($this->px->fs()->get_realpath($row)),'/');
			}
			$path_child = $this->px->fs()->normalize_path( $this->px->fs()->get_realpath( $path ).'/' );
			if( preg_match( '/^'.$preg_pattern.'$/s' , $path_child ) ){
				return true;
			}
		}

		foreach( $ls as $basename ){
			set_time_limit(30);
			$this->make_list_by_dir_scan( $path.DIRECTORY_SEPARATOR.$basename );
		}
		return true;
	}

	/**
	 * add queue
	 * @param string $path 対象のパス
	 * @return bool 真偽
	 */
	private function add_queue( $path ){
		$path_type = $this->px->get_path_type( $path );
		if($path_type != 'normal'){
			// `normal` ではないもの(`data`, `javascript`, `anchor`, `full_url` など)は、
			// 物理ファイルを出力するものではないので、キューに送らない。
			return false;
		}

		$path = $this->px->fs()->normalize_path( $this->px->fs()->get_realpath( $path, $this->path_controot ) );
		$path = preg_replace('/\#.*$/', '', $path);
		$path = preg_replace('/\?.*$/', '', $path);
		if( preg_match( '/\/$/', $path ) ){
			$path .= $this->px->get_directory_index_primary();
		}

		if( $this->px->is_ignore_path( $path ) || $this->is_ignore_path( $path ) || !$this->is_region_path( $path ) ){
			// 対象外, パブリッシュ対象外, 範囲外
			// 対象外パスの親ディレクトリが対象パスの場合は、ディレクトリ単体でキューに登録を試みる。
			// 　　ディレクトリの内容がすべて一時対象外に指定された場合に、
			// 　　一時パブリッシュディレクトリにフォルダが作られないため、
			// 　　同期時にディレクトリごと削除されてしまうことを防止するため。
			$dirname = $this->px->fs()->normalize_path(dirname($path));
			if($dirname != '/'){ $this->add_queue( $dirname ); }
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
	 * パブリッシュ対象か調べる
	 * @param string $path 対象のパス
	 * @return bool 真偽
	 */
	private function is_ignore_path( $path ){
		static $rtn = array();
		if( is_null($path) ){
			return true;
		}
		$path = $this->px->fs()->get_realpath( '/'.$path );
		if( is_dir('./'.$path) ){
			$path .= '/'.$this->px->get_directory_index_primary();
		}
		if( preg_match('/(?:\/|\\\\)$/', $path) ){
			$path .= $this->px->get_directory_index_primary();
		}
		$path = $this->px->fs()->normalize_path($path);

		if( is_bool( $rtn[$path] ?? null ) ){
			return $rtn[$path];
		}

		foreach( $this->plugin_conf->paths_ignore as $row ){
			if(!is_string($row)){continue;}
			$preg_pattern = preg_quote($this->px->fs()->normalize_path($this->px->fs()->get_realpath($row)), '/');
			if( preg_match('/\*/',$preg_pattern) ){
				// ワイルドカードが使用されている場合
				$preg_pattern = preg_quote($row,'/');
				$preg_pattern = preg_replace('/'.preg_quote('\*','/').'/','(?:.*?)',$preg_pattern);//ワイルドカードをパターンに反映
			}elseif(is_dir($row)){
				$preg_pattern = preg_quote($this->px->fs()->normalize_path($this->px->fs()->get_realpath($row)).'/','/');
			}elseif(is_file($row)){
				$preg_pattern = preg_quote($this->px->fs()->normalize_path($this->px->fs()->get_realpath($row)),'/');
			}
			if( preg_match( '/^'.$preg_pattern.'$/s' , $path ) ){
				$rtn[$path] = true;
				return $rtn[$path];
			}
		}
		foreach( $this->paths_ignore as $path_ignore ){
			$preg_pattern = preg_quote( $path_ignore, '/' );
			if( preg_match('/'.preg_quote('\*','/').'/',$preg_pattern) ){
				// ワイルドカードが使用されている場合
				$preg_pattern = preg_replace('/'.preg_quote('\*','/').'/','(?:.*?)',$preg_pattern);//ワイルドカードをパターンに反映
				$preg_pattern = $preg_pattern.'$';//前方・後方一致
			}
			if( preg_match( '/^'.$preg_pattern.'/s' , $path ) ){
				$rtn[$path] = true;
				return $rtn[$path];
			}
		}
		$rtn[$path] = false;// <- default
		return $rtn[$path];
	}

	/**
	 * パブリッシュ範囲内か調べる
	 * @param string $path 対象のパス
	 * @return bool 真偽
	 */
	private function is_region_path( $path ){
		$path = $this->px->fs()->get_realpath( '/'.$path );
		if( $this->px->fs()->is_dir('./'.$path) ){
			$path .= '/';
		}
		$path = $this->px->fs()->normalize_path($path);
		$is_region = false;
		foreach( $this->paths_region as $path_region ){
			if( preg_match( '/^'.preg_quote( $path_region, '/' ).'/s' , $path ) ){
				$is_region = true;
				break;
			}
		}
		if( !$is_region ){
			return false;
		}
		foreach( $this->paths_ignore as $path_ignore ){
			$preg_pattern = preg_quote( $path_ignore, '/' );
			if( preg_match('/'.preg_quote('\*','/').'/',$preg_pattern) ){
				// ワイルドカードが使用されている場合
				$preg_pattern = preg_replace('/'.preg_quote('\*','/').'/','(?:.*?)',$preg_pattern);//ワイルドカードをパターンに反映
				$preg_pattern = $preg_pattern.'$';//前方・後方一致
			}
			if( preg_match( '/^'.$preg_pattern.'/s' , $path ) ){
				return false;
			}
		}
		return true;
	}


	/**
	 * パブリッシュ範囲のルートパスを得る
	 * @return string パブリッシュ範囲のルートパス
	 */
	private function get_region_root_path(){
		$rtn = array();
		foreach( $this->paths_region as $path_region ){
			$path = $this->px->fs()->get_realpath( '/'.$path_region );
			$path = $this->px->fs()->normalize_path($path);
			// ↓スキャンする対象が実在するディレクトリである必要はないので削除。
			// 　実在しない場合は無視されるだけなので問題ない。
			// 　この処理が有効だった場合、ファイル名名指しでパブリッシュしようとした場合にも、
			// 　実在する親ディレクトリに遡ってスキャンしてしまうため、無駄に処理に時間がかかってしまっていた。
			// while( !$this->px->fs()->is_dir('./'.$path) ){
			// 	$path = $this->px->fs()->normalize_path(dirname($path).'/');
			// }
			array_push($rtn, $path);
		}
		return $rtn;
	}


	/**
	 * パブリッシュ先ディレクトリを取得
	 */
	private function get_path_publish_dir(){
		if( !strlen( $this->px->conf()->path_publish_dir ?? "" ) ){
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
	}

	/**
	 * パブリッシュをロックする。
	 *
	 * @return bool ロック成功時に `true`、失敗時に `false` を返します。
	 */
	private function lock(){
		$lockfilepath = $this->path_lockfile;
		$timeout_limit = 5;

		if( !$this->px->fs()->is_dir( dirname( $lockfilepath ) ) ){
			$this->px->fs()->mkdir_r( dirname( $lockfilepath ) );
		}

		// PHPのFileStatusCacheをクリア
		clearstatcache();

		$i = 0;
		while( $this->is_locked() ){
			$i ++;
			if( $i >= $timeout_limit ){
				return false;
				break;
			}
			sleep(1);

			// PHPのFileStatusCacheをクリア
			clearstatcache();
		}
		$src = '';
		$src .= 'ProcessID='.getmypid()."\r\n";
		$src .= @date( 'c', time() )."\r\n";
		$RTN = $this->px->fs()->save_file( $lockfilepath , $src );

		// 割り込みを検証
		clearstatcache();
		sleep(1);
		clearstatcache();
		if($src !== file_get_contents( $lockfilepath )){
			return false;
		}

		return	$RTN;
	}

	/**
	 * パブリッシュがロックされているか確認する。
	 *
	 * @return bool ロック中の場合に `true`、それ以外の場合に `false` を返します。
	 */
	private function is_locked(){
		$lockfilepath = $this->path_lockfile;
		$lockfile_expire = 60*30;//有効期限は30分

		// PHPのFileStatusCacheをクリア
		clearstatcache();

		if( $this->px->fs()->is_file($lockfilepath) ){
			if( ( time() - filemtime($lockfilepath) ) > $lockfile_expire ){
				// 有効期限を過ぎていたら、ロックは成立する。
				return false;
			}
			return true;
		}
		return false;
	}

	/**
	 * パブリッシュロックを解除する。
	 *
	 * @return bool ロック解除成功時に `true`、失敗時に `false` を返します。
	 */
	private function unlock(){
		$lockfilepath = $this->path_lockfile;

		clearstatcache();
		if( !$this->px->fs()->is_file( $lockfilepath ) ){
			return true;
		}

		return unlink( $lockfilepath );
	}

	/**
	 * パブリッシュロックファイルの更新日を更新する。
	 *
	 * @return bool 成功時に `true`、失敗時に `false` を返します。
	 */
	private function touch_lockfile(){
		$lockfilepath = $this->path_lockfile;

		clearstatcache();
		if( !is_file( $lockfilepath ) ){
			return false;
		}

		return touch( $lockfilepath );
	}

}
