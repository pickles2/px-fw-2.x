<?php
/**
 * PX Commands "api"
 */
namespace picklesFramework2\commands;

/**
 * PX Commands "api"
 *
 * ## [API] api.get.*
 *
 * <dl>
 * 	<dt>PX=api.get.version</dt>
 * 		<dd>Pickles Framework のバージョン番号を取得します。</dd>
 * 	<dt>PX=api.get.config</dt>
 * 		<dd>設定オブジェクトを取得します。</dd>
 *
 * 	<dt>PX=api.get.sitemap</dt>
 * 		<dd>サイトマップ全体の配列を取得します。<code>$px->site()</code>が利用できない場合、<code>false</code>を返します。</dd>
 * 	<dt>PX=api.get.sitemap_definition</dt>
 * 		<dd>サイトマップ定義を取得します。<code>$px->site()</code>が利用できない場合、<code>false</code>を返します。</dd>
 * 	<dt>PX=api.get.page_info&path={$path}</dt>
 * 		<dd><code>$px->site()->get_page_info({$path})</code> の返却値を取得します。<code>$path</code>を省略した場合は、カレントページの情報を取得します。<code>$px->site()</code>が利用できない場合、<code>false</code>を返します。</dd>
 * 	<dt>PX=api.get.page_originated_csv&path={$path}</dt>
 * 		<dd><code>$px->site()->get_page_originated_csv({$path})</code> の返却値を取得します。<code>$path</code>を省略した場合は、カレントページの情報を取得します。<code>$px->site()</code>が利用できない場合、<code>false</code>を返します。</dd>
 * 	<dt>PX=api.get.parent</dt>
 * 		<dd><code>$px->site()->get_parent()</code> の返却値を取得します。<code>$px->site()</code>が利用できない場合、<code>false</code>を返します。</dd>
 * 	<dt>PX=api.get.actors</dt>
 * 		<dd><code>$px->site()->get_actors()</code> の返却値を取得します。<code>$px->site()</code>が利用できない場合、<code>false</code>を返します。</dd>
 * 	<dt>PX=api.get.role</dt>
 * 		<dd><code>$px->site()->get_role()</code> の返却値を取得します。<code>$px->site()</code>が利用できない場合、<code>false</code>を返します。</dd>
 * 	<dt>PX=api.get.children&filter={$filter}</dt>
 * 		<dd><code>$px->site()->get_children()</code> の返却値を取得します。<code>$px->site()</code>が利用できない場合、<code>false</code>を返します。</dd>
 * 	<dt>PX=api.get.bros&filter={$filter}</dt>
 * 		<dd><code>$px->site()->get_bros()</code> の返却値を取得します。<code>$px->site()</code>が利用できない場合、<code>false</code>を返します。</dd>
 * 	<dt>PX=api.get.bros_next&filter={$filter}</dt>
 * 		<dd><code>$px->site()->get_bros_next()</code> の返却値を取得します。<code>$px->site()</code>が利用できない場合、<code>false</code>を返します。</dd>
 * 	<dt>PX=api.get.bros_prev&filter={$filter}</dt>
 * 		<dd><code>$px->site()->get_bros_prev()</code> の返却値を取得します。<code>$px->site()</code>が利用できない場合、<code>false</code>を返します。</dd>
 * 	<dt>PX=api.get.next&filter={$filter}</dt>
 * 		<dd><code>$px->site()->get_next()</code> の返却値を取得します。<code>$px->site()</code>が利用できない場合、<code>false</code>を返します。</dd>
 * 	<dt>PX=api.get.prev&filter={$filter}</dt>
 * 		<dd><code>$px->site()->get_prev()</code> の返却値を取得します。<code>$px->site()</code>が利用できない場合、<code>false</code>を返します。</dd>
 * 	<dt>PX=api.get.breadcrumb_array</dt>
 * 		<dd><code>$px->site()->get_breadcrumb_array()</code> の返却値を取得します。<code>$px->site()</code>が利用できない場合、<code>false</code>を返します。</dd>
 * 	<dt>PX=api.get.dynamic_path_info&path={$path}</dt>
 * 		<dd><code>$px->site()->get_dynamic_path_info({$path})</code> の返却値を取得します。<code>$px->site()</code>が利用できない場合、<code>false</code>を返します。</dd>
 * 	<dt>PX=api.get.bind_dynamic_path_param&path={$path}&param={$param}</dt>
 * 		<dd><code>$px->site()->bind_dynamic_path_param({$path}, {$param})</code> の返却値を取得します。<code>{$param}</code> には、パラメータのキーと値の組を必要分格納したオブジェクトをJSON形式の文字列で指定します。<code>$px->site()</code>が利用できない場合、<code>false</code>を返します。</dd>
 *
 * 	<dt>PX=api.get.path_homedir</dt>
 * 		<dd>*[非推奨]* このAPIは <code>PX=api.get.realpath_homedir</code> に改名されました。 古い名前も残されていますが、使用は推奨されません。</dd>
 * 	<dt>PX=api.get.realpath_homedir</dt>
 * 		<dd><code>$px->get_realpath_homedir()</code> の返却値を取得します。</dd>
 * 	<dt>PX=api.get.path_controot</dt>
 * 		<dd><code>$px->get_path_controot()</code> の返却値を取得します。</dd>
 * 	<dt>PX=api.get.path_docroot</dt>
 * 		<dd>*[非推奨]* このAPIは <code>PX=api.get.realpath_docroot</code> に改名されました。 古い名前も残されていますが、使用は推奨されません。</dd>
 * 	<dt>PX=api.get.realpath_docroot</dt>
 * 		<dd><code>$px->get_realpath_docroot()</code> の返却値を取得します。</dd>
 * 	<dt>PX=api.get.path_content</dt>
 * 		<dd><code>$px->get_path_content()</code> の返却値を取得します。</dd>
 * 	<dt>PX=api.get.path_files&path_resource={$path}</dt>
 * 		<dd><code>$px->path_files({$path})</code> の返却値を取得します。<code>$px->site()</code>が利用できない場合、<code>false</code>を返します。</dd>
 * 	<dt>PX=api.get.realpath_files&path_resource={$path}</dt>
 * 		<dd><code>$px->realpath_files({$path})</code> の返却値を取得します。<code>$px->site()</code>が利用できない場合、<code>false</code>を返します。</dd>
 * 	<dt>PX=api.get.path_files_cache&path_resource={$path}</dt>
 * 		<dd><code>$px->path_files_cache({$path})</code> の返却値を取得します。<code>$px->site()</code>が利用できない場合、<code>false</code>を返します。</dd>
 * 	<dt>PX=api.get.realpath_files_cache&path_resource={$path}</dt>
 * 		<dd><code>$px->realpath_files_cache({$path})</code> の返却値を取得します。<code>$px->site()</code>が利用できない場合、<code>false</code>を返します。</dd>
 * 	<dt>PX=api.get.realpath_files_private_cache&path_resource={$path}</dt>
 * 		<dd><code>$px->realpath_files_private_cache({$path})</code> の返却値を取得します。<code>$px->site()</code>が利用できない場合、<code>false</code>を返します。</dd>
 * 	<dt>PX=api.get.domain</dt>
 * 		<dd><code>$px->get_domain()</code> の返却値を取得します。</dd>
 * 	<dt>PX=api.get.directory_index</dt>
 * 		<dd><code>$px->get_directory_index()</code> の返却値を取得します。</dd>
 * 	<dt>PX=api.get.directory_index_primary</dt>
 * 		<dd><code>$px->get_directory_index_primary()</code> の返却値を取得します。</dd>
 * 	<dt>PX=api.get.path_proc_type</dt>
 * 		<dd><code>$px->get_path_proc_type()</code> の返却値を取得します。</dd>
 * 	<dt>PX=api.get.href&linkto={$path_linkto}</dt>
 * 		<dd><code>$px->href({$path_linkto})</code> の返却値を取得します。<code>$px->site()</code>が利用できない場合、<code>false</code>を返します。</dd>
 * 	<dt>PX=api.get.canonical&linkto={$path_linkto}</dt>
 * 		<dd><code>$px->canonical({$path_linkto})</code> の返却値を取得します。<code>$px->site()</code>が利用できない場合、<code>false</code>を返します。</dd>
 * </dl>
 *
 * ## [API] api.is.*
 *
 * <dl>
 * 	<dt>PX=api.is.match_dynamic_path&path={$path}</dt>
 * 		<dd><code>$px->site()->is_match_dynamic_path({$path})</code> の返却値を取得します。</dd>
 * 	<dt>PX=api.is.page_in_breadcrumb&path={$path}</dt>
 * 		<dd><code>$px->site()->is_page_in_breadcrumb({$path})</code> の返却値を取得します。</dd>
 * 	<dt>PX=api.is.ignore_path&path={$path}</dt>
 * 		<dd><code>$px->is_ignore_path({$path})</code> の返却値を取得します。</dd>
 * </dl>
 */
