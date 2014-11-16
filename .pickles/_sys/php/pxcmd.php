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

		@header( 'Content-type: text/html; charset="UTF-8"' );

		// リソースをキャッシュ領域にコピー
		$path_pxcache = $this->px->fs()->get_realpath( $this->px->get_path_controot().$this->px->conf()->public_cache_dir.'/px/' );
		$realpath_pxcache = $this->px->fs()->get_realpath( $this->px->get_path_docroot().$path_pxcache );
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
		<script src="<?= htmlspecialchars( $path_pxcache.'scripts/jquery-1.10.1.min.js' ); ?>" type="text/javascript"></script>

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
		<div class="pxcmd-pxfw_l"><?= $this->px->get_pickles_logo_svg() ?> Pickles (version:<?= htmlspecialchars( $this->px->get_version() ) ?>)</div>
		<div class="pxcmd-pxfw_r">
		<!--<form class="inline">
		<select onchange="window.location.href=\'?PX=\'+this.options[this.selectedIndex].value;">
		foreach( $px_command_list as $command_name_row ){
			<option value="'.t::h($command_name_row).'"'.($this->pxcommand_name[0]==$command_name_row?' selected="selected"':'').'>'.t::h($command_name_row).'</option>
		}
		if( count($plugins_list) ){
			foreach($plugins_list as $plugin_name){
				<option value="plugins.'.t::h(urlencode($plugin_name)).'"'.(implode( '.', array(@$this->pxcommand_name[0], @$this->pxcommand_name[1]) )=='plugins.'.$plugin_name?' selected="selected"':'').'>plugins.'.t::h($plugin_name).'</option>
			}
		}
		</select>
		</form>-->
		</div>
		</div><!-- /.pxcmd-pxfw -->

		<div class="pxcmd-header">
			<div class="pxcmd-project_title"><?= htmlspecialchars( @$this->px->conf()->name ) ?></div>
		</div><!-- /.pxcmd-header -->

		<div class="pxcmd-middle">
<?php
		$command_title = $pxcmd[0].(@strlen($pxcmd[1])?'.'.$pxcmd[1]:'');
		$page_title = null;
?>
		<div class="pxcmd-title">
		<?php if(strlen($page_title)){ ?>
			<p class="pxcmd-category_title"><a href="?PX=<?= htmlspecialchars( $command_title ) ?>" style="color:inherit; text-decoration:none;"><?= htmlspecialchars($command_title) ?></a></p>
			<h1><?= htmlspecialchars( $page_title ) ?></h1>
		<?php }else{ ?>
			<h1><a href="?PX=<?= htmlspecialchars( $command_title ) ?>" style="color:inherit; text-decoration:none;"><?= htmlspecialchars( $command_title ) ?></a></h1>
		<?php } ?>
		</div><!-- /.pxcmd-title -->
		<div id="content" class="contents">
<?= $this->bowl()->pull() ?>
		</div>
		</div><!-- /.pxcmd-middle -->

		<div class="pxcmd-footer">
		<!-- <dl class="clearfix">
		<dt>standard:</dt>
		foreach( $px_command_list as $command_name_row ){
			<dd><a href="?PX='.t::h($command_name_row).'"'.($this->pxcommand_name[0]==$command_name_row?' class="current"':'').'>'.t::h($command_name_row).'</a></dd>
		}
		<dt>standard (unpaged functions):</dt>
		foreach( $px_command_unpaged_list as $command_name_row ){
			<dd><a href="?PX='.t::h($command_name_row).'"'.($this->pxcommand_name[0]==$command_name_row?' class="current"':'').' target="_blank">'.t::h($command_name_row).'</a></dd>
		}
		if( count($plugins_list) ){
			<dt>plugins:</dt>
			foreach($plugins_list as $plugin_name){
				<dd><a href="?PX=plugins.'.t::h(urlencode($plugin_name)).'"'.(implode( '.', array(@$this->pxcommand_name[0], @$this->pxcommand_name[1]) )=='plugins.'.$plugin_name?' class="current"':'').'>'.t::h($plugin_name).'</a></dd>
			}
		}
		</dl>-->

		<p><a href="?">PX Commands を終了する</a></p>
		<p>PHP <?= htmlspecialchars(phpversion()) ?></p>
		<p><?= htmlspecialchars(@date('Y-m-d H:i:s')) ?></p>
		<p class="pxcmd-credits"><a href="http://pickles.pxt.jp/" target="_blank">Pickles Framework</a> (C)Tomoya Koyanagi.</p>
		</div><!-- /.footer -->
		</div>

	</body>
</html>
<?php
		return ob_get_clean();
	}

}
