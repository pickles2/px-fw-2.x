<?php
/**
 * PX Commands "config"
 */
namespace picklesFramework2\commands;

/**
 * PX Commands "config"
 */
class config{

	private $px;

	/**
	 * Starting function
	 */
	public static function regist( $px ){
		$pxcmd = $px->get_px_command();
		if( $pxcmd[0] != 'config' ){
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
	}


	/**
	 * kick
	 */
	private function kick(){
		$conf = clone $this->px->conf();

		if( $this->px->req()->is_cmd() ){
			header('Content-type: text/plain;');
			print $this->px->pxcmd()->get_cli_header();
			var_dump( $conf );
			print $this->px->pxcmd()->get_cli_footer();
		}else{
			$html = '';
			ob_start(); ?>
<div class="unit">
	<pre><?php var_dump( $conf ); ?></pre>
</div>
<?php
			$html .= ob_get_clean();
			print $this->px->pxcmd()->wrap_gui_frame($html);
		}

		exit;
	}

}
