<?php
/**
 * pickles2
 */
namespace picklesFramework2;

/**
 * pickles2 core class
 *
 * @author Tomoya Koyanagi <tomk79@gmail.com>
 */
class px{
	/**
	 * Pickles のホームディレクトリのパス
	 * @access private
	 */
	private $path_homedir;

	/**
	 * コンテンツディレクトリのパス
	 * @access private
	 */
	private $path_content;

	/**
	 * オブジェクト
	 * @access private
	 */
	private $conf, $fs, $req, $site, $bowl, $pxcmd;

	/**
	 * ディレクトリインデックス(省略できるファイル名の一覧)
	 * @access private
	 */
	private $directory_index;

	/**
	 * プロセス関連情報
	 * @access private
	 */
	private $proc_type, $proc_id, $proc_num = 0;

	/**
	 * 関連ファイルのURL情報
	 * @access private
	 */
	private $relatedlinks = array();

	/**
	 * エラーメッセージ情報
	 */
	private $errors = array();

	/**
	 * 応答ステータスコード
	 * @access private
	 */
	private $response_status = 200;

	/**
	 * 応答ステータスメッセージ
	 * @access private
	 */
	private $response_message = 'OK';

	/**
	 * Pickles Framework のバージョン番号を取得する。
	 *
	 * Pickles Framework のバージョン番号はこのメソッドにハードコーディングされます。
	 *
	 * バージョン番号発行の規則は、 Semantic Versioning 2.0.0 仕様に従います。
	 * - [Semantic Versioning(英語原文)](http://semver.org/)
	 * - [セマンティック バージョニング(日本語)](http://semver.org/lang/ja/)
	 *
	 * *[ナイトリービルド]*<br />
	 * バージョン番号が振られていない、開発途中のリビジョンを、ナイトリービルドと呼びます。<br />
	 * ナイトリービルドの場合、バージョン番号は、次のリリースが予定されているバージョン番号に、
	 * ビルドメタデータ `+nb` を付加します。
	 * 通常は、プレリリース記号 `alpha` または `beta` を伴うようにします。
	 * - 例：1.0.0-beta.12+nb (=1.0.0-beta.12リリース前のナイトリービルド)
	 *
	 * @return string バージョン番号を示す文字列
	 */
	public function get_version(){
		return '2.0.15-alpha+nb';
	}

	/**
	 * constructor
	 *
	 * @param string $path_homedir Pickles のホームディレクトリのパス
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
		@header_remove('X-Powered-By');
		$this->set_status(200);// 200 OK

		if( !array_key_exists( 'REMOTE_ADDR' , $_SERVER ) ){
			// commandline only
			if( realpath($_SERVER['SCRIPT_FILENAME']) === false ||
				dirname(realpath($_SERVER['SCRIPT_FILENAME'])) !== realpath('./')
			){
				if( array_key_exists( 'PWD' , $_SERVER ) && is_file($_SERVER['PWD'].'/'.$_SERVER['SCRIPT_FILENAME']) ){
					$_SERVER['SCRIPT_FILENAME'] = realpath($_SERVER['PWD'].'/'.$_SERVER['SCRIPT_FILENAME']);
				}else{
					// for Windows
					// .px_execute.php で chdir(__DIR__) されていることが前提。
					$_SERVER['SCRIPT_FILENAME'] = realpath('./'.basename($_SERVER['SCRIPT_FILENAME']));
				}
			}
		}

		// load Config
		$this->path_homedir = $path_homedir;
		if( is_file($this->path_homedir.DIRECTORY_SEPARATOR.'config.json') ){
			$this->conf = json_decode( file_get_contents( $this->path_homedir.DIRECTORY_SEPARATOR.'config.json' ) );
		}elseif( is_file($this->path_homedir.DIRECTORY_SEPARATOR.'config.php') ){
			$this->conf = include( $this->path_homedir.DIRECTORY_SEPARATOR.'config.php' );
		}
		$this->conf = json_decode( json_encode( $this->conf ) );

		if ( @strlen($this->conf->default_timezone) ) {
			@date_default_timezone_set($this->conf->default_timezone);
		}
		if ( !@strlen($this->conf->path_files) ) {
			$this->conf->path_files = '{$dirname}/{$filename}_files/';
		}

		// make instance $bowl
		require_once(__DIR__.'/bowl.php');
		$this->bowl = new bowl();

		// Apply command-line option "--command-php"
		$req = new \tomk79\request();
		if( strlen(@$req->get_cli_option( '--command-php' )) ){
			@$this->conf->commands->php = $req->get_cli_option( '--command-php' );
		}
		if( strlen(@$req->get_cli_option( '-c' )) ){
			@$this->conf->path_phpini = $req->get_cli_option( '-c' );
		}

		// make instance $fs
		$conf = new \stdClass;
		$conf->file_default_permission = $this->conf->file_default_permission;
		$conf->dir_default_permission  = $this->conf->dir_default_permission;
		$conf->filesystem_encoding	 = $this->conf->filesystem_encoding;
		$this->fs = new \tomk79\filesystem( $conf );
		$this->path_homedir = $this->fs->get_realpath($this->path_homedir.'/');

		// make instance $req
		$conf = new \stdClass;
		$conf->session_name   = $this->conf->session_name;
		$conf->session_expire = $this->conf->session_expire;
		$conf->directory_index_primary = $this->get_directory_index_primary();
		$this->req = new \tomk79\request( $conf );
		if( strlen(@$this->req->get_cli_option( '-u' )) ){
			$_SERVER['HTTP_USER_AGENT'] = @$this->req->get_cli_option( '-u' );
		}


		// make instance $pxcmd
		require_once(__DIR__.'/pxcmd.php');
		$this->pxcmd = new pxcmd($this);


		// funcs: Before sitemap
		$this->fnc_call_plugin_funcs( @$this->conf->funcs->before_sitemap, $this );


		// make instance $site
		require_once(__DIR__.'/site.php');
		$this->site = new site($this);



		// execute Content
		$current_page_info = $this->site()->get_page_info( $this->req()->get_request_file_path() );
		$this->path_content = $this->req()->get_request_file_path();
		$ext = $this->get_path_proc_type( $this->req()->get_request_file_path() );
		if($ext !== 'direct'){
			$this->path_content = $current_page_info['content'];
			if( is_null( $this->path_content ) ){
				$this->path_content = $this->req()->get_request_file_path();
			}
			// $ext = $this->get_path_proc_type( $this->path_content );
			foreach( array_keys( get_object_vars( $this->conf->funcs->processor ) ) as $tmp_ext ){
				if( $this->fs()->is_file( './'.$this->path_content.'.'.$tmp_ext ) ){
					$ext = $tmp_ext;
					$this->path_content = $this->path_content.'.'.$tmp_ext;
					break;
				}
			}
		}
		$this->proc_type = $ext;
		unset($ext);

		// デフォルトの Content-type を出力
		$this->output_content_type();


		// funcs: Before contents
		$this->fnc_call_plugin_funcs( @$this->conf->funcs->before_content, $this );



		if( $this->is_ignore_path( $this->req()->get_request_file_path() ) ){
			$this->set_status(403);// 403 Forbidden
			print 'ignored path';
			exit;
		}else{
			self::exec_content( $this );
		}


		// funcs: process functions
		$this->fnc_call_plugin_funcs( @$this->conf->funcs->processor->{$this->proc_type}, $this );



		// funcs: Before output
		$this->fnc_call_plugin_funcs( @$this->conf->funcs->before_output, $this );

	}

	/**
	 * call plugin functions
	 *
	 * @param mixed $func_list List of plugins function
	 * @return bool 成功時 `true`、失敗時 `false`
	 */
	private function fnc_call_plugin_funcs( $func_list ){
		if( is_null($func_list) ){ return false; }
		$param_arr = func_get_args();
		array_shift($param_arr);

		if( @!empty( $func_list ) ){
			// functions
			if( is_object($func_list) ){
				$func_list = get_object_vars( $func_list );
			}
			if( is_string($func_list) ){
				$fnc_name = $func_list;
				$fnc_name = preg_replace( '/^\\\\*/', '\\', $fnc_name );
				preg_match( '/^(.*?)(?:\\((.*)\\))?$/', $fnc_name, $matched );
				$fnc_name = @$matched[1];
				$option_value = @$matched[2];
				unset($matched);
				if( strlen( $option_value ) ){
					$option_value = json_decode( $option_value );
				}
				// var_dump($fnc_name);
				// var_dump($option_value);
				$this->proc_num ++;
				if( @!strlen($this->proc_id) ){
					$this->proc_id = $this->proc_type.'_'.$this->proc_num;
				}
				array_push( $param_arr, $option_value );
				call_user_func_array( $fnc_name, $param_arr);
				$this->proc_id = null;

			}elseif( is_array($func_list) ){
				foreach( $func_list as $fnc_id=>$fnc_name ){
					if( is_string($fnc_name) ){
						$fnc_name = preg_replace( '/^\\\\*/', '\\', $fnc_name );
					}
					if( is_string($fnc_id) && !preg_match('/^[0-9]+$/', $fnc_id) ){
						$this->proc_id = $fnc_id;
					}
					$param_arr_dd = $param_arr;
					array_unshift( $param_arr_dd, $fnc_name );
					call_user_func_array( array( $this, 'fnc_call_plugin_funcs' ), $param_arr_dd);
				}
			}
			unset($fnc_name);
		}
		return true;
	}