class api{

	/**
	 * Picklesオブジェクト
	 */
	private $px;

	/**
	 * PXコマンド名
	 */
	private $command = array();


	/**
	 * Starting function
	 * @param object $px Picklesオブジェクト
	 * @param object $options プラグイン設定
	 */
	public static function register( $px = null, $options = null ){

		if( count(func_get_args()) <= 1 ){
			return __CLASS__.'::'.__FUNCTION__.'('.( is_array($px) ? json_encode($px) : '' ).')';
		}

		$px->pxcmd()->register('api', function($px){
			(new self( $px ))->kick();
			exit;
		});
	}

	/**
	 * constructor
	 *
	 * @param object $px Picklesオブジェクト
	 */
	public function __construct( $px ){
		$this->px = $px;
	}


	/**
	 * kick
	 */
	private function kick(){

		$this->command = $this->px->get_px_command();

		switch( $this->command[1] ?? "" ){
			case 'get':
				//各種情報の取得
				$this->api_get();
				break;
			case 'is':
				//各種情報の評価
				$this->api_is();
				break;
		}

		if( !strlen( $this->command[1] ?? "" ) ){
			$this->homepage();
		}
		$this->error();
		exit;
	}


	/**
	 * ホームページを表示する。
	 *
	 * HTMLを標準出力した後、`exit()` を発行してスクリプトを終了します。
	 *
	 * @return void
	 */
	private function homepage(){
		$this->user_message('このコマンドは、外部アプリケーションへのAPIを提供します。');
		exit;
	}

