<?php
/**
 * class pxcmd
 *
 * Pickles 2 のコアオブジェクトの1つ `$pxcmd` のオブジェクトクラスを定義します。
 *
 * @author Tomoya Koyanagi <tomk79@gmail.com>
 */
namespace picklesFramework2;

/**
 * PX Commands
 *
 * Pickles 2 のコアオブジェクトの1つ `$pxcmd` のオブジェクトクラスです。
 * このオブジェクトは、Pickles 2 の初期化処理の中で自動的に生成され、`$px` の内部に格納されます。
 *
 * メソッド `$px->pxcmd()` を通じてアクセスします。
 *
 * @author Tomoya Koyanagi <tomk79@gmail.com>
 */
class pxcmd{

	/**
	 * オブジェクト
	 * @access private
	 */
	private $px, $bowl;

	/**
	 * PXコマンド
	 * @access private
	 */
	private $pxcommands = array();

	/**
	 * Constructor
	 *
	 * @param object $px Picklesオブジェクト
	 */
	public function __construct($px){
		$this->px = $px;
		$this->pxcommands = array();

		// make instance $bowl
		$this->bowl = new bowl();

	}

	/**
	 * PXコマンドを登録する。
	 *
	 * @param string $cmd PXコマンド名
	 * @param function $fnc 実行するコマンド
	 * @return bool 成功時 `true`、失敗時 `false`
	 */
	public function register( $cmd, $fnc ){
		if( !is_string($cmd) ){
			return false;
		}
		if( array_key_exists($cmd, $this->pxcommands) ){
			trigger_error('PX Command "'.$cmd.'" is already registered.');
			return false;
		}
		$this->pxcommands[$cmd] = array(
			'cmd' => $cmd ,
			'name' => $cmd ,
			'fnc' => $fnc ,
		);

		$pxcmd = $this->px->get_px_command();
		if( is_array($pxcmd) && count($pxcmd) && $pxcmd[0] == $cmd ){
			$fnc($this->px);
		}
		return true;
	}

	/**
	 * `$bowl` オブジェクトを取得する。
	 *
	 * @return object $bowlオブジェクト
	 */
	public function bowl(){
		return $this->bowl;
	}

	/**
	 * CLI header を生成する。
	 * @return string Header
	 */
	public function get_cli_header(){
		$pxcmd = $this->px->get_px_command();
		@header('Content-type: text/plain');
		ob_start();
		print '------------'."\n";
		print 'Pickles '.$this->px->get_version().' -> '.$pxcmd[0]."\n";
		print 'PHP '.phpversion()."\n";
		print @date('c')."\n";
		print '------------'."\n";
		return ob_get_clean();
	}

	/**
	 * CLI footer を生成する。
	 * @return string Footer
	 */
	public function get_cli_footer(){
		ob_start();
		print '------------'."\n";
		print @date('c')."\n";
		print 'PX Command END;'."\n";
		print "\n";
		return ob_get_clean();
	}