	/**
	 * get $fs
	 * @see https://github.com/tomk79/filesystem
	 * @return object $fs オブジェクト
	 */
	public function fs(){
		return $this->fs;
	}

	/**
	 * get $req
	 * @see https://github.com/tomk79/request
	 * @return object $req オブジェクト
	 */
	public function req(){
		return $this->req;
	}

	/**
	 * get $site
	 * @return object $site オブジェクト
	 */
	public function site(){
		return $this->site;
	}

	/**
	 * get $pxcmd
	 * @return object $pxcmd オブジェクト
	 */
	public function pxcmd(){
		return $this->pxcmd;
	}

	/**
	 * get $bowl
	 * @return object $bowl オブジェクト
	 */
	public function bowl(){
		return $this->bowl;
	}

	/**
	 * get $conf
	 * @return object $conf オブジェクト
	 */
	public function conf(){
		return $this->conf;
	}

	/**
	 * get home directory path
	 * @return string ホームディレクトリパス
	 */
	public function get_path_homedir(){
		return $this->path_homedir;
	}

	/**
	 * get content path
	 * @return string コンテンツディレクトリパス
	 */
	public function get_path_content(){
		return $this->path_content;
	}

	/**
	 * directory_index(省略できるファイル名) の一覧を得る。
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
	 * ファイルの処理方法を調べる。
	 *
	 * @param string $path パス
	 * @return string 処理方法
	 * - ignore = 対象外パス
	 * - direct = 加工せずそのまま出力する(デフォルト)
	 * - その他 = process名を格納して返す
	 */
	public function get_path_proc_type( $path = null ){
		static $rtn = array();
		if( is_null($path) ){
			$path = $this->req()->get_request_file_path();
		}
		$path = $this->fs()->get_realpath( '/'.$path );
		if( is_dir('./'.$path) ){
			$path .= '/'.$this->get_directory_index_primary();
		}
		if( preg_match('/(?:\/|\\\\)$/', $path) ){
			$path .= $this->get_directory_index_primary();
		}
		$path = $this->fs()->normalize_path($path);

		if( @is_bool($rtn[$path]) ){
			return $rtn[$path];
		}

		foreach( $this->conf->paths_proc_type as $row => $type ){
			if(!is_string($row)){continue;}
			$preg_pattern = preg_quote($this->fs()->normalize_path($this->fs()->get_realpath($row)), '/');
			if( preg_match('/\*/',$preg_pattern) ){
				// ワイルドカードが使用されている場合
				$preg_pattern = preg_quote($row,'/');
				$preg_pattern = preg_replace('/'.preg_quote('\*','/').'/','(?:.*?)',$preg_pattern);//ワイルドカードをパターンに反映
				$preg_pattern = $preg_pattern.'$';//前方・後方一致
			}elseif(is_dir($row)){
				$preg_pattern = preg_quote($this->fs()->normalize_path($this->fs()->get_realpath($row)).'/','/');
			}elseif(is_file($row)){
				$preg_pattern = preg_quote($this->fs()->normalize_path($this->fs()->get_realpath($row)),'/');
			}
			if( preg_match( '/^'.$preg_pattern.'/s' , $path ) ){
				$rtn[$path] = $type;
				return $rtn[$path];
			}
		}
		$rtn[$path] = 'direct';// <- default
		return $rtn[$path];
	}//get_path_proc_type();

