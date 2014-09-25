<?php
/**
 * pickles2
 */
namespace pickles;

/**
 * pickles2 core class
 * 
 * @author Tomoya Koyanagi <tomk79@gmail.com>
 */
class pickles{
	private $path_homedir;
	private $path_content;
	private $conf;
	private $fs, $req, $site;
	private $directory_index;
	private $contents_cabinet = array(
		''=>'',    //  メインコンテンツ
		'head'=>'' //  ヘッドセクションに追記
	);
	/**
	 * 関連ファイルのURL情報
	 */
	private $relatedlinks = array();

	/**
	 * PxFWのバージョン情報を取得する。
	 * 
	 * <pre> [バージョン番号のルール]
	 *    基本
	 *      メジャーバージョン番号.マイナーバージョン番号.リリース番号
	 *        例：1.0.0
	 *        例：1.8.9
	 *        例：12.19.129
	 *      - 大規模な仕様の変更や追加を伴う場合にはメジャーバージョンを上げる。
	 *      - 小規模な仕様の変更や追加の場合は、マイナーバージョンを上げる。
	 *      - バグ修正、ドキュメント、コメント修正等の小さな変更は、リリース番号を上げる。
	 *    開発中プレビュー版
	 *      基本バージョンの後ろに、a(=α版)またはb(=β版)を付加し、その連番を記載する。
	 *        例：1.0.0a1 ←最初のα版
	 *        例：1.0.0b12 ←12回目のβ版
	 *      開発中およびリリースバージョンの順序は次の通り
	 *        1.0.0a1 -> 1.0.0a2 -> 1.0.0b1 ->1.0.0b2 -> 1.0.0 ->1.0.1a1 ...
	 *    ナイトリービルド
	 *      ビルドの手順はないので正確には "ビルド" ではないが、
	 *      バージョン番号が振られていない、開発途中のリビジョンを
	 *      ナイトリービルドと呼ぶ。
	 *      ナイトリービルドの場合、バージョン情報は、
	 *      ひとつ前のバージョン文字列の末尾に、'-nb' を付加する。
	 *        例：1.0.0b12-nb (=1.0.0b12リリース後のナイトリービルド)
	 *      普段の開発においてコミットする場合、
	 *      必ずこの get_version() がこの仕様になっていることを確認すること。
	 * </pre>
	 * 
	 * @return string バージョン番号を示す文字列
	 */
	public function get_version(){
		return '2.0.0-nb';
	}

