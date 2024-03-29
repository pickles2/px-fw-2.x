<?php
/**
 * PX Commands "config"
 */
namespace picklesFramework2\commands;

/**
 * PX Commands "config"
 *
 * <dl>
 * 	<dt>PX=config</dt>
 * 		<dd>Pickles Framework 2 の設定情報を表示します。</dd>
 * </dl>
 */
class config{

	/**
	 * Picklesオブジェクト
	 */
	private $px;

	/**
	 * 設定オブジェクト
	 */
	private $conf;

	/**
	 * Starting function
	 * @param object $px Picklesオブジェクト
	 * @param object $options プラグイン設定
	 */
	public static function register( $px = null, $options = null ){

		if( count(func_get_args()) <= 1 ){
			return __CLASS__.'::'.__FUNCTION__.'('.( is_array($px) ? json_encode($px) : '' ).')';
		}

		$px->pxcmd()->register('config', function($px){
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
<table class="def" style="width:100%; table-layout: fixed;">
<colgroup><col width="30%" /><col width="30%" /><col width="40%" /></colgroup>
<?php
print $this->mk_config_unit('name', 'プロジェクト名', 'string');
print $this->mk_config_unit('copyright', 'コピーライト', 'string');
print $this->mk_config_unit('domain', 'ドメイン名', 'string', true);
print $this->mk_config_unit('path_controot', 'コンテンツルートのパス', 'string', true);
print $this->mk_config_unit('path_top', 'トップページのパス', 'string');
print $this->mk_config_unit('path_publish_dir', 'パブリッシュ先ディレクトリのパス', 'string');
print $this->mk_config_unit('public_cache_dir', '公開キャッシュディレクトリのパス', 'string');
print $this->mk_config_unit('path_files', 'リソースディレクトリのパス', 'string');
print $this->mk_config_unit('contents_manifesto', 'コンテンツマニフェストのパス', 'string');
print $this->mk_config_unit('directory_index', 'ディレクトリインデックス', 'array');
print $this->mk_config_unit('commands', 'コマンド', 'hash');
print $this->mk_config_unit('path_phpini', 'php.ini のパス', 'string');
print $this->mk_config_unit('allow_pxcommands', 'PXコマンドを許可', 'bool');
print $this->mk_config_unit('default_timezone', 'タイムゾーン設定', 'string');
?>
</table>
<h2>filesystem</h2>
<table class="def" style="width:100%; table-layout: fixed;">
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
<table class="def" style="width:100%; table-layout: fixed;">
<colgroup><col width="30%" /><col width="30%" /><col width="40%" /></colgroup>
<?php
print $this->mk_config_unit('session_name', 'セッション名', 'string');
print $this->mk_config_unit('session_expire', 'セッション有効期限', 'int');
?>
</table>

<h2>processor</h2>
<h3>paths_enable_sitemap (サイトマップのロードを有効にするパス)</h3>
<?php
	$conf_value = $this->conf->{'paths_enable_sitemap'} ?? null;
	unset( $this->conf->{'paths_enable_sitemap'} );
?>
<table class="def" style="width:100%; table-layout: fixed;">
<colgroup><col width="100%" /></colgroup>
<thead>
<tr>
<th>path</th>
</tr>
</thead>
<tfoot>
<tr>
<th>path</th>
</tr>
</tfoot>
<?php
foreach( $conf_value as $key=>$val ){
	print '<tr>';
	print '<td>'.htmlspecialchars($val ?? "").'</td>';
	print '</tr>';
}
?>
</table>

<h3>paths_proc_type (プロセスタイプ)</h3>
<?php
	$conf_value = $this->conf->{'paths_proc_type'} ?? null;
	unset( $this->conf->{'paths_proc_type'} );
?>
<table class="def" style="width:100%; table-layout: fixed;">
<colgroup><col width="70%" /><col width="30%" /></colgroup>
<thead>
<tr>
<th>path</th>
<th>proc</th>
</tr>
</thead>
<tfoot>
<tr>
<th>path</th>
<th>proc</th>
</tr>
</tfoot>
<?php
foreach( $conf_value as $key=>$val ){
	print '<tr>';
	print '<td>'.htmlspecialchars($key ?? "").'</td>';
	print '<td>'.htmlspecialchars($val ?? "").'</td>';
	print '</tr>';
}
?>
</table>

<h3>funcs (プロセス機能)</h3>
<table class="def" style="width:100%; table-layout: fixed;">
<colgroup><col width="10%" /><col width="15%" /><col width="75%" /></colgroup>
<?php
print $this->mk_config_unit('funcs', 'プロセス機能', 'hash');
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
	private function mk_config_unit( $key, $label, $type='string', $required = false ){
		$conf_value = $this->conf->{$key} ?? null;
		unset( $this->conf->{$key} );
		$rowspan = 1;
		if( $type == 'array' && is_array($conf_value) && count($conf_value) >= 1 ){
			$rowspan = count($conf_value);
		}

		$src = '';
		$src .= '	<tr>'."\n";
		$src .= '		<th style="word-break:break-all;" rowspan="'.intval($rowspan).'"'.(strlen($label ?? "")?'':' colspan="2"').'>'.htmlspecialchars( $key ?? "" ).'</th>'."\n";
		if( strlen($label ?? "") ){
			$src .= '		<th style="word-break:break-all;" rowspan="'.intval($rowspan).'">';
			$src .= htmlspecialchars( $label ?? "" );
			$src .= '</th>'."\n";
		}
		if( $type == 'array' && is_array($conf_value) ){
			if( !count($conf_value) ){
				$src .= '		<td style="word-break:break-all;">'.'---'.'</td>'."\n";
			}else{
				$i = 0;
				foreach( $conf_value as $conf_value_row ){
					if( $i > 0 ){
						$src .= '	</tr>'."\n";
						$src .= '	<tr>'."\n";
					}
					$src .= '		<td style="word-break:break-all;">'.htmlspecialchars($conf_value_row ?? "").'</td>'."\n";
					$i ++;
				}
			}
		}elseif( $type == 'hash' && (is_array($conf_value) || is_object($conf_value)) ){
			$src .= '		<td style="word-break:break-all;">';
			$conf_value_test = $conf_value;
			if( is_object($conf_value) ){
				$conf_value_test = get_object_vars($conf_value);
			}
			$conf_value_test = $conf_value;
			if( !count($conf_value_test) ){
				$src .= '---';
			}else{
				$src .= '<dl>';
				foreach( $conf_value as $ary_key=>$ary_val ){
					$src .= '<dt>'.htmlspecialchars($ary_key ?? "").'</dt>';
					$src .= '<dd>';
					if( is_string($ary_val) || is_int($ary_val) || is_float($ary_val) ){
						$src .= htmlspecialchars($ary_val ?? "");
					}else{
						ob_start();
						var_dump($ary_val);
						$src .= '<pre>'.htmlspecialchars( ob_get_clean() ).'</pre>';
					}
					$src .= '</dd>';
				}
				$src .= '</dl>';
			}
			$src .= '</td>'."\n";
		}elseif(is_null($conf_value)){
			$src .= '		<td style="word-break:break-all;">';
			$src .= '<span style="font-style:italic; color:#aaa; background-color:#fff;">null</span>';
			if( $required ){
				$src .= '<strong style="margin-left:1em; color:#f00; background-color:#fff;">required!!</strong>';
			}
			$src .= '</td>'."\n";
		}elseif(is_array($conf_value) || is_object($conf_value)){
			$src .= '		<td style="word-break:break-all;">';
			$src .= '<pre>';
			ob_start();
			var_dump($conf_value);
			$src .= ob_get_clean();
			$src .= '</pre>';
			if( $required ){
				$src .= '<strong style="margin-left:1em; color:#f00; background-color:#fff;">required!!</strong>';
			}
			$src .= '</td>'."\n";
		}else{
			$src .= '		<td style="word-break:break-all;">';
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
						$src .= '<span style="color:'.htmlspecialchars($conf_value ?? "").';">■</span>'.$this->h( $conf_value );
					}else{
						$src .= $this->h( $conf_value );
					}
					break;
			}
			$src .= '</td>'."\n";
		}
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
		$txt = htmlspecialchars($txt ?? "");
		$txt = preg_replace('/\r\n|\r|\n/','<br />',$txt);
		return $txt;
	}

}