	/**
	 * 除外ファイルか調べる。
	 *
	 * @param string $path パス
	 * @return bool 除外ファイルの場合 `true`、それ以外の場合に `false` を返します。
	 */
	public function is_ignore_path( $path ){
		$type = $this->get_path_proc_type($path);
		if( $type == 'ignore' ){
			return true;
		}
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
				@header('Content-type: text/css; charset='.$output_encoding);
				break;
			case 'js':
				@header('Content-type: text/javascript; charset='.$output_encoding);
				break;
			case 'html':
			case 'htm':
				@header('Content-type: text/html; charset='.$output_encoding);
				break;
			case 'png': @header('Content-type: image/png'); break;
			case 'gif': @header('Content-type: image/gif'); break;
			case 'jpg':case 'jpeg':case 'jpe': @header('Content-type: image/jpeg'); break;
			case 'svg':case 'svgz': @header('Content-type: image/svg+xml'); break;
			case 'txt':case 'text':
			default:
				@header('Content-type: text/plain; charset='.$output_encoding); break;
		}
		return ;
	}

	/**
	 * get response status
	 * @return int ステータスコード
	 */
	public function get_status(){
		return $this->response_status;
	}
	/**
	 * get response message
	 * @return int ステータスメッセージ
	 */
	public function get_status_message(){
		return $this->response_message;
	}

	/**
	 * set response status
	 * @param int $code ステータスコード (100〜599の間の数値)
	 * @param string $message メッセージ
	 * @return bool 成功時 true, 失敗時 false
	 */
	public function set_status($code, $message = null){
		$code = intval($code);
		if( strlen( $code ) != 3 ){
			return false;
		}
		if( !($code >= 100 && $code <= 599) ){
			return false;
		}
		$this->response_status = intval($code);
		if( !strlen($message) ){
			switch( $this->response_status ){
				case 200: $message = 'OK'; break;
				case 403: $message = 'Forbidden'; break;
				case 404: $message = 'NotFound'; break;
				default:break;
			}
		}
		$this->response_message = $message;

		@header('HTTP/1.1 '.$this->response_status.' '.$this->response_message);
		return true;
	}

	/**
	 * エラーメッセージを送信する。
	 *
	 * @param $message string エラーメッセージ
	 * @return bool true
	 */
	public function error( $message = null ){
		@array_push( $this->errors , $message );
		@header('X-PXFW-ERROR: '.$message.'', false);
		return true;
	}

	/**
	 * 送信されたエラーメッセージの一覧を取得する。
	 *
	 * @return array 送信済みのエラーメッセージの一覧
	 */
	public function get_errors(){
		return $this->errors;
	}

	/**
	 * 拡張ヘッダ X-PXFW-RELATEDLINK にリンクを追加する。
	 *
	 * 拡張ヘッダ `X-PXFW-RELATEDLINK` は、サイトマップや物理ディレクトリから発見できないファイルを、Pickles Framework のパブリッシュツールに知らせます。
	 *
	 * 通常、Pickles Framework のパブリッシュツールは 動的に生成されたページなどを知ることができず、パブリッシュされません。このメソッドを通じて、明示的に知らせる必要があります。
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
		@header('X-PXFW-RELATEDLINK: '.$path.'', false);

		return true;
	}

	/**
	 * 拡張ヘッダ X-PXFW-RELATEDLINK に追加されたリンクを取得する。
	 *
	 * 拡張ヘッダ `X-PXFW-RELATEDLINK` に、既に追加されているリンクの一覧を取得します。
	 *
	 * リンクは、 `add_relatedlink()` を通じて追加されます。
	 *
	 * @return array 正常時 `true`、失敗した場合 `false`
	 */
	public function get_relatedlinks(){
		return $this->relatedlinks;
	}

	/**
	 * getting PX Command
	 */
	public function get_px_command(){
		if( !$this->conf()->allow_pxcommands && !$this->req()->is_cmd() ){
			return null;
		}
		$rtn = array();
		$cmd = $this->req()->get_param('PX');
		if( is_string($cmd) ){
			$rtn = explode('.', $cmd);
		}
		return $rtn;
	}// get_px_command()

	/**
	 * execute content
	 * @param object $px picklesオブジェクト
	 * @return bool true
	 */
	private static function exec_content( $px ){
		if( !$px->fs()->is_file( './'.$px->get_path_content() ) ){
			$px->set_status(404);// 404 NotFound
			$px->bowl()->send('<p>404 - File not found.</p>');
			return true;
		}
		if( $px->proc_type === 'direct' ){
			$src = $px->fs()->read_file( './'.$px->get_path_content() );
		}else{
			ob_start();
			include( './'.$px->get_path_content() );
			$src = ob_get_clean();
		}
		$px->bowl()->send($src);
		return true;
	}

	/**
	 * Contents Manifesto のソースを取得する
	 * @return string HTMLコード
	 */
	public function get_contents_manifesto(){
		$px = $this;
		$rtn = '';
		$realpath = $this->get_path_docroot().$this->href( @$this->conf()->contents_manifesto );
		if( !$this->fs()->is_file( $realpath ) ){
			return '';
		}
		ob_start();
		include( $realpath );
		$rtn = ob_get_clean();
		return $rtn;
	}

	/**
	 * 実行者がパブリッシュツールかどうか調べる
	 *
	 * @return bool パブリッシュツールの場合 `true`, それ以外の場合 `false` を返します。
	 */
	public function is_publish_tool(){
		if( !strlen( @$_SERVER['HTTP_USER_AGENT'] ) ){
			// UAが空白なら パブリッシュツール
			return true;
		}
		if( preg_match( '/PicklesCrawler/', @$_SERVER['HTTP_USER_AGENT'] ) ){
			// "PicklesCrawler" が含まれていたら パブリッシュツール
			return true;
		}
		return false;
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
	 * print '&lt;a href=&quot;'.htmlspecialchars( $px-&gt;href('/aaa/bbb.html') ).'&quot;&gt;リンク&lt;/a&gt;';
	 *
	 * // ページIDを指定する例
	 * print '&lt;a href=&quot;'.htmlspecialchars( $px-&gt;href('A-00') ).'&quot;&gt;リンク&lt;/a&gt;';
	 * ?&gt;</pre>
	 *
	 * インストールパスがドキュメントルートではない場合の例:
	 * <pre>&lt;?php
	 * // インストールパスが &quot;/installpath/&quot; の場合
	 * print '&lt;a href=&quot;'.htmlspecialchars( $px-&gt;href('/aaa/bbb.html') ).'&quot;&gt;リンク&lt;/a&gt;';
	 *	 // &quot;/installpath/aaa/bbb.html&quot; が返されます。
	 * ?&gt;</pre>
	 *
	 * @param string $linkto リンク先の パス または ページID
	 *
	 * @return string href属性値
	 */
	public function href( $linkto ){
		$parsed_url = parse_url($linkto);
		$tmp_page_info_by_id = $this->site()->get_page_info_by_id($linkto);
		if( !$tmp_page_info_by_id ){
			$tmp_page_info_by_id = $this->site()->get_page_info_by_id(@$parsed_url['path']);
		}
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

		$path = $this->fs()->normalize_path($path);
		switch( $this->site()->get_path_type( $path ) ){
			case 'full_url':
			case 'javascript':
			case 'anchor':
				break;
			default:
				// index.htmlを省略
				$path = preg_replace('/\/'.$this->get_directory_index_preg_pattern().'((?:\?|\#).*)?$/si','/$1',$path);
				if( preg_match( '/^\\//' , $path ) ){
					//  スラッシュから始まる絶対パスの場合、
					//  インストールパスを起点としたパスに書き変えて返す。
					// $path = preg_replace( '/^\/+/' , '' , $path );
					$path = preg_replace('/\/$/', '', $this->get_path_controot()).$path;
				}
				$parsed_url_fin = parse_url($path);
				$path = $this->fs()->normalize_path( $parsed_url_fin['path'] );

				// パラメータを、引数の生の状態に戻す。
				$path .= (strlen(@$parsed_url['query'])?'?'.@$parsed_url['query']:(strlen(@$parsed_url_fin['query'])?'?'.@$parsed_url_fin['query']:''));
				$path .= (strlen(@$parsed_url['fragment'])?'#'.@$parsed_url['fragment']:(strlen(@$parsed_url_fin['fragment'])?'?'.@$parsed_url_fin['fragment']:''));
				break;
		}

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
	 * print $px-&gt;mk_link('/aaa/bbb.html');
	 *
	 * // ページ /aaa/bbb.html へのリンクをオプション付きで生成
	 * print $px-&gt;mk_link('/aaa/bbb.html', array(
	 *	 'label'=>'<span>リンクラベル</span>', //リンクラベルを文字列で指定
	 *	 'class'=>'class_a class_b', //CSSのクラス名を指定
	 *	 'no_escape'=>true, //エスケープ処理をキャンセル
	 *	 'current'=>true //カレント表示にする
	 * ));
	 * ?&gt;</pre>
	 *
	 * 第2引数に文字列または関数としてリンクラベルを渡す方法もあります。
	 * この場合、その他のオプションは第3引数に渡すことができます。
	 *
	 * 実装例:
	 * <pre>&lt;?php
	 * // ページ /aaa/bbb.html へのリンクをオプション付きで生成
	 * print $px-&gt;mk_link('/aaa/bbb.html',
	 *   '<span>リンクラベル</span>' , //リンクラベルを文字列で指定
	 *   array(
	 *	 'class'=>'class_a class_b',
	 *	 'no_escape'=>true,
	 *	 'current'=>true
	 *   )
	 * );
	 * ?&gt;</pre>
	 *
	 * リンクのラベルはコールバック関数でも指定できます。
	 * コールバック関数には、リンク先のページ情報を格納した連想配列が引数として渡されます。
	 *
	 * 実装例:
	 * <pre>&lt;?php
	 * //リンクラベルをコールバック関数で指定
	 * print $px-&gt;mk_link(
	 *   '/aaa/bbb.html',
	 *   function($page_info){return htmlspecialchars($page_info['title_label']);}
	 * );
	 * ?&gt;</pre>
	 *
	 * @param string $linkto リンク先のパス。PxFWのインストールパスを基点にした絶対パスで指定。
	 * @param array $options options: [as string] Link label, [as function] callback function, [as array] Any options.
	 * <dl>
	 *   <dt>mixed $options['label']</dt>
	 *	 <dd>リンクのラベルを変更します。文字列か、またはコールバック関数が利用できます。</dd>
	 *   <dt>bool $options['current']</dt>
	 *	 <dd><code>true</code> または <code>false</code> を指定します。<code>class="current"</code> を強制的に付加または削除します。このオプションが指定されない場合は、自動的に選択されます。</dd>
	 *   <dt>bool $options['no_escape']</dt>
	 *	 <dd><code>true</code> または <code>false</code> を指定します。この値が <code>false</code> の場合、リンクラベルに含まれるHTML特殊文字が自動的にエスケープされます。デフォルトは、<code>false</code>、<code>$options['label']</code>が指定された場合は自動的に <code>true</code> になります。</dd>
	 *   <dt>mixed $options['class']</dt>
	 *	 <dd>スタイルシートの クラス名 を文字列または配列で指定します。</dd>
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
		if( strpos( $this->site()->get_page_info($linkto,'layout') , 'popup' ) === 0 ){ // <- 'popup' で始まるlayoutは、ポップアップとして扱う。
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
		if( $is_current ){
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
	 * @return string ドメイン名
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
		$rtn = @$_SERVER['SERVER_NAME'];
		return $rtn;
	}

	/**
	 * コンテンツルートディレクトリのパス(=install path) を取得する
	 * @return string コンテンツディレクトリのパス(HTTPクライアントから見た場合のパス)
	 */
	public function get_path_controot(){
		static $rtn;
		if( !is_null($rtn) ){
			return $rtn;
		}
		$rtn = dirname( $_SERVER['SCRIPT_NAME'] );
		if( $rtn != '/' ){
			$rtn .= '/';
		}
		if( $this->req()->is_cmd() ){
			$rtn = '/';
			if( @strlen( $this->conf->path_controot ) ){
				$rtn = $this->conf->path_controot;
				$rtn = preg_replace('/^(.*?)\/*$/s', '$1/', $rtn);
				$rtn = $this->fs->normalize_path($rtn);
				return $rtn;
			}
		}
		$rtn = $this->fs->normalize_path($rtn);
		return $rtn;
	}

	/**
	 * DOCUMENT_ROOT のパスを取得する
	 * @return string ドキュメントルートのパス
	 */
	public function get_path_docroot(){
		$path_controot = $this->fs->normalize_path( $this->fs()->get_realpath( $this->get_path_controot() ) );
		$path_cd = $this->fs->normalize_path( $this->fs()->get_realpath( dirname($_SERVER['SCRIPT_FILENAME']) ).DIRECTORY_SEPARATOR );
		$rtn = preg_replace( '/'.preg_quote( $path_controot, '/' ).'$/s', '', $path_cd ).DIRECTORY_SEPARATOR;
		$rtn = $this->fs->normalize_path($rtn);
		return $rtn;
	}

	/**
	 * ローカルリソースディレクトリのパスを得る。
	 *
	 * @param string $localpath_resource ローカルリソースのパス
	 * @return string ローカルリソースの実際の絶対パス
	 */
	public function path_files( $localpath_resource = null ){
		$tmp_page_info = $this->site()->get_current_page_info();
		$path_content = $tmp_page_info['content'];
		if( is_null($path_content) ){
			$path_content = $this->req()->get_request_file_path();
		}
		unset($tmp_page_info);

		$rtn = $this->conf->path_files;
		$data = array(
			'dirname'=>dirname($path_content),
			'filename'=>basename($this->fs()->trim_extension($path_content)),
			'ext'=>strtolower($this->fs()->get_extension($path_content)),
		);
		$rtn = str_replace( '{$dirname}', $data['dirname'], $rtn );
		$rtn = str_replace( '{$filename}', $data['filename'], $rtn );
		$rtn = str_replace( '{$ext}', $data['ext'], $rtn );
		$rtn = preg_replace( '/^\/*/', '/', $rtn );
		$rtn = preg_replace( '/\/*$/', '', $rtn ).'/';

		$rtn = $rtn.$localpath_resource;
		if( $this->fs()->is_dir('./'.$rtn) ){
			$rtn .= '/';
		}
		$rtn = $this->href( $rtn );
		$rtn = $this->fs->normalize_path($rtn);
		$rtn = preg_replace( '/^\/+/', '/', $rtn );
		return $rtn;
	}//path_files()

	/**
	 * ローカルリソースディレクトリのサーバー内部パスを得る。
	 *
	 * @param string $localpath_resource ローカルリソースのパス
	 * @return string ローカルリソースのサーバー内部パス
	 */
	public function realpath_files( $localpath_resource = null ){
		$rtn = $this->path_files( $localpath_resource );
		$rtn = $this->fs()->get_realpath( $rtn );
		$rtn = $this->fs()->get_realpath( $this->get_path_docroot().$rtn );
		return $rtn;
	}//realpath_files()

	/**
	 * ローカルリソースのキャッシュディレクトリのパスを得る。
	 *
	 * @param string $localpath_resource ローカルリソースのパス
	 * @return string ローカルリソースキャッシュのパス
	 */
	public function path_files_cache( $localpath_resource = null ){
		$tmp_page_info = $this->site()->get_current_page_info();
		$path_content = $tmp_page_info['content'];
		if( is_null($path_content) ){
			$path_content = $this->req()->get_request_file_path();
		}
		unset($tmp_page_info);

		$path_original = $this->get_path_controot().$path_content;
		$path_original = $this->fs()->get_realpath($this->fs()->trim_extension($path_original).'_files/'.$localpath_resource);
		$rtn = $this->get_path_controot().'/'.$this->conf()->public_cache_dir.'/c'.$path_content;
		$rtn = $this->fs()->get_realpath($this->fs()->trim_extension($rtn).'_files/'.$localpath_resource);
		$this->fs()->mkdir_r( dirname( $this->get_path_docroot().$rtn ) );

		if( file_exists( $this->get_path_docroot().$path_original ) ){
			if( is_dir($this->get_path_docroot().$path_original) ){
				$this->fs()->mkdir_r( $this->get_path_docroot().$rtn );
			}else{
				$this->fs()->mkdir_r( dirname( $this->get_path_docroot().$rtn ) );
			}
			$this->fs()->copy_r( $this->get_path_docroot().$path_original, $this->get_path_docroot().$rtn );
		}

		$rtn = $this->fs()->normalize_path($rtn);
		$rtn = preg_replace( '/^\/+/', '/', $rtn );
		$this->add_relatedlink($rtn);
		return $rtn;
	}//path_files_cache()

	/**
	 * ローカルリソースのキャッシュディレクトリのサーバー内部パスを得る。
	 *
	 * @param string $localpath_resource ローカルリソースのパス
	 * @return string ローカルリソースキャッシュのサーバー内部パス
	 */
	public function realpath_files_cache( $localpath_resource = null ){
		$rtn = $this->path_files_cache( $localpath_resource );
		$rtn = $this->fs()->get_realpath( $this->get_path_docroot().$rtn );
		return $rtn;
	}//realpath_files_cache()


	/**
	 * コンテンツ別の非公開キャッシュディレクトリのサーバー内部パスを得る。
	 *
	 * @param string $localpath_resource ローカルリソースのパス
	 * @return string コンテンツ別の非公開キャッシュのサーバー内部パス
	 */
	public function realpath_files_private_cache( $localpath_resource = null ){
		$tmp_page_info = $this->site()->get_current_page_info();
		$path_content = $tmp_page_info['content'];
		if( is_null($path_content) ){
			$path_content = $this->req()->get_request_file_path();
		}
		unset($tmp_page_info);

		$path_original = $this->get_path_controot().$path_content;
		$path_original = $this->fs()->get_realpath($this->fs()->trim_extension($path_original).'_files/'.$localpath_resource);
		$rtn = $this->get_path_homedir().'/_sys/ram/caches/c'.$path_content;
		$rtn = $this->fs()->get_realpath($this->fs()->trim_extension($rtn).'_files/'.$localpath_resource);
		$this->fs()->mkdir_r( dirname( $rtn ) );

		$rtn = $this->fs()->normalize_path( $rtn );
		$rtn = preg_replace( '/^\/+/', '/', $rtn );
		$rtn = $this->fs()->get_realpath( $rtn );
		return $rtn;
	}//realpath_files_private_cache()

	/**
	 * プラグイン別公開キャッシュのパスを得る。
	 *
	 * @param string $localpath_resource リソースのパス
	 * @return string 公開キャッシュのパス
	 */
	public function path_plugin_files( $localpath_resource = null ){
		$rtn = $this->get_path_controot().'/'.$this->conf()->public_cache_dir.'/p/'.urlencode($this->proc_id).'/';
		$rtn = $this->fs()->get_realpath( $rtn.'/' );
		if( !$this->fs()->is_dir( $this->get_path_docroot().$rtn ) ){
			$this->fs()->mkdir_r( $this->get_path_docroot().$rtn );
		}
		if( !strlen( $localpath_resource ) ){
			return $this->fs()->normalize_path($rtn);
		}
		$rtn = $this->fs()->get_realpath( $rtn.'/'.$localpath_resource );
		if( $this->fs()->is_dir( $this->get_path_docroot().$rtn ) ){
			$rtn .= '/';
		}
		if( !$this->fs()->is_dir( dirname($this->get_path_docroot().$rtn) ) ){
			$this->fs()->mkdir_r( dirname($this->get_path_docroot().$rtn) );
		}
		$rtn = $this->fs()->normalize_path($rtn);
		$rtn = preg_replace( '/^\/+/', '/', $rtn );
		$this->add_relatedlink($rtn);
		return $rtn;
	}

	/**
	 * プラグイン別公開キャッシュのサーバー内部パスを得る。
	 *
	 * @param string $localpath_resource リソースのパス
	 * @return string プライベートキャッシュのサーバー内部パス
	 */
	public function realpath_plugin_files( $localpath_resource = null ){
		$rtn = $this->path_plugin_files( $localpath_resource );
		$rtn = $this->fs()->get_realpath( $this->get_path_docroot().$rtn );
		return $rtn;
	}

	/**
	 * プラグイン別プライベートキャッシュのサーバー内部パスを得る。
	 *
	 * @param string $localpath_resource リソースのパス
	 * @return string プライベートキャッシュのサーバー内部パス
	 */
	public function realpath_plugin_private_cache( $localpath_resource = null ){
		$lib_realpath = $this->get_path_homedir().'_sys/ram/caches/p/'.urlencode($this->proc_id).'/';
		$rtn = $this->fs()->get_realpath( $lib_realpath.'/' );
		if( !$this->fs()->is_dir( $rtn ) ){
			$this->fs()->mkdir_r( $rtn );
		}
		if( !strlen( $localpath_resource ) ){
			return $rtn;
		}
		$rtn .= '/'.$localpath_resource;
		// if( $this->fs()->is_dir( $rtn ) ){
		// 	$rtn .= '/';
		// }
		$rtn = $this->fs()->get_realpath( $rtn );
		if( !$this->fs()->is_dir( dirname($rtn) ) ){
			$this->fs()->mkdir_r( dirname($rtn) );
		}
		return $rtn;
	}

	/**
	 * テキストを、指定の文字セットに変換する
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

	/**
	 * アプリケーションロックする。
	 *
	 * @param string $app_name アプリケーションロック名
	 * @param int $expire 有効時間(秒) (省略時: 60秒)
	 * @return bool ロック成功時に `true`、失敗時に `false` を返します。
	 */
	public function lock( $app_name, $expire = 60 ){
		$lockfilepath = $this->get_path_homedir().'_sys/ram/applock/'.urlencode($app_name).'.lock.txt';
		$timeout_limit = 5;

		if( !@is_dir( dirname( $lockfilepath ) ) ){
			$this->fs()->mkdir_r( dirname( $lockfilepath ) );
		}

		#	PHPのFileStatusCacheをクリア
		clearstatcache();

		$i = 0;
		while( $this->is_locked( $app_name, $expire ) ){
			$i ++;
			if( $i >= $timeout_limit ){
				return false;
				break;
			}
			sleep(1);

			#	PHPのFileStatusCacheをクリア
			clearstatcache();
		}
		$src = '';
		$src .= 'ProcessID='.getmypid()."\r\n";
		$src .= @date( 'Y-m-d H:i:s' , time() )."\r\n";
		$RTN = $this->fs()->save_file( $lockfilepath , $src );
		return	$RTN;
	}//lock()

	/**
	 * アプリケーションロックされているか確認する。
	 *
	 * @param string $app_name アプリケーションロック名
	 * @param int $expire 有効時間(秒) (省略時: 60秒)
	 * @return bool ロック中の場合に `true`、それ以外の場合に `false` を返します。
	 */
	public function is_locked( $app_name, $expire = 60 ){
		$lockfilepath = $this->get_path_homedir().'_sys/ram/applock/'.urlencode($app_name).'.lock.txt';
		$lockfile_expire = $expire;

		#	PHPのFileStatusCacheをクリア
		clearstatcache();

		if( $this->fs()->is_file($lockfilepath) ){
			if( ( time() - filemtime($lockfilepath) ) > $lockfile_expire ){
				#	有効期限を過ぎていたら、ロックは成立する。
				return false;
			}
			return true;
		}
		return false;
	}//is_locked()

	/**
	 * アプリケーションロックを解除する。
	 *
	 * @param string $app_name アプリケーションロック名
	 * @return bool ロック解除成功時に `true`、失敗時に `false` を返します。
	 */
	public function unlock( $app_name ){
		$lockfilepath = $this->get_path_homedir().'_sys/ram/applock/'.urlencode($app_name).'.lock.txt';

		#	PHPのFileStatusCacheをクリア
		clearstatcache();

		return @unlink( $lockfilepath );
	}//unlock()

	/**
	 * アプリケーションロックファイルの更新日を更新する。
	 *
	 * @param string $app_name アプリケーションロック名
	 * @return bool 成功時に `true`、失敗時に `false` を返します。
	 */
	public function touch_lockfile( $app_name ){
		$lockfilepath = $this->get_path_homedir().'_sys/ram/applock/'.urlencode($app_name).'.lock.txt';

		#	PHPのFileStatusCacheをクリア
		clearstatcache();
		if( !is_file( $lockfilepath ) ){
			return false;
		}

		return touch( $lockfilepath );
	}//touch_lockfile()

	/**
	 * Pickles の SVG ロゴソースを取得する
	 * @param array $opt オプション
	 * <dl>
	 * 	<dt>color</dt><dd>ロゴの色コード</dd>
	 * </dl>
	 * @return string Pickles ロゴの SVG ソースコード
	 */
	public function get_pickles_logo_svg( $opt = array() ){
		$logo_color = '#f6f6f6';
		if( @strlen( $opt['color'] ) ){
			$logo_color = $opt['color'];
		}
		ob_start();
		?>
<svg version="1.1" id="Pickles Framework LOGO" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="30px" height="40px" viewBox="0 0 40 50" enable-background="new 0 0 40 50" xml:space="preserve">
<g>
	<g>
		<path fill="<?php print htmlspecialchars($logo_color); ?>" d="M38.514,17.599c-0.049-0.831-0.191-1.571-0.174-2.405c0.02-0.777-0.002-1.581-0.338-2.298
			c-0.484-1.033-0.473-2.235-1.027-3.287c-0.484-0.914-1.334-1.693-1.936-2.541c-0.295-0.415-0.656-0.776-0.934-1.201
			c-0.246-0.373-0.557-0.422-0.902-0.69c-0.465-0.357-0.826-0.874-1.26-1.273c-0.408-0.373-0.887-0.258-1.326-0.533
			c-0.26-0.163-0.346-0.414-0.643-0.567c-0.412-0.21-0.777-0.318-1.23-0.358c-0.408-0.035-0.838-0.001-1.18-0.271
			c-0.914-0.718-1.96-0.61-3.042-0.7C23.729,1.407,22,0.836,21.185,0.799c-0.786-0.035-1.667-0.157-2.448-0.045
			c-0.264,0.039-0.496,0.154-0.766,0.184c-0.377,0.044-0.711-0.049-1.083-0.061c-0.403-0.014-0.876,0.133-1.267,0.229
			c-0.956,0.231-1.884,0.577-2.776,0.988c-0.461,0.213-0.973,0.291-1.451,0.453C10.89,2.718,10.471,2.987,9.993,3.2
			c-0.51,0.224-0.869,0.512-1.307,0.824c-0.48,0.342-1.065,0.559-1.509,0.953C6.622,5.475,6.1,6.013,5.598,6.564
			C5.369,6.818,5.01,7.11,4.853,7.416C4.701,7.711,4.773,8.061,4.531,8.35c-0.143,0.168-0.382,0.229-0.51,0.422
			c-0.167,0.25-0.188,0.565-0.353,0.821c-0.178,0.271-0.28,0.583-0.438,0.872c-0.433,0.79-0.564,1.293-0.741,2.134
			c-0.155,0.74-0.709,1.344-0.817,2.084c-0.101,0.683,0.11,1.388-0.067,2.103c-0.242,0.986-0.489,1.966-0.586,2.979
			c-0.087,0.902,0.154,1.771,0.051,2.67c-0.118,1.014,0.223,2.089,0.328,3.104c0.293,2.828,1.2,5.59,1.459,8.362
			c0.021,0.246-0.042,0.48-0.002,0.716c0.1,0.548,0.489,0.836,0.354,1.445c-0.128,0.557,0.148,0.861,0.275,1.408
			c0.049,0.217,0.012,0.26-0.027,0.502c-0.096,0.642,0.247,1.352,0.39,1.971c0.174,0.758,0.254,1.544,0.305,2.323
			c0.07,1.016,0.655,1.801,0.942,2.736c0.272,0.879,0.281,1.827,0.851,2.588c0.518,0.688,1.422,1.182,2.276,1.282
			c0.784,0.094,1.937,0.21,2.708,0.01c0.749-0.196,1.319-0.579,1.881-1.099c0.411-0.384,0.567-0.89,0.798-1.389
			c0.57-1.24,0.088-2.425,0.387-3.699c0.104-0.453,0.322-0.924,0.381-1.376c0.04-0.332-0.104-0.626-0.096-0.954
			c0.009-0.384,0.223-0.65,0.299-1.001c0.065-0.283-0.029-0.58-0.035-0.876c-0.012-0.486-0.161-1.018-0.082-1.495
			c0.105-0.664,0.578-1.341,1.112-1.713c0.358-0.25,0.545-0.225,0.995-0.225c0.924,0.003,1.783-0.15,2.7-0.205
			c0.842-0.047,1.596,0.182,2.461,0.087c1.75-0.199,4.988-0.023,6.546-0.924c0.449-0.261,0.643-0.836,1-1.005
			c0.342-0.16,1.004,0,1.387-0.09c1.086-0.257,1.557-1.38,2.334-2.111c1.012-0.954,1.996-2.079,2.941-3.053
			c0.051-0.052,0.078-0.086,0.113-0.145c0.055-0.099,0.098-0.2,0.154-0.295c0.047-0.073,0.125-0.15,0.154-0.234
			c0.342-0.961,1.244-1.649,1.293-2.756c0.025-0.543,0.107-1.077,0.213-1.603C38.203,21.037,38.611,19.331,38.514,17.599z
			 M35.098,22.087c-0.258,0.668,0.141,1.435-0.146,2.104c-0.096,0.215-0.414,0.369-0.527,0.596
			c-0.109,0.217-0.061,0.545-0.145,0.756c-0.443,1.093-1.465,2.137-2.35,2.888c-0.879,0.743-1.701,1.713-2.799,2.144
			c-0.408,0.16-0.811,0.164-1.207,0.358c-0.527,0.262-1.025,0.301-1.584,0.47c-1.107,0.327-2.757,0.184-3.89,0.359
			c-0.246,0.039-0.475,0.031-0.695,0.065c-0.21,0.032-0.479,0.043-0.668,0.036c-0.071-0.004-0.131-0.039-0.213-0.036
			c-0.736,0-1.473,0.021-2.21,0.021c-0.871,0-1.714-0.075-2.587,0.008c-0.459,0.048-0.734,0.282-1.149,0.492
			c-0.353,0.184-0.706,0.109-1.056,0.337c-0.321,0.203-0.63,0.413-0.916,0.668c-0.531,0.47-0.945,0.958-1.086,1.669
			c-0.108,0.548-0.1,1.1-0.247,1.635c-0.141,0.508-0.215,1.025-0.176,1.567c0.033,0.457,0.174,0.966,0.266,1.428
			c0.077,0.368,0.084,0.767,0.012,1.15c-0.096,0.504-0.272,0.868-0.195,1.392c0.141,0.938,0.081,3.8-1.263,3.929
			c-0.324,0.036-0.688-0.051-1.026-0.03c-0.335,0.02-0.642,0.004-0.966-0.074c-0.558-0.134-0.485-0.317-0.599-0.828
			c-0.048-0.228-0.224-0.416-0.269-0.595c-0.045-0.186-0.016-0.39-0.062-0.579c-0.021-0.094-0.04-0.181-0.059-0.25
			c-0.121-0.556-0.047-0.625,0.671-0.725c0.554-0.069,1.437-0.117,1.689-0.707c0.114-0.269,0.097-0.727-0.123-0.938
			c-0.202-0.194-0.74-0.231-1.028-0.247C8,41.123,6.985,41.373,6.727,40.803c-0.225-0.505-0.284-1.136,0.193-1.486
			c0.353-0.263,0.792-0.22,1.207-0.243c0.379-0.022,0.794-0.078,1.056-0.392c0.233-0.281,0.094-0.968-0.088-1.274
			c-0.176-0.297-0.475-0.409-0.803-0.452c-0.66-0.087-1.508,0.167-2.062-0.313c-0.397-0.341-0.462-1.001-0.093-1.385
			c0.276-0.285,0.714-0.354,1.086-0.415c0.684-0.105,1.775-0.118,1.819-1.036c0.053-1.036-0.631-1.475-1.546-1.475
			c-0.59,0-1.255,0.031-1.795-0.257c-0.383-0.205-0.467-0.458-0.503-0.859c-0.044-0.544-0.16-1.076-0.311-1.597
			c-0.151-0.53-0.205-1.039-0.286-1.588c-0.17-1.129-0.265-2.27-0.361-3.407c-0.044-0.492-0.049-0.878-0.23-1.34
			c-0.394-1.001-0.091-1.954-0.106-2.999c-0.012-1.009,0.237-1.757,0.389-2.726c0.104-0.659-0.143-1.457,0.07-2.085
			c0.093-0.274,0.315-0.438,0.463-0.674c0.178-0.28,0.172-0.502,0.278-0.793c0.224-0.62,0.645-1.147,0.778-1.808
			c0.083-0.411-0.062-0.841,0.1-1.236c0.147-0.356,0.467-0.721,0.688-1.04c0.521-0.75,1.121-1.446,1.796-2.06
			c0.526-0.48,1.123-1.011,1.764-1.333c0.637-0.319,1.223-0.835,1.892-1.104c0.434-0.174,0.744-0.078,1.129-0.108
			c0.34-0.026,0.552-0.292,0.804-0.485c0.401-0.306,0.651-0.252,1.125-0.34c0.746-0.137,0.995-0.938,1.853-0.833
			c0.755,0.094,1.357,0.2,2.1,0.062c0.822-0.155,1.717-0.081,2.547-0.073c1.545,0.012,4.1,0.655,5.48,1.314
			c0.525,0.252,1.092,0.509,1.6,0.78c0.756,0.403,1.598,0.935,2.309,1.417c1.014,0.692,1.643,1.578,2.309,2.584
			c0.363,0.55,0.838,1.023,1.166,1.596c0.309,0.538,0.564,1.154,0.844,1.708c0.205,0.406,0.254,0.79,0.254,1.245
			c-0.002,1.163,0.121,2.386,0.291,3.53C36.135,19.17,35.566,20.866,35.098,22.087z M22.27,13.678
			c-0.289,0.532-0.24,1.13,0.135,1.619c0.556,0.724,1.805,1.087,2.525,0.359c0.61-0.614,0.524-1.585,0.034-2.248
			C24.333,12.552,23.008,12.32,22.27,13.678z M15.898,12.908c-0.136-0.036-0.29-0.053-0.461-0.049
			c-0.559,0.005-1.304,0.231-1.507,0.794c-0.258,0.706-0.104,1.556,0.476,2.064c0.629,0.549,2.205,0.448,2.47-0.454
			C17.108,14.475,16.807,13.143,15.898,12.908z"/>
	</g>
</g>
<g>
	<path fill="<?php print htmlspecialchars($logo_color); ?>" d="M30.088,48.687c-0.18,0.021-0.371,0.004-0.57-0.04c-0.748-0.175-1.4-0.707-2.01-1.157
		c-0.115-0.082-0.18-0.244-0.275-0.346c-0.279-0.311-0.795-0.827-1.217-0.929c-0.028-0.012-0.059-0.017-0.088-0.017
		c-0.207,0.009-0.357,0.195-0.479,0.341c-0.14,0.168-0.307,0.324-0.469,0.473c-0.133,0.124-0.301,0.227-0.412,0.375
		c-0.076,0.098-0.132,0.158-0.229,0.244c-0.611,0.546-0.982,1.238-1.835,1.188c-0.748-0.039-1.44-0.212-1.912-0.798
		c-0.603-0.742-1.419-1.275-1.249-2.369c0.117-0.763,0.773-1.385,1.231-1.925c0.265-0.305,0.659-0.539,0.96-0.817l0.026-0.022
		c0.236-0.212,0.236-0.212,0.058-0.439c-0.146-0.187-0.335-0.347-0.516-0.507c-0.101-0.083-0.195-0.17-0.289-0.259
		c-0.733-0.7-1.257-0.942-1.527-1.955c-0.256-0.965,0.139-1.764,0.928-2.351c0.168-0.123,0.416-0.23,0.535-0.406
		c0.017-0.032,0.028-0.082,0.058-0.105c0.577-0.466,1.276-0.692,2.023-0.421c0.78,0.276,1.329,1.133,1.975,1.652
		c0.194,0.156,0.391,0.32,0.577,0.485c0.139,0.126,0.288,0.345,0.497,0.359c0.231,0.017,0.45-0.346,0.596-0.492
		c0.234-0.227,0.455-0.453,0.697-0.68c0.053-0.051,0.09-0.118,0.143-0.164c0.055-0.051,0.119-0.118,0.176-0.149
		c0.191-0.109,0.326-0.29,0.465-0.453c0.436-0.494,1.279-1.001,1.957-0.677c0.279,0.132,0.58,0.312,0.797,0.531
		c0.166,0.168,0.248,0.387,0.428,0.544c0.207,0.181,0.375,0.384,0.582,0.57c0.203,0.187,0.377,0.375,0.453,0.649
		c0.102,0.391,0.217,1.079,0,1.448c-0.188,0.318-0.725,0.57-1.008,0.82c-0.201,0.186-0.252,0.345-0.408,0.547
		c-0.008,0.006-0.166,0.219-0.186,0.236c-0.336,0.327-0.945,0.796-0.443,1.254c0.242,0.219,0.48,0.422,0.699,0.673
		c0.121,0.137,0.297,0.203,0.43,0.332c0.551,0.539,1.199,1.155,1.295,1.964c0.08,0.656-0.369,0.813-0.738,1.231
		C31.301,47.71,30.984,48.576,30.088,48.687z M22.495,38.755c-0.048,0.004-0.097,0.012-0.143,0.023
		c-0.52,0.125-0.934,0.821-0.586,1.294c0.18,0.246,0.553,0.383,0.773,0.602c0.268,0.271,0.471,0.606,0.676,0.919
		c0.408,0.638,0.953,0.814,0.518,1.571c-0.471,0.814-1.285,1.338-1.857,2.066c-0.357,0.453-0.429,1.031,0.139,1.313
		c0.76,0.375,1.166-0.537,1.625-0.977c0.293-0.282,0.582-0.572,0.898-0.831c0.254-0.206,0.385-0.425,0.596-0.66
		c0.548-0.606,1.187-0.278,1.632,0.235c0.24,0.282,0.574,0.544,0.85,0.805c0.258,0.246,0.562,0.403,0.842,0.618
		c0.408,0.313,0.822,0.99,1.379,0.676c0.297-0.163,0.559-0.766,0.234-1.012c-0.072-0.056-0.41-0.301-0.471-0.363
		c-0.014-0.017-0.016-0.035-0.031-0.047c-0.012-0.018-0.037-0.024-0.051-0.04c-0.018-0.016-0.021-0.038-0.037-0.058
		s-0.043-0.02-0.057-0.035c-0.336-0.329-0.732-0.689-1.127-0.979c-0.4-0.297-1-0.625-1.039-1.188
		c-0.01-0.149,0.045-0.294,0.129-0.416c0.223-0.327,0.67-0.562,0.971-0.823c0.426-0.369,0.857-0.709,1.146-1.188
		c0.152-0.255,0.324-0.337,0.377-0.639c0.025-0.156,0.109-0.313,0-0.445c-0.07-0.084-0.191-0.102-0.273-0.173
		c-0.117-0.093-0.129-0.233-0.258-0.32c-0.158-0.108-0.25-0.069-0.396,0.028c-0.191,0.132-0.404,0.261-0.57,0.421
		c-0.195,0.191-0.354,0.42-0.561,0.599c-0.242,0.207-0.492,0.407-0.725,0.626c-0.342,0.323-0.91,1.305-1.486,0.934
		c-0.161-0.105-0.3-0.247-0.455-0.363C24.461,40.412,23.482,38.695,22.495,38.755z"/>
</g>
</svg>
<?php
		return ob_get_clean();
	}//get_pickles_logo_svg()

}