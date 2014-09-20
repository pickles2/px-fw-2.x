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

		// make instance $site
		require_once(__DIR__.'/site.php');
		$this->site = new site($this);


		// execute content
		$this->path_content = $this->site()->get_page_info( $this->req()->get_request_file_path(), 'content' );
		if( is_null( $this->path_content ) ){
			$this->path_content = $this->req()->get_request_file_path();
		}
		foreach( array_keys( get_object_vars( $this->conf->extensions ) ) as $ext ){
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
		if( @!empty( $this->conf->extensions->{$ext} ) ){
			foreach( $this->contents_cabinet as $contents_key=>$src ){
				if( is_string($this->conf->extensions->{$ext}) ){
					$fnc_name = preg_replace( '/^\\\\*/', '\\', $this->conf->extensions->{$ext} );
					$src = call_user_func( $fnc_name, $this, $src, $contents_key );
				}elseif( is_array($this->conf->extensions->{$ext}) ){
					foreach( $this->conf->extensions->{$ext} as $fnc_name ){
						$fnc_name = preg_replace( '/^\\\\*/', '\\', $fnc_name );
						$src = call_user_func( $fnc_name, $this, $src, $contents_key );
					}
				}
				$this->contents_cabinet[$contents_key] = $src;
			}
		}
		unset($src, $fnc_name);

		// execute theme
		$fnc_name = preg_replace( '/^\\\\*/', '\\', $this->conf->theme );
		$src = call_user_func( $fnc_name, $this );

		$src = $this->output_filter( $src );
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