	/**
	 * constructor
	 * @param string $path_homedir Pickles のホームディレクトリ
	 */
	public function __construct( $path_homedir ){
		// initialize PHP
		if( !extension_loaded( 'mbstring' ) ){
			trigger_error('mbstring not loaded.');
		}
		if( is_callable('mb_internal_encoding') ){
			mb_internal_encoding('UTF-8');
			@ini_set( 'mbstring.internal_encoding' , 'UTF-8' );
			@ini_set( 'mbstring.http_input' , 'UTF-8' );
			@ini_set( 'mbstring.http_output' , 'UTF-8' );
		}
		@ini_set( 'default_charset' , 'UTF-8' );
		if( is_callable('mb_detect_order') ){
			@ini_set( 'mbstring.detect_order' , 'UTF-8,SJIS-win,eucJP-win,SJIS,EUC-JP,JIS,ASCII' );
			mb_detect_order( 'UTF-8,SJIS-win,eucJP-win,SJIS,EUC-JP,JIS,ASCII' );
		}
		header_remove('X-Powered-By');
		@header('HTTP/1.1 200 OK');

		// load Config
		$this->path_homedir = $path_homedir;
		if( is_file($this->path_homedir.DIRECTORY_SEPARATOR.'config.json') ){
			$this->conf = json_decode( file_get_contents( $this->path_homedir.DIRECTORY_SEPARATOR.'config.json' ) );
		}elseif( is_file($this->path_homedir.DIRECTORY_SEPARATOR.'config.php') ){
			$this->conf = include( $this->path_homedir.DIRECTORY_SEPARATOR.'config.php' );
		}

		// make instance $fs
		$conf = new \stdClass;
		$conf->file_default_permission = $this->conf->file_default_permission;
		$conf->dir_default_permission  = $this->conf->dir_default_permission;
		$conf->filesystem_encoding     = $this->conf->filesystem_encoding;
		$this->fs = new \tomk79\filesystem( $conf );
		$this->path_homedir = $this->fs->get_realpath($this->path_homedir).'/';

		// make instance $req
		$conf = new \stdClass;
		$conf->session_name   = $this->conf->session_name;
		$conf->session_expire = $this->conf->session_expire;
		$conf->directory_index_primary = $this->get_directory_index_primary();
		$this->req = new \tomk79\request( $conf );

		if( @!empty( $this->conf->funcs->starting ) ){
			// Starting functions
			if( is_string($this->conf->funcs->starting) ){
				$fnc_name = $this->conf->funcs->starting;
				$fnc_name = preg_replace( '/^\\\\*/', '\\', $fnc_name );
				call_user_func( $fnc_name, $this );
			}elseif( is_array($this->conf->funcs->starting) ){
				foreach( $this->conf->funcs->starting as $fnc_name ){
					if( is_string($fnc_name) ){
						$fnc_name = preg_replace( '/^\\\\*/', '\\', $fnc_name );
					}
					call_user_func( $fnc_name, $this );
				}
			}
			unset($fnc_name);
		}

		// make instance $site
		require_once(__DIR__.'/site.php');
		$this->site = new site($this);


		// execute content
		$this->path_content = $this->site()->get_page_info( $this->req()->get_request_file_path(), 'content' );
		if( is_null( $this->path_content ) ){
			$this->path_content = $this->req()->get_request_file_path();
		}
		foreach( array_keys( get_object_vars( $this->conf->funcs->extensions ) ) as $ext ){
			if( $this->fs()->is_file( './'.$this->path_content.'.'.$ext ) ){
				$this->path_content = $this->path_content.'.'.$ext;
				break;
			}
		}
		$this->output_content_type(); // デフォルトの Content-type を出力
		if( $this->is_ignore_path( $this->req()->get_request_file_path() ) ){
			@header('HTTP/1.1 403 Forbidden');
			$this->send_content('<p>ignore path</p>');
		}else{
			self::exec_content( $this );
		}

		$ext = strtolower( pathinfo( $this->path_content , PATHINFO_EXTENSION ) );
		if( @!empty( $this->conf->funcs->extensions->{$ext} ) ){
			// extension functions
			foreach( $this->contents_cabinet as $contents_key=>$src ){
				if( is_string($this->conf->funcs->extensions->{$ext}) ){
					$fnc_name = $this->conf->funcs->extensions->{$ext};
					$fnc_name = preg_replace( '/^\\\\*/', '\\', $fnc_name );
					$src = call_user_func( $fnc_name, $this, $src, $contents_key );
				}elseif( is_array($this->conf->funcs->extensions->{$ext}) ){
					foreach( $this->conf->funcs->extensions->{$ext} as $fnc_name ){
						if( is_string($fnc_name) ){
							$fnc_name = preg_replace( '/^\\\\*/', '\\', $fnc_name );
						}
						$src = call_user_func( $fnc_name, $this, $src, $contents_key );
					}
				}
				$this->contents_cabinet[$contents_key] = $src;
			}
		}
		unset($src, $fnc_name);

		// execute theme
		$fnc_name = preg_replace( '/^\\\\*/', '\\', $this->conf->funcs->theme );
		$src = call_user_func( $fnc_name, $this );

		$src = $this->output_filter( $src );

		if( count($this->relatedlinks) ){
			@header('X-PXFW-RELATEDLINK: '.implode(',',$this->relatedlinks).'');
		}

		print $src;

		exit;
	}

	/**
	 * get $fs
	 */
	public function fs(){
		return $this->fs;
	}

	/**
	 * get $req
	 */
	public function req(){
		return $this->req;
	}

	/**
	 * get $site
	 */
	public function site(){
		return $this->site;
	}

	/**
	 * get $conf
	 */
	public function conf(){
		return $this->conf;
	}

	/**
	 * get home directory path
	 */
	public function get_path_homedir(){
		return $this->path_homedir;
	}

	public function get_path_content(){
		return $this->path_content;
	}

