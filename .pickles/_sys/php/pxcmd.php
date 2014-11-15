<?php
/**
 * class pxcmd
 * 
 * Pickles2 のコアオブジェクトの1つ `$pxcmd` のオブジェクトクラスを定義します。
 * 
 * @author Tomoya Koyanagi <tomk79@gmail.com>
 */
namespace picklesFramework2;

/**
 * PX Commands
 * 
 * Pickles2 のコアオブジェクトの1つ `$pxcmd` のオブジェクトクラスです。
 * このオブジェクトは、Pickles2 の初期化処理の中で自動的に生成され、`$px` の内部に格納されます。
 * 
 * メソッド `$px->pxcmd()` を通じてアクセスします。
 * 
 * @author Tomoya Koyanagi <tomk79@gmail.com>
 */
class pxcmd{
	private $px, $bowl;
	private $pxcommands = array();

	/**
	 * コンストラクタ
	 * 
	 * @param object $px Picklesオブジェクト
	 */
	public function __construct($px){
		$this->px = $px;
		$this->pxcommands = array();

		// make instance $bowl
		require_once(__DIR__.'/bowl.php');
		$this->bowl = new bowl();

	}

	/**
	 * PXコマンド登録
	 */
	public static function regist( $cmd, $fnc ){
		if( array_key_exists($this->pxcommands, $cmd) ){
			return false;
		}
		$pxcommands[$cmd] = array(
			'cmd' => $cmd ,
			'name' => $cmd ,
			'fnc' => $fnc ,
		);

		$pxcmd = $px->get_px_command();
		if( $pxcmd[0] == $cmd ){
			$fnc();
		}
		return true;
	}

	/**
	 * get $bowl
	 */
	public function bowl(){
		return $this->bowl;
	}

	/**
	 * generate CLI header
	 */
	public function get_cli_header(){
		$pxcmd = $this->px->get_px_command();
		@header('Content-type: text/plain');
		ob_start();
		print '------------'."\n";
		print 'Pickles '.$this->px->get_version().' -> '.$pxcmd[0]."\n";
		print 'PHP '.phpversion()."\n";
		print @date('Y-m-d H:i:s')."\n";
		print '------------'."\n";
		return ob_get_clean();
	}

	/**
	 * generate CLI footer
	 */
	public function get_cli_footer(){
		ob_start();
		print '------------'."\n";
		print @date('Y-m-d H:i:s')."\n";
		print 'PX Command END;'."\n";
		print "\n";
		return ob_get_clean();
	}

	/**
	 * wrap GUI frame
	 */
	public function wrap_gui_frame( $content ){
		$this->bowl()->send($content);
		unset($content);

		// リソースをキャッシュ領域にコピー
		$path_pxcache = $this->px->fs()->get_realpath( $this->px->conf()->public_cache_dir.'/px/' );
		$realpath_pxcache = $this->px->fs()->get_realpath( $this->px->get_path_docroot().$this->px->get_path_controot().$path_pxcache );
		$this->px->fs()->mkdir( $realpath_pxcache );
		$this->px->fs()->copy_r( __DIR__.'/files/', $realpath_pxcache );

		$pxcmd = $this->px->get_px_command();
		ob_start();?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<title><?= htmlspecialchars( $this->px->conf()->name ) ?></title>
		<!-- jQuery -->
		<script src="<?php print htmlspecialchars($this->px->href($path_pxcache.'scripts/jquery-1.10.1.min.js')); ?>" type="text/javascript"></script>

		<!-- Bootstrap -->
		<link rel="stylesheet" href="<?= htmlspecialchars( $this->px->href($path_pxcache.'styles/bootstrap/css/bootstrap.min.css') ); ?>">
		<script src="<?= htmlspecialchars( $this->px->href($path_pxcache.'styles/bootstrap/js/bootstrap.min.js') ); ?>"></script>

		<!-- FESS -->
		<link rel="stylesheet" href="<?php print htmlspecialchars($this->px->href($path_pxcache.'styles/contents.css')); ?>" type="text/css" />

<?= $this->bowl()->pull('head') ?>
	</head>
	<body>
		<div class="container">
			<p>Pickles <?= htmlspecialchars( $this->px->get_version() ) ?> - <?= htmlspecialchars($pxcmd[0]) ?></p>
			<p>PHP <?= htmlspecialchars(phpversion()) ?></p>
			<p><?= htmlspecialchars(@date('Y-m-d H:i:s')) ?></p>
		</div>
		<hr />
		<div class="container">
			<div class="contents">
<?= $this->bowl()->pull() ?>
			</div>
		</div>
		<hr />
		<div class="container">
		</div>
	</body>
</html>
<?php
		return ob_get_clean();
	}

}