	/**
	 * エラーメッセージを表示する。
	 *
	 * HTMLを標準出力した後、`exit()` を発行してスクリプトを終了します。
	 *
	 * @return void
	 */
	private function error(){
		$this->user_message('未定義のコマンドを受け付けました。');
		exit;
	}


	/**
	 * ユーザーへのメッセージを表示して終了する
	 * @param string $msg メッセージテキスト
	 */
	private function user_message($msg){
		if( $this->px->req()->is_cmd() ){
			header('Content-type: text/plain;');
			print $this->px->pxcmd()->get_cli_header();
			print $msg."\n";
			print $this->px->pxcmd()->get_cli_footer();
		}else{
			$html = '';
			ob_start(); ?>
	<p><?= htmlspecialchars($msg ?? "") ?></p>
<?php
			$html .= ob_get_clean();
			print $this->px->pxcmd()->wrap_gui_frame($html);
		}
		exit;
	}



	/**
	 * [API] api.get.*
	 *
	 * 結果を標準出力した後、`exit()` を発行してスクリプトを終了します。
	 *
	 * @return void
	 */
	private function api_get(){
		$sitemap_filter_options = function($px, $cmd=null){
			$options = array();
			$options['filter'] = $px->req()->get_param('filter');
			if( strlen($options['filter'] ?? "") ){
				switch( $options['filter'] ){
					case 'true':
					case '1':
						$options['filter'] = true;
						break;
					case 'false':
					case '0':
						$options['filter'] = false;
						break;
				}
			}
			return $options;
		};

		switch( $this->command[2] ){
			case 'version':
				$val = $this->px->get_version();
				print $this->data_convert( $val );
				break;
			case 'config':
				$val = $this->px->conf();
				print $this->data_convert( $val );
				break;
			case 'sitemap':
				$val = ($this->px->site() ? $this->px->site()->get_sitemap() : false);
				print $this->data_convert( $val );
				break;
			case 'sitemap_definition':
				$val = ($this->px->site() ? $this->px->site()->get_sitemap_definition() : false);
				print $this->data_convert( $val );
				break;
			case 'page_info':
				$path = $this->px->req()->get_param('path');
				if( is_null($path) ){
					$path = $this->px->req()->get_request_file_path();
				}
				$val = ($this->px->site() ? $this->px->site()->get_page_info($path) : false);
				print $this->data_convert( $val );
				break;
			case 'page_originated_csv':
				$path = $this->px->req()->get_param('path');
				if( is_null($path) ){
					$path = $this->px->req()->get_request_file_path();
				}
				$val = ($this->px->site() ? $this->px->site()->get_page_originated_csv($path) : false);
				print $this->data_convert( $val );
				break;
			case 'parent':
				$val = ($this->px->site() ? $this->px->site()->get_parent() : false);
				print $this->data_convert( $val );
				break;
			case 'actors':
				$val = ($this->px->site() ? $this->px->site()->get_actors() : false);
				print $this->data_convert( $val );
				break;
			case 'role':
				$val = ($this->px->site() ? $this->px->site()->get_role() : false);
				print $this->data_convert( $val );
				break;
			case 'children':
				$val = ($this->px->site() ? $this->px->site()->get_children(null, $sitemap_filter_options($this->px, $this->command[2])) : false);
				print $this->data_convert( $val );
				break;
			case 'bros':
				$val = ($this->px->site() ? $this->px->site()->get_bros(null, $sitemap_filter_options($this->px, $this->command[2])) : false);
				print $this->data_convert( $val );
				break;
			case 'bros_next':
				$val = ($this->px->site() ? $this->px->site()->get_bros_next(null, $sitemap_filter_options($this->px, $this->command[2])) : false);
				print $this->data_convert( $val );
				break;
			case 'bros_prev':
				$val = ($this->px->site() ? $this->px->site()->get_bros_prev(null, $sitemap_filter_options($this->px, $this->command[2])) : false);
				print $this->data_convert( $val );
				break;
			case 'next':
				$val = ($this->px->site() ? $this->px->site()->get_next(null, $sitemap_filter_options($this->px, $this->command[2])) : false);
				print $this->data_convert( $val );
				break;
			case 'prev':
				$val = ($this->px->site() ? $this->px->site()->get_prev(null, $sitemap_filter_options($this->px, $this->command[2])) : false);
				print $this->data_convert( $val );
				break;
			case 'breadcrumb_array':
				$val = ($this->px->site() ? $this->px->site()->get_breadcrumb_array() : false);
				print $this->data_convert( $val );
				break;
			case 'dynamic_path_info':
				$val = ($this->px->site() ? $this->px->site()->get_dynamic_path_info($this->px->req()->get_param('path')) : false);
				print $this->data_convert( $val );
				break;
			case 'bind_dynamic_path_param':
				$param = $this->px->req()->get_param('param');
				$param = json_decode( $param, true );
				$val = ($this->px->site() ? $this->px->site()->bind_dynamic_path_param($this->px->req()->get_param('path'), $param) : false);
				print $this->data_convert( $val );
				unset($param);
				break;
			case 'path_homedir': // deprecated
				$val = $this->px->get_realpath_homedir();
				print $this->data_convert( $val );
				break;
			case 'realpath_homedir':
				$val = $this->px->get_realpath_homedir();
				print $this->data_convert( $val );
				break;
			case 'path_controot':
				$val = $this->px->get_path_controot();
				print $this->data_convert( $val );
				break;
			case 'path_docroot': // deprecated
				$val = $this->px->get_realpath_docroot();
				print $this->data_convert( $val );
				break;
			case 'realpath_docroot':
				$val = $this->px->get_realpath_docroot();
				print $this->data_convert( $val );
				break;
			case 'path_content':
				$val = $this->px->get_path_content();
				print $this->data_convert( $val );
				break;
			case 'path_files':
				$val = ($this->px->site() ? $this->px->path_files($this->px->req()->get_param('path_resource')) : false);
				print $this->data_convert( $val );
				break;
			case 'realpath_files':
				$val = ($this->px->site() ? $this->px->realpath_files($this->px->req()->get_param('path_resource')) : false);
				print $this->data_convert( $val );
				break;
			case 'path_files_cache':
				$val = ($this->px->site() ? $this->px->path_files_cache($this->px->req()->get_param('path_resource')) : false);
				print $this->data_convert( $val );
				break;
			case 'realpath_files_cache':
				$val = ($this->px->site() ? $this->px->realpath_files_cache($this->px->req()->get_param('path_resource')) : false);
				print $this->data_convert( $val );
				break;
			case 'realpath_files_private_cache':
				$val = ($this->px->site() ? $this->px->realpath_files_private_cache($this->px->req()->get_param('path_resource')) : false);
				print $this->data_convert( $val );
				break;
			case 'domain':
				$val = $this->px->get_domain();
				print $this->data_convert( $val );
				break;
			case 'directory_index':
				$val = $this->px->get_directory_index();
				print $this->data_convert( $val );
				break;
			case 'directory_index_primary':
				$val = $this->px->get_directory_index_primary();
				print $this->data_convert( $val );
				break;
			case 'path_proc_type':
				$val = $this->px->get_path_proc_type();
				print $this->data_convert( $val );
				break;
			case 'href':
				$val = ($this->px->site() ? $this->px->href($this->px->req()->get_param('linkto')) : false);
				print $this->data_convert( $val );
				break;
			case 'canonical':
				$val = ($this->px->site() ? $this->px->canonical($this->px->req()->get_param('linkto')) : false);
				print $this->data_convert( $val );
				break;
			default:
				print $this->data_convert( array('result'=>0) );
				break;
		}
		exit;
	}

