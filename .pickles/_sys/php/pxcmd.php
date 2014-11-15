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
	private $px;
	private $pxcommands = array();

	/**
	 * コンストラクタ
	 * 
	 * @param object $px Picklesオブジェクト
	 */
	public function __construct($px){
		$this->px = $px;
		$this->pxcommands = array();
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
		$pxcmd = $this->px->get_px_command();
		ob_start();?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8" />
	<title><?= htmlspecialchars( $this->px->conf()->name ) ?></title>
</head>
<body>
<?php
		print htmlspecialchars( 'Pickles '.$this->px->get_version().' -> '.$pxcmd[0] ).'<br />'."\n";
		print htmlspecialchars( 'PHP '.phpversion() ).'<br />'."\n";
		print @date('Y-m-d H:i:s').'<br />'."\n";
?>
<hr />
<div class="contents">
<?= $content; ?>
</div>
<hr />
</body>
</html>
<?php
		return ob_get_clean();
	}

}
