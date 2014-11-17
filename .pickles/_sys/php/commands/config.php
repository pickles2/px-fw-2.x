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
	private $conf;

	/**
	 * Starting function
	 */
	public static function register( $px ){
		$px->pxcmd()->register('config', function($px){
			(new self( $px ))->kick();
			exit;
		});
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
		$this->conf = clone $this->px->conf();

		if( $this->px->req()->is_cmd() ){
			header('Content-type: text/plain;');
			print $this->px->pxcmd()->get_cli_header();
			var_dump( $this->conf );
			print $this->px->pxcmd()->get_cli_footer();
		}else{
			$html = '';
			ob_start(); ?>
<h2>basic</h2>
<table class="def" style="width:100%;">
<colgroup><col width="30%" /><col width="30%" /><col width="40%" /></colgroup>
<?php
print $this->mk_config_unit('name', 'プロジェクト名', 'string');
print $this->mk_config_unit('domain', 'ドメイン名', 'string', true);
print $this->mk_config_unit('path_controot', 'コンテンツルートのパス', 'string', true);
print $this->mk_config_unit('path_top', 'トップページのパス', 'string');
print $this->mk_config_unit('path_publish_dir', 'パブリッシュ先ディレクトリのパス', 'string');
print $this->mk_config_unit('public_cache_dir', '公開キャッシュディレクトリのパス', 'string');
print $this->mk_config_unit('contents_manifesto', 'コンテンツマニフェストのパス', 'string');
print $this->mk_config_unit('directory_index', 'ディレクトリインデックス', 'array');
print $this->mk_config_unit('commands', 'コマンド', 'object');
?>
</table>
<h2>filesystem</h2>
<table class="def" style="width:100%;">
<colgroup><col width="30%" /><col width="30%" /><col width="40%" /></colgroup>
<?php
print $this->mk_config_unit('file_default_permission', 'デフォルトのファイルパーミッション', 'string', true);
print $this->mk_config_unit('dir_default_permission', 'デフォルトのディレクトリパーミッション', 'string', true);
print $this->mk_config_unit('filesystem_encoding', 'ファイルシステムの文字セット', 'string');
print $this->mk_config_unit('output_encoding', '出力時の文字セット', 'string');
print $this->mk_config_unit('output_eol_coding', '出力時の改行コード', 'string');
?>
</table>
<h2>session</h2>
<table class="def" style="width:100%;">
<colgroup><col width="30%" /><col width="30%" /><col width="40%" /></colgroup>
<?php
print $this->mk_config_unit('session_name', 'セッション名', 'string');
print $this->mk_config_unit('session_expire', 'セッション有効期限', 'int');
print $this->mk_config_unit('allow_pxcommands', 'PXコマンドを許可', 'bool');
?>
</table>

<h2>processor</h2>
<table class="def" style="width:100%;">
<colgroup><col width="30%" /><col width="30%" /><col width="40%" /></colgroup>
<?php
print $this->mk_config_unit('paths_proc_type', 'プロセスタイプ', 'object');
print $this->mk_config_unit('funcs', 'プロセス機能', 'object');
?>
</table>

<?php if( count( get_object_vars($this->conf) ) ){ ?>
<h2>other</h2>
<div class="unit">
	<pre><?php var_dump( $this->conf ); ?></pre>
</div>
<?php } ?>

<?php
			$html .= ob_get_clean();
			print $this->px->pxcmd()->wrap_gui_frame($html);
		}

		exit;
	}

	/**
	 * コンフィグ項目1件を出力する。
	 * 
	 * @param string $key コンフィグのキー
	 * @param string $label コンフィグラベル
	 * @param string $type 値に期待される型
	 * @param bool $required 必須項目の場合 `true`、それ以外は `false`
	 * @return string コンフィグ1件分のHTMLソース(trタグ)
	 */
	private function mk_config_unit( $key,$label,$type='string',$required = false ){
		$conf_value = @$this->conf->{$key};
		unset( $this->conf->{$key} );

		$src = '';
		$src .= '	<tr>'."\n";
		$src .= '		<th style="word-break:break-all;"'.(strlen($label)?'':' colspan="2"').'>'.htmlspecialchars( $key ).'</th>'."\n";
		if( strlen($label) ){
			$src .= '		<th style="word-break:break-all;">'.htmlspecialchars( $label ).'</th>'."\n";
		}
		$src .= '		<td style="word-break:break-all;">';
		if(is_null(@$conf_value)){
			$src .= '<span style="font-style:italic; color:#aaa; background-color:#fff;">null</span>';
			if( $required ){
				$src .= '<strong style="margin-left:1em; color:#f00; background-color:#fff;">required!!</strong>';
			}
		}elseif(is_array(@$conf_value) || is_object(@$conf_value)){
			$src .= '<pre>';
			ob_start();
			var_dump($conf_value);
			$src .= ob_get_clean();
			$src .= '</pre>';
			if( $required ){
				$src .= '<strong style="margin-left:1em; color:#f00; background-color:#fff;">required!!</strong>';
			}
		}else{
			switch(strtolower($type)){
				case 'bool':
					$src .= ($conf_value?'<span style="font-style:italic; color:#03d; background-color:#fff;">true</span>':'<span style="font-style:italic; color:#03d; background-color:#fff;">false</span>');
					break;
				case 'realpath':
					$src .= $this->h( realpath( $conf_value ) ).'<br />(<q>'.$this->h( $conf_value ).'</q>)';
					break;
				case 'string':
				default:
					if( preg_match( '/^colors\./', $key ) ){
						// 色設定の場合に限り、カラーチップを手前に置く。
						$src .= '<span style="color:'.htmlspecialchars($conf_value).';">■</span>'.$this->h( $conf_value );
					}else{
						$src .= $this->h( $conf_value );
					}
					break;
			}
		}
		$src .= '</td>'."\n";
		$src .= '	</tr>'."\n";
		return $src;
	}

	/**
	 * HTML特殊文字をエスケープする。
	 * 
	 * このメソッドは改行コードを改行タグ `<br />` に変換します。
	 * 
	 * @param string $txt 文字列
	 * @return string HTML特殊文字がエスケープされた文字列
	 */
	private function h($txt){
		$txt = htmlspecialchars($txt);
		$txt = preg_replace('/\r\n|\r|\n/','<br />',$txt);
		return $txt;
	}

}