	/**
	 * directory_index の一覧を得る。
	 * 
	 * @return array ディレクトリインデックスの一覧
	 */
	public function get_directory_index(){
		if( count($this->directory_index) ){
			return $this->directory_index;
		}
		$tmp_di = $this->conf->directory_index;
		$this->directory_index = array();
		foreach( $tmp_di as $file_name ){
			$file_name = trim($file_name);
			if( !strlen($file_name) ){ continue; }
			array_push( $this->directory_index, $file_name );
		}
		if( !count( $this->directory_index ) ){
			array_push( $this->directory_index, 'index.html' );
		}
		return $this->directory_index;
	}//get_directory_index()

	/**
	 * directory_index のいずれかにマッチするためのpregパターン式を得る。
	 * 
	 * @param string $delimiter pregパターンのデリミタ。省略時は `/` (`preg_quote()` の実装に従う)。
	 * @return string pregパターン
	 */
	public function get_directory_index_preg_pattern( $delimiter = null ){
		$directory_index = $this->get_directory_index();
		foreach( $directory_index as $key=>$row ){
			$directory_index[$key] = preg_quote($row, $delimiter);
		}
		$rtn = '(?:'.implode( '|', $directory_index ).')';
		return $rtn;
	}//get_directory_index_preg_pattern()

	/**
	 * 最も優先されるインデックスファイル名を得る。
	 * 
	 * @return string 最も優先されるインデックスファイル名
	 */
	public function get_directory_index_primary(){
		$directory_index = $this->get_directory_index();
		return $directory_index[0];
	}//get_directory_index_primary()

	/**
	 * 除外ファイルか調べる。
	 *
	 * @param string $path パス
	 * @return bool 除外ファイルの場合 `true`、それ以外の場合に `false` を返します。
	 */
	private function is_ignore_path( $path ){
		static $rtn = array();
		$path = $this->fs()->get_realpath( '/'.$path );
		if( is_dir('./'.$path) ){
			$path .= '/';
		}

		if( @is_bool($rtn[$path]) ){
			return $rtn[$path];
		}

		foreach( $this->conf->paths_ignore as $row ){
			if(!is_string($row)){continue;}
			$preg_pattern = preg_quote($this->fs()->get_realpath($row),'/');
			if( preg_match('/\*/',$preg_pattern) ){
				// ワイルドカードが使用されている場合
				$preg_pattern = preg_quote($row,'/');
				$preg_pattern = preg_replace('/'.preg_quote('\*','/').'/','(?:.*?)',$preg_pattern);//ワイルドカードをパターンに反映
				$preg_pattern = $preg_pattern.'$';//前方・後方一致
			}elseif(is_dir($row)){
				$preg_pattern = preg_quote($this->fs()->get_realpath($row).'/','/');
			}elseif(is_file($row)){
				$preg_pattern = preg_quote($this->fs()->get_realpath($row),'/');
			}
			if( preg_match( '/^'.$preg_pattern.'/s' , $path ) ){
				$rtn[$path] = true;
				return true;
			}
		}
		$rtn[$path] = false;
		return false;
	}//is_ignore_path();

	/**
	 * output content-type
	 */
	private function output_content_type(){
		$extension = strtolower( pathinfo( $this->req()->get_request_file_path() , PATHINFO_EXTENSION ) );
		$output_encoding = @$this->conf->output_encoding;
		if( !strlen( $output_encoding ) ){
			$output_encoding = 'utf-8';
		}
		switch( strtolower( $extension ) ){
			case 'css':
				@header('Content-type: text/css; charset='.$output_encoding);//デフォルトのヘッダー
				break;
			case 'js':
				@header('Content-type: text/javascript; charset='.$output_encoding);//デフォルトのヘッダー
				break;
			case 'html':
			case 'htm':
				@header('Content-type: text/html; charset='.$output_encoding);//デフォルトのヘッダー
				break;
			case 'txt':
			case 'text':
				@header('Content-type: text/plain; charset='.$output_encoding);//デフォルトのヘッダー
				break;
		}
		return ;
	}