	/**
	 * [API] api.is.*
	 *
	 * 結果を標準出力した後、`exit()` を発行してスクリプトを終了します。
	 *
	 * @return void
	 */
	private function api_is(){
		switch( $this->command[2] ){
			case 'match_dynamic_path':
				$val = ($this->px->site() ? $this->px->site()->is_match_dynamic_path($this->px->req()->get_param('path')) : false);
				print $this->data_convert( $val );
				break;
			case 'page_in_breadcrumb':
				$val = ($this->px->site() ? $this->px->site()->is_page_in_breadcrumb($this->px->req()->get_param('path')) : false);
				print $this->data_convert( $val );
				break;
			case 'ignore_path':
				$val = $this->px->is_ignore_path($this->px->req()->get_param('path'));
				print $this->data_convert( $val );
				break;
			default:
				print $this->data_convert( array('result'=>0) );
				break;
		}
		exit;
	}


	/**
	 * データを自動的に加工して返す。
	 *
	 * @param mixed $val 加工するデータ
	 * @return string 加工されたテキストデータ
	 */
	private function data_convert($val){
		$data_type = $this->px->req()->get_param('type');
		if( !is_string($data_type) || !strlen($data_type ?? "") ){
			$data_type = 'json';
		}
		header('Content-type: application/xml; charset=UTF-8');
		if( $data_type == 'json' ){
			header('Content-type: application/json; charset=UTF-8');
		}elseif( $data_type == 'jsonp' ){
			header('Content-type: application/javascript; charset=UTF-8');
		}
		switch( $data_type ){
			case 'jsonp':
				return $this->data2jsonp($val);
				break;
			case 'json':
				return $this->data2json($val);
				break;
			case 'xml':
				return $this->data2xml($val);
				break;
		}
		// return self::data2jssrc($val);
		return json_encode($val);
	}