	/**
	 * wrap GUI frame
	 *
	 * @param string $content コンテンツ領域のHTMLソースコード
	 * @return string HTML全体のHTMLソースコード
	 */
	public function wrap_gui_frame( $content ){
		$this->bowl()->send($content);
		unset($content);

		@header( 'Content-type: text/html; charset="UTF-8"' );

		// リソースをキャッシュ領域にコピー
		$path_pxcache = $this->px->fs()->get_realpath( $this->px->get_path_controot().$this->px->conf()->public_cache_dir.'/px/' );
		$realpath_pxcache = $this->px->fs()->get_realpath( $this->px->get_realpath_docroot().$path_pxcache );
		$this->px->fs()->mkdir( $realpath_pxcache );
		$this->px->fs()->copy_r( __DIR__.'/files/', $realpath_pxcache );

		$pxcmd = $this->px->get_px_command();
		ob_start();?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<title><?= htmlspecialchars( $this->px->conf()->name ?? "" ) ?></title>

		<!-- jQuery -->
		<script src="<?= htmlspecialchars( $path_pxcache.'scripts/jquery-3.6.0.min.js' ); ?>" type="text/javascript"></script>

		<!-- Bootstrap -->
		<link rel="stylesheet" href="<?= htmlspecialchars( $path_pxcache.'styles/bootstrap/css/bootstrap.min.css' ); ?>">
		<script src="<?= htmlspecialchars( $path_pxcache.'styles/bootstrap/js/bootstrap.min.js' ); ?>"></script>

		<!-- FESS -->
		<link rel="stylesheet" href="<?= htmlspecialchars( $path_pxcache.'styles/contents.css' ); ?>" type="text/css" />

		<!-- layout -->
		<link rel="stylesheet" href="<?= htmlspecialchars( $path_pxcache.'styles/layout.css' ); ?>" type="text/css" />

<?= $this->bowl()->pull('head') ?>
	</head>
	<body class="pxcmd">
		<div class="pxcmd-outline">
			<div class="pxcmd-pxfw clearfix">
				<div class="pxcmd-pxfw_l"><?= $this->px->get_pickles_logo_svg() ?> Pickles 2 (version: <?= htmlspecialchars( $this->px->get_version() ?? "" ) ?>)</div>
				<div class="pxcmd-pxfw_r">
<?php
	// $pxcommands = $this->pxcommands;
	// if( count($pxcommands) ){
	// 	print '<select onchange="window.location.href=\'?PX=\'+this.options[this.selectedIndex].value;">';
	// 	foreach( $pxcommands as $row ){
	// 		print '<option value="'.htmlspecialchars($row['cmd']).'"'.($row['cmd']==$pxcmd[0]?' selected="selected"':'').'>'.htmlspecialchars($row['name'] ?? "").'</option>';
	// 	}
	// 	print '</select>';
	// }
?>
				</div>
			</div><!-- /.pxcmd-pxfw -->

			<div class="pxcmd-header">
				<div class="pxcmd-project_title"><?= htmlspecialchars( $this->px->conf()->name ?? "" ) ?></div>
			</div><!-- /.pxcmd-header -->

			<div class="pxcmd-middle">
<?php
		$command_title = $pxcmd[0].(strlen($pxcmd[1] ?? "")?'.'.$pxcmd[1]:'');
		$page_title = null;
?>
			<div class="pxcmd-title">
		<?php if(strlen($page_title ?? "")){ ?>
				<p class="pxcmd-category_title"><a href="?PX=<?= htmlspecialchars( $command_title ?? "" ) ?>" style="color:inherit; text-decoration:none;"><?= htmlspecialchars($command_title) ?></a></p>
				<h1><?= htmlspecialchars( $page_title ?? "" ) ?></h1>
		<?php }else{ ?>
				<h1><a href="?PX=<?= htmlspecialchars( $command_title ?? "" ) ?>" style="color:inherit; text-decoration:none;"><?= htmlspecialchars( $command_title ?? "" ) ?></a></h1>
		<?php } ?>
			</div><!-- /.pxcmd-title -->
			<div id="content" class="contents">
<?= $this->bowl()->pull() ?>
			</div>
			</div><!-- /.pxcmd-middle -->

			<div class="pxcmd-footer">
				<p><a href="?" class="btn btn-default">PX Commands を終了する</a></p>
				<p>PHP <?= htmlspecialchars(phpversion()) ?></p>
				<p><?= htmlspecialchars(@date('c') ?? "") ?></p>
				<p class="pxcmd-credits"><a href="https://pickles2.pxt.jp/" target="_blank">Pickles Framework</a> (C)Tomoya Koyanagi, and Pickles Project.</p>
			</div><!-- /.footer -->
		</div>

	</body>
</html>
<?php
		return ob_get_clean();
	}

}