	/**
	 * output filter
	 */
	private function output_filter( $src ){
		if( @strlen( $this->conf->output_encoding ) ){
			$output_encoding = $this->conf->output_encoding;
			if( !strlen($output_encoding) ){ $output_encoding = 'UTF-8'; }
			$extension = strtolower( pathinfo( $this->get_path_content() , PATHINFO_EXTENSION ) );
			switch( strtolower( $extension ) ){
				case 'css':
					//出力ソースの文字コード変換
					$src = preg_replace('/\@charset\s+"[a-zA-Z0-9\_\-\.]+"\;/si','@charset "'.htmlspecialchars($output_encoding).'";',$src);
					$src = $this->convert_encoding( $src, $output_encoding );
					break;
				case 'js':
					//出力ソースの文字コード変換
					$src = $this->convert_encoding( $src, $output_encoding );
					break;
				case 'html':
				case 'htm':
					//出力ソースの文字コード変換(HTML)
					$src = preg_replace('/(<meta\s+charset\=")[a-zA-Z0-9\_\-\.]+("\s*\/?'.'>)/si','$1'.htmlspecialchars($output_encoding).'$2',$src);
					$src = preg_replace('/(<meta\s+http\-equiv\="Content-Type"\s+content\="[a-zA-Z0-9\_\-\+]+\/[a-zA-Z0-9\_\-\+]+\;\s*charset\=)[a-zA-Z0-9\_\-\.]+("\s*\/?'.'>)/si','$1'.htmlspecialchars($output_encoding).'$2',$src);
					$src = $this->convert_encoding( $src, $output_encoding );
					break;
				default:
					$src = $this->convert_encoding( $src, $output_encoding );
					break;
			}

		}
		if( @strlen( $this->conf->output_eol_coding ) ){
			//出力ソースの改行コード変換
			$eol_code = "\r\n";
			switch( strtolower( $this->conf->output_eol_coding ) ){
				case 'cr':     $eol_code = "\r"; break;
				case 'lf':     $eol_code = "\n"; break;
				case 'crlf':
				default:       $eol_code = "\r\n"; break;
			}
			$src = preg_replace('/\r\n|\r|\n/si',$eol_code,$src);
		}

		return $src;
	}

	/**
	 * コンテンツキャビネットにコンテンツを送る。
	 * 
	 * ソースコードを$themeオブジェクトに預けます。
	 * このメソッドから預けられたコードは、同じ `$content_name` 値 をキーにして、`$theme->pull_content()` から引き出すことができます。
	 * 
	 * この機能は、コンテンツからテーマへコンテンツを渡すために使用されます。
	 * 
	 * 同じ名前(`$content_name`値)で複数回ソースを送った場合、後方に追記されます。
	 * 
	 * @param string $src 送るHTMLソース
	 * @param string $content_name キャビネットの格納名。
	 * $theme->pull_content() から取り出す際に使用する名称です。
	 * 任意の名称が利用できます。PxFWの標準状態では、無名(空白文字列) = メインコンテンツ、'head' = ヘッダー内コンテンツ の2種類が定義されています。
	 * 
	 * @return bool 成功時 true、失敗時 false
	 */
	public function send_content( $src , $content_name = '' ){
		if( !strlen($content_name) ){ $content_name = ''; }
		if( !is_string($content_name) ){ return false; }
		@$this->contents_cabinet[$content_name] .= $src;
		return true;
	}

	/**
	 * コンテンツキャビネットのコンテンツを置き換える。
	 * 
	 * ソースコードを$themeオブジェクトに預けます。
	 * `$theme->send_content()` と同じですが、複数回送信した場合に、このメソッドは追記ではなく上書きする点が異なります。
	 * 
	 * @param string $src 送るHTMLソース
	 * @param string $content_name キャビネットの格納名。
	 * $theme->pull_content() から取り出す際に使用する名称です。
	 * 任意の名称が利用できます。PxFWの標準状態では、無名(空白文字列) = メインコンテンツ、'head' = ヘッダー内コンテンツ の2種類が定義されています。
	 * 
	 * @return bool 成功時 true、失敗時 false
	 */
	public function replace_content( $src , $content_name = '' ){
		if( !strlen($content_name) ){ $content_name = ''; }
		if( !is_string($content_name) ){ return false; }
		@$this->contents_cabinet[$content_name] = $src;
		return true;
	}