	/**
	 * データをXMLに加工して返す。
	 *
	 * @param mixed $val 加工するデータ
	 * @return string 加工されたテキストデータ
	 */
	private function data2xml($val){
		return '<api>'.self::xml_encode($val).'</api>';
	}

	/**
	 * データをJSONに加工して返す。
	 *
	 * @param mixed $val 加工するデータ
	 * @return string 加工されたテキストデータ
	 */
	private function data2json($val){
		// return self::data2jssrc($val);
		return json_encode($val);
	}

	/**
	 * データをJSONPに加工して返す。
	 *
	 * @param mixed $val 加工するデータ
	 * @return string 加工されたテキストデータ
	 */
	private function data2jsonp($val){
		//JSONPのコールバック関数名は、パラメータ callback に受け取る。
		$cb = trim( $this->px->req()->get_param('callback') );
		if( !strlen($cb ?? "") ){
			$cb = 'callback';
		}
		// return $cb.'('.self::data2jssrc($val).');';
		return $cb.'('.json_encode($val).');';
	}


	/**
	 * 変数をXML構造に変換する
	 *
	 * @param mixed $value 値
	 * @param array $options オプション
	 * <dl>
	 *   <dt>delete_arrayelm_if_null</dt>
	 *     <dd>配列の要素が `null` だった場合に削除。</dd>
	 *   <dt>array_break</dt>
	 *     <dd>配列に適当なところで改行を入れる。</dd>
	 * </dl>
	 * @return string XMLシンタックスに変換された値
	 */
	private static function xml_encode( $value = null , $options = array() ){

		if( is_array( $value ) ){
			// 配列
			$is_hash = false;
			$i = 0;
			foreach( $value as $key=>$val ){
				// ArrayかHashか見極める
				if( !is_int( $key ) ){
					$is_hash = true;
					break;
				}
				if( $key != $i ){
					// 順番通りに並んでなかったらHash とする。
					$is_hash = true;
					break;
				}
				$i ++;
			}

			if( $is_hash ){
				$RTN .= '<object>';
			}else{
				$RTN .= '<array>';
			}
			if( $options['array_break'] ){ $RTN .= "\n"; }
			foreach( $value as $key=>$val ){
				if( $options['delete_arrayelm_if_null'] && is_null( $value[$key] ) ){
					// 配列のnull要素を削除するオプションが有効だった場合
					continue;
				}
				$RTN .= '<element';
				if( $is_hash ){
					$RTN .= ' name="'.htmlspecialchars( $key ?? "" ).'"';
				}
				$RTN .= '>';
				$RTN .= self::xml_encode( $value[$key] , $options );
				$RTN .= '</element>';
				if( $options['array_break'] ){ $RTN .= "\n"; }
			}
			if( $is_hash ){
				$RTN .= '</object>';
			}else{
				$RTN .= '</array>';
			}
			if( $options['array_break'] ){ $RTN .= "\n"; }
			return	$RTN;
		}

		if( is_object( $value ) ){
			// オブジェクト型
			$RTN = '';
			$RTN .= '<object>';
			$proparray = get_object_vars( $value );
			$methodarray = get_class_methods( get_class( $value ) );
			foreach( $proparray as $key=>$val ){
				$RTN .= '<element name="'.htmlspecialchars( $key ?? "" ).'">';

				$RTN .= self::xml_encode( $val , $options );
				$RTN .= '</element>';
			}
			$RTN .= '</object>';
			return	$RTN;
		}

		if( is_int( $value ) ){
			// 数値
			$RTN = '<value type="int">'.htmlspecialchars( $value ?? "" ).'</value>';
			return	$RTN;
		}

		if( is_float( $value ) ){
			// 浮動小数点
			$RTN = '<value type="float">'.htmlspecialchars( $value ?? "" ).'</value>';
			return	$RTN;
		}

		if( is_string( $value ) ){
			// 文字列型
			$RTN = '<value type="string">'.htmlspecialchars( $value ?? "" ).'</value>';
			return	$RTN;
		}

		if( is_null( $value ) ){
			// ヌル
			return	'<value type="null"></value>';
		}

		if( is_resource( $value ) ){
			// リソース型
			return	'<value type="undefined"></value>';
		}

		if( is_bool( $value ) ){
			// ブール型
			if( $value ){
				return	'<value type="bool">true</value>';
			}else{
				return	'<value type="bool">false</value>';
			}
		}

		return	'<value type="undefined"></value>';

	}

	/**
	 * ダブルクオートで囲えるようにエスケープ処理する。
	 *
	 * @param string $text テキスト
	 * @return string エスケープされたテキスト
	 */
	private static function escape_doublequote( $text ){
		$text = preg_replace( '/\\\\/' , '\\\\\\\\' , $text);
		$text = preg_replace( '/"/' , '\\"' , $text);
		return	$text;
	}

}