	/**
	 * コンテンツキャビネットからコンテンツを引き出す
	 * @param string $content_name キャビネット上のコンテンツ名
	 * @param bool $do_finalize ファイナライズ処理を有効にするか(default: true)
	 * @return mixed 成功時、キャビネットから得られたHTMLソースを返す。失敗時、false
	 */
	public function pull_content( $content_name = '' ){
		if( !strlen($content_name) ){ $content_name = ''; }
		if( !is_string($content_name) ){ return false; }

		$content = $this->contents_cabinet[$content_name];

		return $content;
	}

	/**
	 * 拡張ヘッダ X-PXFW-RELATEDLINK にリンクを追加する。
	 * 
	 * 拡張ヘッダ `X-PXFW-RELATEDLINK` は、サイトマップや物理ディレクトリから発見できないファイルを、PxFWのパブリッシュツールに知らせます。
	 * 
	 * 通常、PxFWのパブリッシュツールは 動的に生成されたページなどを知ることができず、パブリッシュされません。このメソッドを通じて、明示的に知らせる必要があります。
	 * 
	 * @param string $path リンクのパス
	 * @return bool 正常時 `true`、失敗した場合 `false`
	 */
	public function add_relatedlink( $path ){
		$path = trim($path);
		if(!strlen($path)){
			return false;
		}
		array_push( $this->relatedlinks , $path );
		return true;
	}

	/**
	 * getting PX Command
	 */
	public function get_px_command(){
		$rtn = $this->req()->get_param('PX');
		if( is_string($rtn) ){
			$rtn = explode('.', $rtn);
		}
		return $rtn;
	}

	/**
	 * execute content
	 * @return bool true
	 */
	private static function exec_content( $px ){
		if( !$px->fs()->is_file( './'.$px->get_path_content() ) ){
			@header('HTTP/1.1 404 NotFound');
			$px->send_content('<p>404 - File not found.</p>');
			return true;
		}
		ob_start();
		include( './'.$px->get_path_content() );
		$src = ob_get_clean();
		$px->send_content($src);
		return true;
	}

	/**
	 * リンク先のパスを生成する。
	 * 
	 * 引数には、リンク先のパス、またはページIDを受け取り、
	 * 完成されたリンク先情報(aタグのhref属性にそのまま適用できる状態)にして返します。
	 * 
	 * Pickles Framework がドキュメントルート直下にインストールされていない場合、インストールパスを追加した値を返します。
	 * 
	 * `http://` などスキーマから始まる情報を受け取ることもできます。
	 * 
	 * 実装例：
	 * <pre>&lt;?php
	 * // パスを指定する例
	 * print '&lt;a href=&quot;'.htmlspecialchars( $px-&gt;theme()-&gt;href('/aaa/bbb.html') ).'&quot;&gt;リンク&lt;/a&gt;';
	 * 
	 * // ページIDを指定する例
	 * print '&lt;a href=&quot;'.htmlspecialchars( $px-&gt;theme()-&gt;href('A-00') ).'&quot;&gt;リンク&lt;/a&gt;';
	 * ?&gt;</pre>
	 * 
	 * インストールパスがドキュメントルートではない場合の例:
	 * <pre>&lt;?php
	 * // インストールパスが &quot;/installpath/&quot; の場合
	 * print '&lt;a href=&quot;'.htmlspecialchars( $px-&gt;theme()-&gt;href('/aaa/bbb.html') ).'&quot;&gt;リンク&lt;/a&gt;';
	 *     // &quot;/installpath/aaa/bbb.html&quot; が返されます。
	 * ?&gt;</pre>
	 * 
	 * @param string $linkto リンク先の パス または ページID
	 * 
	 * @return string href属性値
	 */
	public function href( $linkto ){
		$parsed_url = parse_url($linkto);
		$tmp_page_info_by_id = $this->site()->get_page_info_by_id($linkto);
		if( !$tmp_page_info_by_id ){ $tmp_page_info_by_id = $this->site()->get_page_info_by_id(@$parsed_url['path']); }
		$path = $linkto;
		$path_type = $this->site()->get_path_type( $linkto );
		if( @$tmp_page_info_by_id['id'] == $linkto || @$tmp_page_info_by_id['id'] == @$parsed_url['path'] ){
			$path = @$tmp_page_info_by_id['path'];
			$path_type = $this->site()->get_path_type( $path );
		}
		unset($tmp_page_info_by_id);

		if( preg_match( '/^alias[0-9]*\:(.+)/' , $path , $tmp_matched ) ){
			//  エイリアスを解決
			$path = $tmp_matched[1];
		}elseif( $path_type == 'dynamic' ){
			//  ダイナミックパスをバインド
			// $sitemap_dynamic_path = $this->site()->get_dynamic_path_info( $linkto );
			// $tmp_path = $sitemap_dynamic_path['path_original'];
			$tmp_path = $path;
			$path = '';
			while( 1 ){
				if( !preg_match( '/^(.*?)\{(\$|\*)([a-zA-Z0-9\_\-]*)\}(.*)$/s' , $tmp_path , $tmp_matched ) ){
					$path .= $tmp_path;
					break;
				}
				$path .= $tmp_matched[1];
				if(!strlen($tmp_matched[3])){
					//無名のパラメータはバインドしない。
				}elseif( !is_null( $this->req()->get_path_param($tmp_matched[3]) ) ){
					$path .= $this->req()->get_path_param($tmp_matched[3]);
				}else{
					$path .= $tmp_matched[3];
				}
				$tmp_path = $tmp_matched[4];
				continue;
			}
			unset($tmp_path , $tmp_matched);
		}

		switch( $this->site()->get_path_type( $path ) ){
			case 'full_url':
			case 'javascript':
			case 'anchor':
				break;
			default:
				// index.htmlを省略
				$path = preg_replace('/\/'.$this->get_directory_index_preg_pattern().'((?:\?|\#).*)?$/si','/$1',$path);
				break;
		}

		if( preg_match( '/^\//' , $path ) ){
			//  スラッシュから始まる絶対パスの場合、
			//  インストールパスを起点としたパスに書き変えて返す。
			$path = preg_replace( '/^\/+/' , '' , $path );
			$path = $this->get_path_docroot().$path;
		}

		// パラメータを、引数の生の状態に戻す。
		$parsed_url_fin = parse_url($path);
		$path = $parsed_url_fin['path'];
		$path .= (strlen(@$parsed_url['query'])?'?'.@$parsed_url['query']:(strlen(@$parsed_url_fin['query'])?'?'.@$parsed_url_fin['query']:''));
		$path .= (strlen(@$parsed_url['fragment'])?'#'.@$parsed_url['fragment']:(strlen(@$parsed_url_fin['fragment'])?'?'.@$parsed_url_fin['fragment']:''));

		return $path;
	}//href()

	/**
	 * リンクタグ(aタグ)を生成する。
	 * 
	 * リンク先の パス または ページID を受け取り、aタグを生成して返します。リンク先は、`href()` メソッドを通して調整されます。
	 * 
	 * このメソッドは、受け取ったパス(またはページID)をもとに、サイトマップからページ情報を取得し、aタグを自動補完します。
	 * 例えば、パンくず情報をもとに `class="current"` を付加したり、ページタイトルをラベルとして採用します。
	 * 
	 * この動作は、引数 `$options` に値を指定して変更することができます。
	 * 
	 * 実装例:
	 * <pre>&lt;?php
	 * // ページ /aaa/bbb.html へのリンクを生成
	 * print $px-&gt;theme()-&gt;mk_link('/aaa/bbb.html');
	 * 
	 * // ページ /aaa/bbb.html へのリンクをオプション付きで生成
	 * print $px-&gt;theme()-&gt;mk_link('/aaa/bbb.html', array(
	 *     'label'=>'<span>リンクラベル</span>', //リンクラベルを文字列で指定
	 *     'class'=>'class_a class_b', //CSSのクラス名を指定
	 *     'no_escape'=>true, //エスケープ処理をキャンセル
	 *     'current'=>true //カレント表示にする
	 * ));
	 * ?&gt;</pre>
	 * 
	 * 第2引数に文字列または関数としてリンクラベルを渡す方法もあります。
	 * この場合、その他のオプションは第3引数に渡すことができます。
	 * 
	 * 実装例:
	 * <pre>&lt;?php
	 * // ページ /aaa/bbb.html へのリンクをオプション付きで生成
	 * print $px-&gt;theme()-&gt;mk_link('/aaa/bbb.html',
	 *   '<span>リンクラベル</span>' , //リンクラベルを文字列で指定
	 *   array(
	 *     'class'=>'class_a class_b',
	 *     'no_escape'=>true,
	 *     'current'=>true
	 *   )
	 * );
	 * ?&gt;</pre>
	 * 
	 * PxFW 1.0.4 以降、リンクのラベルはコールバック関数でも指定できます。
	 * コールバック関数には、リンク先のページ情報を格納した連想配列が引数として渡されます。
	 * 
	 * 実装例:
	 * <pre>&lt;?php
	 * //リンクラベルをコールバック関数で指定
	 * print $px-&gt;theme()-&gt;mk_link(
	 *   '/aaa/bbb.html',
	 *   function($page_info){return htmlspecialchars($page_info['title_label']);}
	 * );
	 * ?&gt;</pre>
	 * 
	 * @param string $linkto リンク先のパス。PxFWのインストールパスを基点にした絶対パスで指定。
	 * @param array $options options: [as string] Link label, [as function] callback function, [as array] Any options.
	 * <dl>
	 *   <dt>mixed $options['label']</dt>
	 *     <dd>リンクのラベルを変更します。文字列か、またはコールバック関数(PxFW 1.0.4 以降)が利用できます。</dd>
	 *   <dt>bool $options['current']</dt>
	 *     <dd><code>true</code> または <code>false</code> を指定します。<code>class="current"</code> を強制的に付加または削除します。このオプションが指定されない場合は、自動的に選択されます。</dd>
	 *   <dt>bool $options['no_escape']</dt>
	 *     <dd><code>true</code> または <code>false</code> を指定します。この値が <code>true</code> の場合、リンクラベルに含まれるHTML特殊文字が自動的にエスケープされます。デフォルトは、<code>true</code>、<code>$options['label']</code>が指定された場合は自動的に <code>false</code> になります。</dd>
	 *   <dt>mixed $options['class']</dt>
	 *     <dd>スタイルシートの クラス名 を文字列または配列で指定します。</dd>
	 * </dl>
	 * 第2引数にリンクラベルを直接指定することもできます。この場合、オプション配列は第3引数に指定します。
	 * 
	 * @return string HTMLソース(aタグ)
	 */
	public function mk_link( $linkto, $options = array() ){
		$parsed_url = parse_url($linkto);
		$args = func_get_args();
		$page_info = $this->site()->get_page_info($linkto);
		if( !$page_info ){ $page_info = $this->site()->get_page_info(@$parsed_url['path']); }
		$href = $this->href($linkto);
		$hrefc = $this->href($this->req()->get_request_file_path());
		$label = $page_info['title_label'];
		$page_id = $page_info['id'];

		$options = array();
		if( count($args) >= 2 && is_array($args[count($args)-1]) ){
			//  最後の引数が配列なら
			//  オプション連想配列として読み込む
			$options = $args[count($args)-1];
			if( is_string(@$options['label']) || (is_object(@$options['label']) && is_callable(@$options['label'])) ){
				$label = $options['label'];
				if( !is_array($options) || !array_key_exists('no_escape', $options) || is_null($options['no_escape']) ){
					$options['no_escape'] = true;
				}
			}
		}
		if( @is_string($args[1]) || (@is_object($args[1]) && @is_callable($args[1])) ){
			//  第2引数が文字列、または function なら
			//  リンクのラベルとして採用
			$label = $args[1];
			if( !is_array($options) || !array_key_exists('no_escape', $options) || is_null($options['no_escape']) ){
				$options['no_escape'] = true;
			}
		}

		$breadcrumb = $this->site()->get_breadcrumb_array($hrefc);
		$is_current = false;
		if( is_array($options) && array_key_exists('current', $options) && !is_null( $options['current'] ) ){
			$is_current = !@empty($options['current']);
		}elseif($href==$hrefc){
			$is_current = true;
		}elseif( $this->site()->is_page_in_breadcrumb($page_info['id']) ){
			$is_current = true;
		}
		$is_popup = false;
		if( strpos( $this->site()->get_page_info($linkto,'layout') , 'popup' ) === 0 ){
			$is_popup = true;
		}
		$label = (!is_null($label)?$label:$href); // labelがnullの場合、リンク先をラベルとする

		$classes = array();
		// CSSのクラスを付加
		if( is_array($options) && array_key_exists('class', $options) && is_string($options['class']) ){
			$options['class'] = preg_split( '/\s+/', trim($options['class']) );
		}
		if( is_array($options) && array_key_exists('class', $options) && is_array($options['class']) ){
			foreach($options['class'] as $class_row){
				array_push($classes, trim($class_row));
			}
		}
		if($is_current){
			array_push($classes, 'current');
		}

		if( is_object($label) && is_callable($label) ){
			$label = $label($page_info);
			if( !is_array($options) || !array_key_exists('no_escape', $options) || is_null($options['no_escape']) ){
				$options['no_escape'] = true;
			}
		}
		if( !@$options['no_escape'] ){
			// no_escape(エスケープしない)指示がなければ、
			// HTMLをエスケープする。
			$label = htmlspecialchars($label);
		}

		$rtn = '<a href="'.htmlspecialchars($href).'"'.(count($classes)?' class="'.htmlspecialchars(implode(' ', $classes)).'"':'').''.($is_popup?' onclick="window.open(this.href);return false;"':'').'>'.$label.'</a>';
		return $rtn;
	}

	/**
	 * domain を取得する
	 */
	public function get_domain(){
		static $rtn;
		if( !is_null($rtn) ){
			return $rtn;
		}
		if( @strlen( $this->conf->domain ) ){
			$rtn = $this->conf->domain;
			return $rtn;
		}
		$rtn = $_SERVER['SERVER_NAME'];
		return $rtn;
	}

	/**
	 * install path を取得する
	 */
	public function get_path_docroot(){
		static $rtn;
		if( !is_null($rtn) ){
			return $rtn;
		}
		if( @strlen( $this->conf->path_docroot ) ){
			$rtn = $this->conf->path_docroot;
			$rtn = preg_replace('/^(.*?)\/*$/s', '$1/', $rtn);
			return $rtn;
		}
		$rtn = dirname( $_SERVER['SCRIPT_NAME'] ).'/';
		return $rtn;
	}

	/**
	 * テキストを、指定の文字セットに変換する。
	 * 
	 * @param mixed $text テキスト
	 * @param string $encode 変換後の文字セット。省略時、`mb_internal_encoding()` から取得
	 * @param string $encodefrom 変換前の文字セット。省略時、自動検出
	 * @return string 文字セット変換後のテキスト
	 */
	public function convert_encoding( $text, $encode = null, $encodefrom = null ){
		if( !is_callable( 'mb_internal_encoding' ) ){ return $text; }
		if( !strlen( $encode ) ){ $encode = mb_internal_encoding(); }
		if( !strlen( $encodefrom ) ){ $encodefrom = mb_internal_encoding().',UTF-8,SJIS-win,eucJP-win,SJIS,EUC-JP,JIS,ASCII'; }

		switch( strtolower( $encode ) ){
			case 'sjis':
			case 'sjis-win':
			case 'shift_jis':
			case 'shift-jis':
			case 'x-sjis':
				// ※ 'ms_kanji'、'windows-31j'、'cp932' は、もともと SJIS-win になる
				$encode = 'SJIS-win';
				break;
			case 'eucjp':
			case 'eucjp-win':
			case 'euc-jp':
				$encode = 'eucJP-win';
				break;
		}

		if( is_array( $text ) ){
			$RTN = array();
			if( !count( $text ) ){ return $text; }
			$TEXT_KEYS = array_keys( $text );
			foreach( $TEXT_KEYS as $Line ){
				$KEY = mb_convert_encoding( $Line , $encode , $encodefrom );
				if( is_array( $text[$Line] ) ){
					$RTN[$KEY] = $this->convert_encoding( $text[$Line] , $encode , $encodefrom );
				}else{
					$RTN[$KEY] = @mb_convert_encoding( $text[$Line] , $encode , $encodefrom );
				}
			}
		}else{
			if( !strlen( $text ) ){ return $text; }
			$RTN = @mb_convert_encoding( $text , $encode , $encodefrom );
		}
		return $RTN;
	}

}
