<?php
/**
 * class site
 * 
 * Pickles2 のコアオブジェクトの1つ `$site` のオブジェクトクラスを定義します。
 * 
 * @author Tomoya Koyanagi <tomk79@gmail.com>
 */
namespace picklesFramework2;

/**
 * Site and page Manager
 * 
 * Pickles2 のコアオブジェクトの1つ `$site` のオブジェクトクラスです。
 * このオブジェクトは、Pickles2 の初期化処理の中で自動的に生成され、`$px` の内部に格納されます。
 * 
 * メソッド `$px->site()` を通じてアクセスします。
 * 
 * @author Tomoya Koyanagi <tomk79@gmail.com>
 */
class site{
	/**
	 * Picklesオブジェクト
	 */
	private $px;
	/**
	 * 設定オブジェクト
	 */
	private $conf;
	/**
	 * サイトマップ配列
	 */
	private $sitemap_array = array();
	/**
	 * ページIDマップ
	 */
	private $sitemap_id_map = array();
	/**
	 * ダイナミックパスの一覧
	 */
	private $sitemap_dynamic_paths = array();
	/**
	 * サイトマップのツリー構造
	 */
	private $sitemap_page_tree = array();
	/**
	 * ダイナミックパスパラメータ
	 */
	private $dynamic_path_param = array();

	/**
	 * コンストラクタ
	 * 
	 * @param object $px Picklesオブジェクト
	 */
	public function __construct( $px ){
		$this->px = $px;
		$this->conf = $this->px->conf();

		//サイトマップCSVを読み込む
		$this->load_sitemap_csv();

		//ダイナミックパスを検索、パラメータを取り出す
		foreach( $this->sitemap_dynamic_paths as $sitemap_dynamic_path ){
			if( preg_match( $sitemap_dynamic_path['preg'] , $this->px->req()->get_request_file_path() , $tmp_matched ) ){
				$page_info = $this->get_page_info( $this->px->req()->get_request_file_path() );
				foreach( $sitemap_dynamic_path['pattern_map'] as $key=>$val ){
					$this->set_path_param( $val , $tmp_matched[$key+1] );
				}
				break;
			}
		}
	}

	/**
	 * ダイナミックパスからパラメータを受け取る。
	 * 
	 * @param string $key ダイナミックパスパラメータ名
	 * @return string ダイナミックパスパラメータ値
	 */
	public function get_path_param( $key ){
		return $this->dynamic_path_param[$key];
	}//get_path_param()

	/**
	 * ダイナミックパスからのパラメータをセットする。
	 * 
	 * @param string $key ダイナミックパスパラメータ名
	 * @param string $val ダイナミックパスパラメータ値
	 * @return bool 常に `true`
	 */
	public function set_path_param( $key , $val ){
		$this->dynamic_path_param[$key] = $val;
		return true;
	}//set_path_param()

	/**
	 * サイトマップCSVを読み込む。
	 * 
	 * サイトマップディレクトリに格納されたサイトマップCSV (`./_PX/sitemaps/*.csv`) を読み込み、パースします。
	 * 
	 * この処理は、サイトマップの行数や階層構造によっては、重い処理になります。
	 * そのため、このメソッドは、パースした後の配列情報をキャッシュディレクトリ(`./_PX/caches/sitemaps/`)にキャッシュし、
	 * 次回以降はキャッシュを読み込むことで重い処理を回避します。
	 * 
	 * このキャッシュは、キャッシュファイルのタイムスタンプより新しいCSVを発見するか、
	 * `?PX=clearcache` によりキャッシュがクリアされると、次回アクセス時に再生成されます。
	 * 
	 * @return bool 常に `true`
	 */
	private function load_sitemap_csv(){
		$path_sitemap_cache_dir = $this->px->get_path_homedir().'_sys/ram/caches/sitemaps/';
		if( $this->is_sitemap_cache() ){
			//  サイトマップキャッシュが存在する場合、キャッシュからロードする。
			$this->sitemap_array         = @include($path_sitemap_cache_dir.'sitemap.array');
			$this->sitemap_id_map        = @include($path_sitemap_cache_dir.'sitemap_id_map.array');
			$this->sitemap_dynamic_paths = @include($path_sitemap_cache_dir.'sitemap_dynamic_paths.array');
			$this->sitemap_page_tree     = @include($path_sitemap_cache_dir.'sitemap_page_tree.array');
			return true;
		}

		$path_sitemap_dir = $this->px->get_path_homedir().'sitemaps/';
		$ary_sitemap_files = $this->px->fs()->ls( $path_sitemap_dir );
		sort($ary_sitemap_files);

		// $path_top の設定値をチューニング
		$path_top = $this->conf->path_top;
		if(!strlen( $path_top )){ $path_top = '/'; }
		$path_top = preg_replace( '/\/$/si' , '/'.$this->px->get_directory_index_primary() , $path_top );//index.htmlを付加する。

		//  サイトマップをロード
		$num_auto_pid = 0;
		foreach( $ary_sitemap_files as $basename_sitemap_csv ){
			if( strtolower( $this->px->fs()->get_extension($basename_sitemap_csv) ) != 'csv' ){
				continue;
			}
			$tmp_sitemap = $this->px->fs()->read_csv( $path_sitemap_dir.$basename_sitemap_csv );
			foreach ($tmp_sitemap as $row_number=>$row) {
				set_time_limit(30);//タイマー延命
				$num_auto_pid++;
				$tmp_array = array();
				if( preg_match( '/^(?:\*)/is' , $row[0] ) ){
					if( $row_number > 0 ){
						// アスタリスク始まりの場合はコメント行とみなす。
						continue;
					}
					// アスタリスク始まりでも、0行目の場合は、定義行とみなす。
					// 定義行とみなす条件: 0行目の全セルがアスタリスク始まりであること。
					$is_definition_row = true;
					foreach($row as $cell_value){
						if( !preg_match( '/^(?:\*)/is' , $cell_value ) ){
							$is_definition_row = false;
						}
					}
					if( !$is_definition_row ){
						continue;
					}
					$tmp_sitemap_definition = array();
					$tmp_col_id = 'A';
					foreach($row as $tmp_col_number=>$cell_value){
						$cell_value = trim(preg_replace('/^\*/si', '', $cell_value));
						$tmp_sitemap_definition[$cell_value] = array(
							'num'=>$tmp_col_number,
							'col'=>$tmp_col_id++,
							'key'=>$cell_value,
							'name'=>$cell_value,
						);
					}
					unset($is_definition_row);
					unset($cell_value);
					continue;
				}
				foreach ($tmp_sitemap_definition as $defrow) {
					$tmp_array[$defrow['key']] = $row[$defrow['num']];
				}
				if( !preg_match( '/^(?:\/|alias\:|javascript\:|\#|[a-zA-Z0-9]+\:\/\/)/is' , $tmp_array['path'] ) ){
					// 不正な形式のチェック
					continue;
				}
				switch( $this->get_path_type( $tmp_array['path'] ) ){
					case 'full_url':
					case 'javascript':
					case 'anchor':
						// 直リンク系のパスをエイリアス扱いにする
						$tmp_array['path'] = preg_replace('/^(?:alias:)?/s', 'alias:', $tmp_array['path']);
						break;
					default:
						// スラ止のパスに index.html を付加する。
						// ただし、JS、アンカー、外部リンクには適用しない。
						$tmp_array['path'] = preg_replace( '/\/((?:\?|\#).*)?$/si' , '/'.$this->px->get_directory_index_primary().'$1' , $tmp_array['path'] );
						break;
				}
				if( !strlen( $tmp_array['id'] ) ){
					//ページID文字列を自動生成
					$tmp_id = ':auto_page_id.'.($num_auto_pid);
					$tmp_array['id'] = $tmp_id;
					unset($tmp_id);
				}

				// project.path_top の設定に対する処理
				if( $tmp_array['path'] == $path_top ){
					$tmp_array['id'] = '';
				}elseif( !strlen($tmp_array['id']) ){
					$tmp_array['id'] = ':auto_page_id.'.($num_auto_pid);
				}

				if($this->get_path_type( $tmp_array['path'] ) == 'dynamic'){
					//ダイナミックパスのインデックス作成
					$tmp_preg_pattern = $tmp_array['path'];
					$preg_pattern = '';
					while(1){
						if( !preg_match('/^(.*?)\{(\$|\*)([a-zA-Z0-9\-\_]*)\}(.*)$/s',$tmp_preg_pattern,$tmp_matched) ){
							$preg_pattern .= preg_quote($tmp_preg_pattern,'/');
							break;
						}
						$preg_pattern .= preg_quote($tmp_matched[1],'/');
						switch( $tmp_matched[2] ){
							case '$':
								$preg_pattern .= '([a-zA-Z0-9\-\_]+)';break;
							case '*':
								$preg_pattern .= '(.*?)';break;
						}
						$tmp_preg_pattern = $tmp_matched[4];
						continue;
					}
					preg_match_all('/\{(\$|\*)([a-zA-Z0-9\-\_]*)\}/',$tmp_array['path'],$pattern_map);
					$tmp_path_original = $tmp_array['path'];
					$tmp_array['path'] = preg_replace('/'.preg_quote('{','/').'(\$|\*)([a-zA-Z0-9\-\_]*)'.preg_quote('}','/').'/s','$2',$tmp_array['path']);
					array_push( $this->sitemap_dynamic_paths, array(
						'path'=>$tmp_array['path'],
						'path_original'=>$tmp_path_original,
						'id'=>$tmp_array['id'],
						'preg'=>'/^'.$preg_pattern.'$/s',
						'pattern_map'=>$pattern_map[2],
					) );
					if( !strlen( $tmp_array['content'] ) ){
						$tmp_array['content'] = $tmp_array['path'];
					}
					$tmp_array['path'] = $tmp_path_original;
					unset($preg_pattern);
					unset($pattern_map);
					unset($tmp_path_original);
				}

				if( !strlen( $tmp_array['content'] ) ){
					$tmp_array['content'] = $tmp_array['path'];
					$tmp_array['content'] = preg_replace('/(?:\?|\#).*$/s','',$tmp_array['content']);
					$tmp_array['content'] = preg_replace('/\/$/s','/'.$this->px->get_directory_index_primary(), $tmp_array['content']);
				}
				$tmp_array['content'] = preg_replace( '/\/$/si' , '/'.$this->px->get_directory_index_primary() , $tmp_array['content'] );//index.htmlを付加する。
				if( preg_match( '/^alias\:/s' , $tmp_array['path'] ) ){
					//エイリアスの値調整
					$tmp_array['content'] = null;
					$tmp_array['path'] = preg_replace( '/^alias\:/s' , 'alias'.$num_auto_pid.':' , $tmp_array['path'] );
				}

				//  パンくず欄の先頭が > から始まっていた場合、削除
				$tmp_array['logical_path'] = @preg_replace( '/^\>+/s' , '' , $tmp_array['logical_path'] );

				$this->sitemap_array[$tmp_array['path']] = $tmp_array;
				$this->sitemap_id_map[$tmp_array['id']] = $tmp_array['path'];
			}
		}
		//  / サイトマップをロード

		//  ダイナミックパスを並び替え
		usort($this->sitemap_dynamic_paths, function($a,$b){
			$path_short_a = preg_replace( '/\{.*$/si', '', $a['path_original'] );
			$path_short_b = preg_replace( '/\{.*$/si', '', $b['path_original'] );
			if( strlen($path_short_a) > strlen($path_short_b) ){ return -1; }
			if( strlen($path_short_a) < strlen($path_short_b) ){ return  1; }
			if( $path_short_a > $path_short_b ){ return -1; }
			if( $path_short_a < $path_short_b ){ return  1; }
			return 0;
		});

		//  ページツリー情報を構成
		$this->sitemap_page_tree = array();
		foreach( $this->sitemap_array as $tmp_path=>$tmp_page_info ){
			set_time_limit(30);//タイマー延命
			$this->get_children( $tmp_path );
			// $this->get_children( $tmp_path, array('filter'=>true) );
			// $this->get_children( $tmp_path, array('filter'=>false) );//list_flgを無視して、全員持ってくる
		}
		set_time_limit(30);//タイマーリセット
		unset($tmp_path, $tmp_page_info );

		//  キャッシュディレクトリを作成
		set_time_limit(0);//タイマー延命
		$this->px->fs()->mkdir($path_sitemap_cache_dir);

		//  キャッシュファイルを作成
		$this->px->fs()->save_file( $path_sitemap_cache_dir.'sitemap.array' , self::data2phpsrc($this->sitemap_array) );
		$this->px->fs()->save_file( $path_sitemap_cache_dir.'sitemap_id_map.array' , self::data2phpsrc($this->sitemap_id_map) );
		$this->px->fs()->save_file( $path_sitemap_cache_dir.'sitemap_dynamic_paths.array' , self::data2phpsrc($this->sitemap_dynamic_paths) );
		$this->px->fs()->save_file( $path_sitemap_cache_dir.'sitemap_page_tree.array' , self::data2phpsrc($this->sitemap_page_tree) );
		set_time_limit(30);//タイマーリセット

		return true;
	}//load_sitemap_csv();

	/**
	 * サイトマップキャッシュが読み込み可能か調べる。
	 * 
	 * @return bool 読み込み可能な場合に `true`、読み込みできない場合に `false` を返します。
	 */
	private function is_sitemap_cache(){
		$path_sitemap_cache_dir = $this->px->get_path_homedir().'_sys/ram/caches/sitemaps/';
		$path_sitemap_dir = $this->px->get_path_homedir().'sitemaps/';
		if(
			!is_file($path_sitemap_cache_dir.'sitemap.array') || 
			!is_file($path_sitemap_cache_dir.'sitemap_id_map.array') || 
			!is_file($path_sitemap_cache_dir.'sitemap_dynamic_paths.array')
		){
			return false;
		}
		foreach( $this->px->fs()->ls( $path_sitemap_dir ) as $filename ){
			if( $this->px->fs()->is_newer_a_than_b( $path_sitemap_dir.$filename , $path_sitemap_cache_dir.'sitemap.array' ) ){
				return false;
			}
		}
		return true;
	}

	/**
	 * サイトマップ配列を取得する。
	 * 
	 * @return array 全ページが含まれたサイトマップ配列
	 */
	public function get_sitemap(){
		return $this->sitemap_array;
	}

	/**
	 * 親ページのIDを取得する。
	 * 
	 * @param string $path 起点とするページのパス または ページID。省略時、カレントページから自動的に取得します。
	 * @return string|bool 親ページを見つけた場合に、そのページID。見つからない場合には `false` を返します。
	 */
	public function get_parent( $path = null ){
		if( is_null( $path ) ){
			$path = $this->px->req()->get_request_file_path();
		}
		if(!strlen($this->get_page_info($path,'id'))){
			// トップページの親はいない。
			return false;
		}
		$logical_path = $this->get_page_info( $path , 'logical_path' );
		if( !strlen($logical_path) ){return '';}
		$logical_paths = explode('>',$logical_path);
		$rtn = $logical_paths[count($logical_paths)-1];
		if(is_null($rtn)){
			return false;
		}
		return $this->get_page_info( $rtn, 'id' );
	}

	/**
	 * 所属するカテゴリトップページのIDを取得する。
	 * 
	 * ページ `$path` の親ページをたどり、初めに `category_top_flg` が立っているページを、
	 * 自身が所属するカテゴリのトップページとみなし、そのページIDを返します。
	 * 
	 * @param string $path 起点とするページのパス または ページID。省略時、カレントページから自動的に取得します。
	 * @return string カテゴリトップページのページID
	 */
	public function get_category_top( $path = null ){
		if( is_null( $path ) ){
			$path = $this->px->req()->get_request_file_path();
		}
		$current_page_info = $this->get_page_info($path);
		if( $current_page_info['category_top_flg'] ){
			//  自身がカテゴリトップだった場合。
			return $current_page_info['id'];
		}
		if( !strlen($current_page_info['id']) ){
			//  自身がトップページだった場合。
			return '';
		}
		$page_info = $current_page_info;
		$parent_pid = $page_info['id'];
		while( $parent_pid = $this->get_parent($parent_pid) ){
			if(!strlen($parent_pid)){
				break;
			}
			$page_info = $this->get_page_info($parent_pid);
			if( $page_info['category_top_flg'] ){
				//  自身がカテゴリトップだった場合。
				return $page_info['id'];
			}
		}
		return $page_info['id'];
	}//get_category_top()

	/**
	 * グローバルメニューのページID一覧を取得する。
	 * 
	 * サイトマップ配列から、次の条件に当てはまるページをグローバルメニューとして抽出します。
	 * 
	 * - トップページ直下にある。
	 * - かつ、`list_flg` が立っている。
	 * - かつ、`category_top_flg` が立っている。
	 * 
	 * @return array ページの一覧
	 */
	public function get_global_menu(){
		$rtn = array();
		$home_children = $this->get_children('', array('filter'=>true));//PxFW 1.0.4 list_flg を参照するように変更
		foreach( $home_children as $page_id ){
			$page_info = $this->get_page_info($page_id);
			if(!$page_info['category_top_flg']){continue;}
			array_push($rtn, $page_id);
		}
		return $rtn;
	}//get_global_menu()

	/**
	 * ショルダーメニューのページID一覧を取得する。
	 * 
	 * サイトマップ配列から、次の条件に当てはまるページをショルダーメニューとして抽出します。
	 * 
	 * - トップページ直下にある。
	 * - かつ、`list_flg` が立っている。
	 * - かつ、`category_top_flg` が立っていない。
	 * 
	 * このメソッドは、PxFW 1.0.4 で追加されました。
	 * 
	 * @return array ページの一覧
	 */
	public function get_shoulder_menu(){
		$rtn = array();
		$home_children = $this->get_children('', array('filter'=>true));
		foreach( $home_children as $page_id ){
			$page_info = $this->get_page_info($page_id);
			if($page_info['category_top_flg']){continue;}
			array_push($rtn, $page_id);
		}
		return $rtn;
	}//get_shoulder_menu()

	/**
	 * ページ情報を取得する。
	 * 
	 * このメソッドは、指定したページの情報を連想配列で返します。対象のページは第1引数にパスまたはページIDで指定します。
	 * 
	 * カレントページの情報を取得する場合は、代わりに `$px->site()->get_current_page_info()` が使用できます。
	 * 
	 * パスで指定したページの情報を取得する例 :
	 * <pre>&lt;?php
	 * // ページ &quot;/aaa/bbb.html&quot; のページ情報を得る
	 * $page_info = $px-&gt;site()-&gt;get_page_info('/aaa/bbb.html');
	 * var_dump( $page_info );
	 * ?&gt;</pre>
	 * 
	 * ページIDで指定したページの情報を取得する例 :
	 * <pre>&lt;?php
	 * // トップページのページ情報を得る
	 * // (トップページのページIDは必ず空白の文字列)
	 * $page_info = $px-&gt;site()-&gt;get_page_info('');
	 * var_dump( $page_info );
	 * ?&gt;</pre>
	 * 
	 * @param string $path 取得するページのパス または ページID。省略時、カレントページから自動的に取得します。
	 * @param string $key 取り出す単一要素のキー。省略時はすべての要素を含む連想配列が返されます。省略可。
	 * @return mixed 単一ページ情報を格納する連想配列、`$key` が指定された場合は、その値のみ。
	 */
	public function get_page_info( $path, $key = null ){
		if( is_null($path) ){ return null; }
		if( array_key_exists($path, $this->sitemap_id_map) && !is_null($this->sitemap_id_map[$path]) ){
			//ページIDで指定された場合、パスに置き換える
			$path = $this->sitemap_id_map[$path];
		}
		if( !preg_match( '/^(?:\/|[a-zA-Z0-9]+\:)/s', $path ) ){
			// $path が相対パスで指定された場合
			preg_match( '/(\/)$/s', $path, $tmp_matched );
			$path = $this->px->fs()->get_realpath( dirname( $this->px->req()->get_request_file_path() ).'/'.$path );
			if( @strlen($tmp_matched[1]) ){ $path .= $tmp_matched[1]; }
			$path = $this->px->fs()->normalize_path($path);
			unset( $tmp_matched );
		}
		$path = preg_replace('/\/'.$this->px->get_directory_index_preg_pattern().'((?:\?|\#).*)?$/si','/$1',$path);//directory_index を一旦省略

		$tmp_path = $path;
		if( !array_key_exists($path, $this->sitemap_id_map) || is_null( $this->sitemap_array[$path] ) ){
			foreach( $this->px->get_directory_index() as $index_file_name ){
				$tmp_path = preg_replace('/\/((?:\?|\#).*)?$/si','/'.$index_file_name.'$1',$path);//省略された index.html を付加。
				if( !is_null( @$this->sitemap_array[$tmp_path] ) ){
					break;
				}
			}
		}
		$path = $tmp_path;
		$parsed_url = parse_url($path);
		unset($tmp_path);

		if( is_null( @$this->sitemap_array[$path] ) ){
			//  サイトマップにズバリなければ、
			//  ダイナミックパスを検索する。
			$sitemap_dynamic_path = $this->get_dynamic_path_info( $path );
			if( is_array( $sitemap_dynamic_path ) ){
				$path = $sitemap_dynamic_path['path_original'];
			}
		}
		$args = func_get_args();

		switch( $this->get_path_type($path) ){
			case 'full_url':
			case 'javascript':
			case 'anchor':
				break;
			default:
				$path = preg_replace( '/\/$/si' , '/'.$this->px->get_directory_index_primary() , $path );
				break;
		}

		if( is_null( @$this->sitemap_array[$path] ) ){
			//  サイトマップにズバリなければ、
			//  引数からパラメータを外したパスだけで再検索
			$path = @$parsed_url['path'];
		}

		$rtn = @$this->sitemap_array[$path];
		if( !is_array($rtn) ){ return null; }
		if( !strlen( @$rtn['title_breadcrumb'] ) ){ $rtn['title_breadcrumb'] = $rtn['title']; }
		if( !strlen( @$rtn['title_h1'] ) ){ $rtn['title_h1'] = $rtn['title']; }
		if( !strlen( @$rtn['title_label'] ) ){ $rtn['title_label'] = $rtn['title']; }
		if( !strlen( @$rtn['title_full'] ) ){ $rtn['title_full'] = $rtn['title'].' | '.$this->px->conf()->name; }
		if( count($args) >= 2 ){
			$rtn = $rtn[$args[1]];
		}
		return $rtn;
	}

	/**
	 * ページ情報をセットする。
	 * 
	 * @param string $path セットするページのパス または ページID。
	 * @param array $page_info セットするページ情報を格納する連想配列。
	 * @return bool 常に `true`
	 */
	public function set_page_info( $path , $page_info ){
		static $num_auto_pid = 0;
		$path_type = $this->get_path_type($path);
		if( is_null( $path_type ) || $path_type === false ){
			$path = $this->px->req()->get_request_file_path();
			$path_type = $this->get_path_type($path);
		}
		if( is_string( $path_type ) ){
			//  $path がスラドメされている場合に index.html を付加
			$path = preg_replace( '/\/$/si' , '/'.$this->px->get_directory_index_primary() , $path );
		}

		$before_page_info = $this->get_page_info( $path );
		$current_page_info = $this->get_current_page_info();
		$is_target_current_page = false;
		if( $before_page_info === $current_page_info ){
			$is_target_current_page = true;
		}

		if(!is_array($before_page_info) || ( $before_page_info['path'] != $path && $before_page_info['id'] != $path ) ){
			//まったく新しいページだったら
			$before_page_info = $this->get_current_page_info();
			if( is_string( $path_type ) ){
				//  パスでの指定だった場合
				$before_page_info['path'] = $path;
				if(@!strlen($page_info['id'])){
					//ページIDを動的に発行
					$before_page_info['id'] = ':live_auto_page_id.'.($num_auto_pid++);
				}
			}else{
				//  ページIDでの指定だった場合
				$before_page_info['id'] = $path;
				$page_info['id'] = $path;
			}
		}elseif(!is_null($this->sitemap_id_map[$path])){
			//既存ページをページIDで指定されていたら
			$before_page_info['id'] = $path;
		}else{
			//既存ページをパスで指定されていたら
			$before_page_info['path'] = $path;
			if(!strlen($page_info['id'])){
				//ページIDを動的に発行
				$before_page_info['id'] = ':live_auto_page_id.'.($num_auto_pid++);
			}
		}
		$tmp_array = $before_page_info;

		if( @strlen($page_info['title']) && $page_info['title']!=@$tmp_array['title'] ){
			//タイトルの指定があったら
			//タイトル系オプション値も自動で振りたいので、あえて消す。
			unset( $tmp_array['title_breadcrumb'] );
			unset( $tmp_array['title_h1'] );
			unset( $tmp_array['title_label'] );
			unset( $tmp_array['title_full'] );
		}

		//  パンくず欄の先頭が > から始まっていた場合、削除
		$tmp_array['logical_path'] = preg_replace( '/^\>+/s' , '' , @$tmp_array['logical_path'] );

		//  指定値を反映
		foreach( $page_info as $key=>$val ){
			$tmp_array[$key] = $val;
		}

		if( !strlen( $tmp_array['title'] ) ){
			$tmp_array['title'] = $tmp_array['path'];
		}
		if( is_null( $tmp_array['id'] ) ){
			$tmp_array['id'] = ':live_auto_page_id.'.($num_auto_pid++);
		}

		//  サイトマップに登録
		$this->sitemap_array[$tmp_array['path']] = $tmp_array;
		$this->sitemap_id_map[$tmp_array['id']] = $tmp_array['path'];

		//  ページツリーキャッシュを削除
		$parent = $this->get_page_info_by_id( $this->get_parent( $tmp_array['path'] ) );
		$this->sitemap_page_tree[$parent['path']] = null;

		//  パブリッシュ対象にリンクを追加
		$this->px->add_relatedlink( $this->px->theme()->href($tmp_array['path']) );

		// カレントページにレイアウトの指示があったら、テーマに反映する。
		if( $is_target_current_page && @strlen($page_info['layout']) ){
			$this->px->theme()->set_layout_id( $page_info['layout'] );
		}

		return true;
	}//set_page_info()

	/**
	 * ページIDからページ情報を得る。
	 * 
	 * @param string $page_id 取得するページのページID
	 * @return array ページ情報を格納する連想配列
	 */
	public function get_page_info_by_id( $page_id ){
		return $this->get_page_info($page_id);
	}

	/**
	 * パスからページIDを得る。
	 * 
	 * @param string $path 取得するページのパス
	 * @return string `$path` に対応するページID
	 */
	public function get_page_id_by_path( $path ){
		$page_info = $this->get_page_info($path);
		return $page_info['id'];
	}

	/**
	 * ページIDからパスを得る。
	 * 
	 * @param string $page_id 取得するページのページID
	 * @return string `$page_id` に対応するパス
	 */
	public function get_page_path_by_id( $page_id ){
		$page_info = $this->get_page_info($page_id);
		return $page_info['path'];
	}



	/**
	 * 現在のページの情報を得る。
	 * 
	 * 例：
	 * <pre>&lt;?php
	 * // カレントページのページ情報を得る
	 * $page_info = $px-&gt;site()-&gt;get_current_page_info();
	 * var_dump( $page_info );
	 * ?&gt;</pre>
	 * 
	 * @param string $key 取り出す情報のキー
	 * @return array カレントページのページ情報を格納する連想配列
	 */
	public function get_current_page_info( $key = null ){
		$current_path = $this->px->req()->get_request_file_path();
		$args = func_get_args();
		if( count($args) ){
			return $this->get_page_info( $current_path, $args[0] );
		}
		return $this->get_page_info( $current_path );
	}

	/**
	 * 現在のページの情報をセットする。
	 * 
	 * @param array $page_info セットするページ情報を格納する連想配列。
	 * @return bool 常に `true`
	 */
	public function set_current_page_info( $page_info ){
		$current_path = $this->px->req()->get_request_file_path();
		return $this->set_page_info( $current_path, $page_info );
	}

	/**
	 * パスがダイナミックパスにマッチするか調べる。
	 * 
	 * @param string $path 調べる対象のパス文字列
	 * @return bool マッチするダイナミックパスが見つかったら `true`、なければ `false` を返します。
	 */
	public function is_match_dynamic_path( $path ){
		foreach( $this->sitemap_dynamic_paths as $sitemap_dynamic_path ){
			//ダイナミックパスを検索
			if( preg_match( $sitemap_dynamic_path['preg'] , $path ) ){
				return true;
			}
		}
		return false;
	}

	/**
	 * ダイナミックパス情報を得る。
	 * 
	 * @param string $path 対象のパス
	 * @return string|bool 見つかった場合に、ダイナミックパスを、見つからない場合に `false` を返します。
	 */
	public function get_dynamic_path_info( $path ){
		foreach( $this->sitemap_dynamic_paths as $sitemap_dynamic_path ){
			//ダイナミックパスを検索
			if( $sitemap_dynamic_path['path_original'] == $path ){
				return $sitemap_dynamic_path;
			}
			if( preg_match( $sitemap_dynamic_path['preg'] , $path ) ){
				return $sitemap_dynamic_path;
			}
		}
		return false;
	}

	/**
	 * ダイナミックパスに値をバインドする。
	 *
	 * @param string $dynamic_path ダイナミックパス文字列
	 * @param array $params パラメータを格納する連想配列
	 * @return string パラメータをバインドして完成したパス
	 */
	public function bind_dynamic_path_param( $dynamic_path , $params = array() ){
		$path = '';
		while( 1 ){
			if( !preg_match( '/^(.*?)\{(\$|\*)([a-zA-Z0-9\_\-]*)\}(.*)$/s' , $dynamic_path , $tmp_matched ) ){
				$path .= $dynamic_path;
				break;
			}
			$path .= $tmp_matched[1];
				// ※注意: このメソッドでは、無名のパラメータもバインドする。
				//   (明示的に使用されるメソッドなので)
			if( !is_null( $params[$tmp_matched[3]] ) ){
				$path .= $params[$tmp_matched[3]];
			}else{
				$path .= $tmp_matched[3];
			}
			$dynamic_path = $tmp_matched[4];
			continue;
		}
		unset($dynamic_path , $tmp_matched);
		$path = preg_replace('/\/$/si','/'.$this->px->get_directory_index_primary(),$path); // index.htmlをつける
		return $path;
	}//bind_dynamic_path_param()

	/**
	 * 子階層のページの一覧を取得する。
	 * 
	 * このメソッドは、指定したページの子階層のページの一覧を返します。`$path` を省略した場合は、カレントページのパスを起点に一覧を抽出します。
	 * 
	 * カレントページの子階層のリンクを作成する例 :
	 * <pre>&lt;?php
	 * // カレントページの子階層のリンクを作成する
	 * $children = $px-&gt;site()-&gt;get_children();
	 * print '&lt;ul&gt;';
	 * foreach( $children as $child ){
	 * 	print '&lt;li&gt;'.$px-&gt;theme()-&gt;mk_link($child).'&lt;/li&gt;';
	 * }
	 * print '&lt;/ul&gt;';
	 * ?&gt;</pre>
	 * 
	 * カレントページの子階層のリンクを、list_flg を無視してすべて表示する例 :
	 * <pre>&lt;?php
	 * // カレントページの子階層のリンクを作成する
	 * // (list_flg を無視してすべて表示する)
	 * $children = $px-&gt;site()-&gt;get_children(null, array('filter'=&gt;false));
	 * print '&lt;ul&gt;';
	 * foreach( $children as $child ){
	 * 	print '&lt;li&gt;'.$px-&gt;theme()-&gt;mk_link($child).'&lt;/li&gt;';
	 * }
	 * print '&lt;/ul&gt;';
	 * ?&gt;</pre>
	 * 
	 * @param string $path 起点とするページのパス または ページID。省略時、カレントページから自動的に取得します。
	 * @param array $opt オプション(省略可)
	 * <dl>
	 *   <dt>$opt['filter'] (初期値: `true`)</dt>
	 *     <dd>フィルターの有効/無効を切り替えます。`true` のとき有効、`false`のとき無効となります。フィルターが有効な場合、サイトマップで `list_flg` が `0` のページが一覧から除外されます。</dd>
	 * </dl>
	 * @return array ページの一覧
	 */
	public function get_children( $path = null, $opt = array() ){
		if( is_null( $path ) ){
			$path = $this->px->req()->get_request_file_path();
		}
		$filter = true;
		if(!is_null(@$opt['filter'])){ $filter = !empty($opt['filter']); }

		$page_info = $this->get_page_info( $path );

		if( $filter && is_array( @$this->sitemap_page_tree[$page_info['path']]['children'] ) ){
			//  ページキャッシュツリーがすでに作られている場合
			return $this->sitemap_page_tree[$page_info['path']]['children'];
		}
		if( !$filter && is_array( @$this->sitemap_page_tree[$page_info['path']]['children_all'] ) ){
			//  ページキャッシュツリーがすでに作られている場合
			return $this->sitemap_page_tree[$page_info['path']]['children_all'];
		}

		$tmp_children_orderby_manual = array();
		$tmp_children_orderby_auto = array();
		$tmp_children_orderby_listed_manual = array();
		$tmp_children_orderby_listed_auto = array();

		// $current_layer = '';
		// if( strlen( trim($page_info['id']) ) ){
		// 	$tmp_breadcrumb = explode( '>', $page_info['logical_path'].'>'.$page_info['id'] );
		// 	foreach( $tmp_breadcrumb as $tmp_path ){
		// 		if( !strlen($tmp_path) ){continue;}
		// 		$tmp_page_info = $this->get_page_info( trim($tmp_path) );
		// 		$current_layer .= '>'.$tmp_page_info['id'];
		// 	}
		// }
		// unset($tmp_breadcrumb,$tmp_path,$tmp_page_info);

		foreach( $this->get_sitemap() as $row ){
			if( !strlen($row['id']) ){
				continue;
			}
			// if($filter){
			// 	if( !$row['list_flg'] ){
			// 		continue;
			// 	}
			// }

			// $target_layer = '';
			$parent_page_id = '';
			if( strlen( trim($row['id']) ) ){
				$tmp_breadcrumb = @explode( '>', $row['logical_path'] );
				$tmp_page_info = $this->get_page_info( trim($tmp_breadcrumb[count($tmp_breadcrumb)-1]) );
				$parent_page_id = trim($tmp_page_info['id']);

				// foreach( $tmp_breadcrumb as $tmp_path ){
				// 	if( !strlen($tmp_path) ){continue;}
				// 	$tmp_page_info = $this->get_page_info( trim($tmp_path) );
				// 	$target_layer .= '>'.$tmp_page_info['id'];
				// }
			}
			unset($tmp_breadcrumb,$tmp_path,$tmp_page_info);

			if( $page_info['id'] == $parent_page_id ){
				if(@strlen($row['orderby'])){
					array_push( $tmp_children_orderby_manual , $row['id'] );
				}else{
					array_push( $tmp_children_orderby_auto , $row['id'] );
				}
				if( $row['list_flg'] ){
					if(@strlen($row['orderby'])){
						array_push( $tmp_children_orderby_listed_manual , $row['id'] );
					}else{
						array_push( $tmp_children_orderby_listed_auto , $row['id'] );
					}
				}
			}
		}

		//  ページキャッシュを作成しなおす
		usort( $tmp_children_orderby_listed_manual , array( $this , 'usort_sitemap' ) );
		$this->sitemap_page_tree[$page_info['path']]['children'] = array_merge( $tmp_children_orderby_listed_manual , $tmp_children_orderby_listed_auto );
		usort( $tmp_children_orderby_manual , array( $this , 'usort_sitemap' ) );
		$this->sitemap_page_tree[$page_info['path']]['children_all'] = array_merge( $tmp_children_orderby_manual , $tmp_children_orderby_auto );

		//  return value
		$rtn = null;
		if($filter){
			$rtn = $this->sitemap_page_tree[$page_info['path']]['children'];
		}else{
			$rtn = $this->sitemap_page_tree[$page_info['path']]['children_all'];
		}

		return $rtn;
	}//get_children()

	/**
	 * ページ情報の配列を並び替える。
	 * 
	 * @param string $a 比較対象1のページID
	 * @param string $b 比較対象2のページID
	 * @return int 並び順の前後関係 (`1`|`0`|`-1`)
	 */
	private function usort_sitemap( $a , $b ){
		$page_info_a = $this->get_page_info( $a );
		$page_info_b = $this->get_page_info( $b );
		$orderby_a = $page_info_a['orderby'];
		$orderby_b = $page_info_b['orderby'];
		if( strlen( $orderby_a ) && !strlen( $orderby_b ) ){
			return	-1;
		}elseif( strlen( $orderby_b ) && !strlen( $orderby_a ) ){
			return	1;
		}elseif( $orderby_a < $orderby_b ){
			return	-1;
		}elseif( $orderby_a > $orderby_b ){
			return	1;
		}
		return	0;
	}//usort_sitemap()

	/**
	 * 同じ階層のページの一覧を取得する。
	 * 
	 * @param string $path 起点とするページのパス または ページID。省略時、カレントページから自動的に取得します。
	 * @param array $opt オプション(省略可)
	 * @return array ページの一覧
	 */
	public function get_bros( $path = null, $opt = array() ){
		if( is_null( $path ) ){
			$path = $this->px->req()->get_request_file_path();
		}
		$page_info = $this->get_page_info($path);
		if( !strlen($page_info['id']) ){
			//トップページの兄弟はトップページだけ。
			return array('');
		}
		$parent = $this->get_parent( $path );
		$bros = $this->get_children( $parent, $opt );
		return $bros;
	}//get_bros()

	/**
	 * 同じ階層の次のページのIDを取得する。
	 * 
	 * @param string $path 起点とするページのパス または ページID。省略時、カレントページから自動的に取得します。
	 * @param array $opt オプション(省略可)
	 * @return string|bool ページID。存在しない場合は `false`を返します。
	 */
	public function get_bros_next( $path = null, $opt = array() ){
		if( is_null( $path ) ){
			$path = $this->px->req()->get_request_file_path();
		}
		$filter = true;
		if(!is_null(@$opt['filter'])){ $filter = !empty($opt['filter']); }

		$bros = $this->get_bros($path,$opt);
		$page_info = $this->get_page_info($path);
		if( !strlen($page_info['id']) ){
			//トップページの次の兄弟はいない。
			return false;
		}

		$num = 0;
		foreach($bros as $num=>$row){
			if( $row == $page_info['id'] ){
				break;
			}
		}
		for($i = $num+1; @!is_null($bros[$i]); $i ++){
			if(is_null($bros[$i])){
				return false;
			}
			if($filter===false || $this->get_page_info($bros[$i], 'layout') != 'popup' && $this->get_path_type($this->get_page_info($bros[$i], 'path')) != 'alias' ){
				return $bros[$i];
			}
		}
		return false;
	}//get_bros_next()

	/**
	 * 同じ階層の前のページのIDを取得する。
	 * 
	 * @param string $path 起点とするページのパス または ページID。省略時、カレントページから自動的に取得します。
	 * @param array $opt オプション(省略可)
	 * @return string|bool ページID。存在しない場合は `false`を返します。
	 */
	public function get_bros_prev( $path = null, $opt = array() ){
		if( is_null( $path ) ){
			$path = $this->px->req()->get_request_file_path();
		}
		$filter = true;
		if(!is_null(@$opt['filter'])){ $filter = !empty($opt['filter']); }

		$bros = $this->get_bros($path,$opt);
		$page_info = $this->get_page_info($path);
		if( !strlen($page_info['id']) ){
			//トップページの前の兄弟はいない。
			return false;
		}

		$num = 0;
		foreach($bros as $num=>$row){
			if( $row == $page_info['id'] ){
				break;
			}
		}
		for($i = $num-1; @!is_null($bros[$i]); $i --){
			if(is_null($bros[$i])){
				return false;
			}
			if($filter===false || $this->get_page_info($bros[$i], 'layout') != 'popup' && $this->get_path_type( $this->get_page_info($bros[$i], 'path') ) != 'alias' ){
				return $bros[$i];
			}
		}
		return false;
	}//get_bros_prev()

	/**
	 * 次のページのIDを取得する。
	 * 
	 * @param string $path 起点とするページのパス または ページID。省略時、カレントページから自動的に取得します。
	 * @param array $opt オプション(省略可)
	 * <dl>
	 *   <dt>$opt['filter'] (初期値: `true`)</dt>
	 *     <dd>フィルターの有効/無効を切り替えます。`true` のとき有効、`false`のとき無効となります。フィルターが有効な場合、エイリアスが除外され、さらにその次のページを探します。</dd>
	 *   <dt>$opt['skip_children'] (初期値: `false`)</dt>
	 *     <dd>子供をスキップするか。`true` のときスキップ、`false`のとき子供も対象とします。これは内部で再帰的に実行する際の無限ループを避けるためのフラグとして設けられました。通常はこれを指定する必要はありません。</dd>
	 * </dl>
	 * @return string|bool ページID。存在しない場合は `false`を返します。
	 */
	public function get_next( $path = null, $opt = array() ){
		if( is_null( $path ) ){
			$path = $this->px->req()->get_request_file_path();
		}
		$filter = true;
		if(!is_null(@$opt['filter'])){
			$filter = !@empty($opt['filter']);
		}
		$skip_children = false;
		if(!is_null(@$opt['skip_children'])){
			$skip_children = !@empty($opt['skip_children']);
		}

		$fin = null;

		$children = $this->get_children($path,$opt);
		$page_bros_next = $this->get_bros_next($path, $opt);
		$parent = $this->get_parent($path);

		//  子供がいたら
		if( !$skip_children && is_array($children) && count($children) ){
			$fin = $children[0];
		}elseif( $page_bros_next!==false ){
			//  次の兄弟がいたら、そのひとがnext
			$fin = $page_bros_next;
		}elseif( $parent !== false ){
			//  親の兄弟
			$fin = $this->get_next($parent, array('skip_children'=>true,'filter'=>$filter));
		}else{
			return false;
		}


		// 除外条件 検証
		if(
			$this->get_page_info($fin, 'layout') == 'popup' // <- popupはとばす (行き止まりができるので)
		){
			return $this->get_next($fin, $opt);
		}

		// フィルター検証
		if( $filter===true &&
			(
				$this->get_path_type($this->get_page_info($fin, 'path')) == 'alias' // <- エイリアスはとばす (ループが起きるので)
			)
		){
			return $this->get_next($fin, $opt);
		}

		return $fin;
	}

	/**
	 * 前のページのIDを取得する。
	 * 
	 * @param string $path 起点とするページのパス または ページID。省略時、カレントページから自動的に取得します。
	 * @param array $opt オプション(省略可)
	 * <dl>
	 *   <dt>$opt['filter'] (初期値: `true`)</dt>
	 *     <dd>フィルターの有効/無効を切り替えます。`true` のとき有効、`false`のとき無効となります。フィルターが有効な場合、エイリアスが除外され、さらにその前のページを探します。</dd>
	 * </dl>
	 * @return string|bool ページID。存在しない場合は `false`を返します。
	 */
	public function get_prev( $path = null, $opt = array() ){
		if( is_null( $path ) ){
			$path = $this->px->req()->get_request_file_path();
		}
		$filter = true;
		if(!is_null(@$opt['filter'])){
			$filter = !@empty($opt['filter']);
		}

		$fin = null;
		$current_page = $path;

		//  前の兄弟がいたら、そのひとがprev
		$page_bros_prev = $this->get_bros_prev($current_page,$opt);
		if($page_bros_prev!==false){
			// [兄がいた場合]
			// 前の兄弟の子供を調べる。
			$fin = $page_bros_prev;
			while( $prev_children = $this->get_children($fin, $opt) ){
				if(is_array($prev_children) && count($prev_children)){
					// 該当する子供がいたら末っ子がprev
					$fin = $prev_children[count($prev_children)-1];
					continue;
				}
				break;
			}
		}else{
			// [兄がいない場合]
			// 親を返す
			$parent = $this->get_parent($current_page);
			if($parent===false){
				return false;
			}
			$fin = $parent;
		}

		// 除外条件 検証
		if(
			$this->get_page_info($fin, 'layout') == 'popup' // <- popupはとばす (行き止まりができるので)
		){
			return $this->get_prev($fin, $opt);
		}

		// フィルター検証
		if( $filter===true &&
			(
				$this->get_path_type($this->get_page_info($fin, 'path')) == 'alias' // <- エイリアスはとばす (ループが起きるので)
			)
		){
			return $this->get_prev($fin, $opt);
		}

		return $fin;
	}// get_prev()

	/**
	 * パンくず配列を取得する。
	 * 
	 * @param string $path 起点とするページのパス または ページID。省略時、カレントページから自動的に取得します。
	 * @return array 親ページまでのパンくず階層をあらわす配列。自身を含まない。$pathがトップページを示す場合は、空の配列。
	 */
	public function get_breadcrumb_array( $path = null ){
		if( is_null( $path ) ){
			$path = $this->px->req()->get_request_file_path();
		}
		$page_info = $this->get_page_info( $path );
		if( !strlen($page_info['id']) ){return array();}

		$rtn = array('');
		$tmp_breadcrumb = @explode( '>', $page_info['logical_path'] );
		foreach( $tmp_breadcrumb as $tmp_id ){
			if( !strlen($tmp_id) ){continue;}
			$tmp_page_info = $this->get_page_info( trim($tmp_id) );
			array_push( $rtn , $tmp_page_info['id'] );
		}

		return $rtn;
	}//get_breadcrumb_array()

	/**
	 * ページが、パンくず内に存在しているか調べる。
	 * 
	 * @param string $page_path 調べる対象のページのパス、またはID。
	 * @param string $path 起点とするページのパス または ページID。省略時、カレントページから自動的に取得します。
	 * @return bool 存在している場合に `true`、存在しない場合に `false` を返します。
	 */
	public function is_page_in_breadcrumb( $page_path, $path = null ){
		if( is_null($path) ){
			$path = $this->px->req()->get_request_file_path();
		}
		$breadcrumb = $this->get_breadcrumb_array($path);
		$current_page_id = $this->get_page_id_by_path($path);
		$target_page_id = $this->get_page_id_by_path($page_path);
		if( $current_page_id == $target_page_id ){
			return true;
		}
		foreach( $breadcrumb as $row ){
			if( $target_page_id == $this->get_page_id_by_path($row) ){
				return true;
			}
		}
		return false;
	}// is_page_in_breadcrumb()

	/**
	 * パス文字列を受け取り、種類を判定する。
	 * 
	 * @param string $path 調べるパス
	 * @return string|bool 判定結果。
	 * - `javascript:` から始まる場合 => 'javascript'
	 * - `#:` から始まる場合 => 'anchor'
	 * - `http://` などURLスキーマ名から始まる場合 => 'full_url'
	 * - その他で `alias:` から始まる場合 => 'alias'
	 * - `{$xxxx}` または `{*xxxx}` を含む場合 => 'dynamic'
	 * - `/` から始まる場合 => 'normal'
	 * - どれにも当てはまらない不明な形式の場合に、`false` を返します。
	 */
	public function get_path_type( $path ) {
		if( preg_match( '/^(?:alias[0-9]*\:)?javascript\:/i' , $path ) ) {
			//  javascript: から始まる場合
			//  サイトマップ上での重複を許容するために、
			//  自動的にalias扱いとなることを考慮した正規表現。
			$path_type = 'javascript';
		} else if( preg_match( '/^(?:alias[0-9]*\:)?\#/' , $path ) ) {
			//  # から始まる場合
			//  サイトマップ上での重複を許容するために、
			//  自動的にalias扱いとなることを考慮した正規表現。
			$path_type = 'anchor';
		} else if( preg_match( '/^(?:alias[0-9]*\:)?[a-zA-Z0-90-9]+\:\/\//' , $path ) ) {
			//  http:// などURLスキーマから始まる場合
			//  サイトマップ上での重複を許容するために、
			//  自動的にalias扱いとなることを考慮した正規表現。
			$path_type = 'full_url';
		} else if( preg_match( '/^alias[0-9]*\:/' , $path ) ) {
			//  alias:から始まる場合
			//  サイトマップデータ上でpathは一意である必要あるので、
			//  alias と : の間に、後から連番を降られる。
			//  このため、数字が含まれている場合を考慮した。(@tomk79)
			$path_type = 'alias';
		} else if( preg_match( '/\{(?:\$|\*)(?:[a-zA-Z0-9\_\-]*)\}/' , $path ) ) {
			//  {$xxxx} または {*xxxx} を含む場合(ダイナミックパス)
			$path_type = 'dynamic';
		} else if( preg_match( '/^\//' , $path ) ) {
			//  /から始まる場合
			$path_type = 'normal';
		} else {
			//  どれにも当てはまらない場合はfalseを返す
			$path_type = false;
		}
		return $path_type;
	}//get_path_type()


	/**
	 * 変数を受け取り、PHPのシンタックスに変換する。
	 * 
	 * @param mixed $value 値
	 * @param array $options オプション
	 * <dl>
	 *   <dt>delete_arrayelm_if_null</dt>
	 *     <dd>配列の要素が `null` だった場合に削除。</dd>
	 *   <dt>array_break</dt>
	 *     <dd>配列に適当なところで改行を入れる。</dd>
	 * </dl>
	 * @return string PHPシンタックスに変換された値
	 */
	private static function data2text( $value = null , $options = array() ){

		$RTN = '';
		if( is_array( $value ) ){
			#	配列
			$RTN .= 'array(';
			if( @$options['array_break'] ){ $RTN .= "\n"; }
			$keylist = array_keys( $value );
			foreach( $keylist as $Line ){
				if( @$options['delete_arrayelm_if_null'] && is_null( @$value[$Line] ) ){
					#	配列のnull要素を削除するオプションが有効だった場合
					continue;
				}
				$RTN .= ''.self::data2text( $Line ).'=>'.self::data2text( $value[$Line] , $options ).',';
				if( @$options['array_break'] ){ $RTN .= "\n"; }
			}
			$RTN = preg_replace( '/,(?:\r\n|\r|\n)?$/' , '' , $RTN );
			$RTN .= ')';
			if( @$options['array_break'] ){ $RTN .= "\n"; }
			return	$RTN;
		}

		if( is_int( $value ) ){
			#	数値
			return	$value;
		}

		if( is_float( $value ) ){
			#	浮動小数点
			return	$value;
		}

		if( is_string( $value ) ){
			#	文字列型
			$RTN = '\''.self::escape_singlequote( $value ).'\'';
			$RTN = preg_replace( '/\r\n|\r|\n/' , '\'."\\n".\'' , $RTN );
			$RTN = preg_replace( '/'.preg_quote('<'.'?','/').'/' , '<\'.\'?' , $RTN );
			$RTN = preg_replace( '/'.preg_quote('?'.'>','/').'/' , '?\'.\'>' , $RTN );
			$RTN = preg_replace( '/'.preg_quote('/'.'*','/').'/' , '/\'.\'*' , $RTN );
			$RTN = preg_replace( '/'.preg_quote('*'.'/','/').'/' , '*\'.\'/' , $RTN );
			$RTN = preg_replace( '/<(scr)(ipt)/i' , '<$1\'.\'$2' , $RTN );
			$RTN = preg_replace( '/\/(scr)(ipt)>/i' , '/$1\'.\'$2>' , $RTN );
			$RTN = preg_replace( '/<(sty)(le)/i' , '<$1\'.\'$2' , $RTN );
			$RTN = preg_replace( '/\/(sty)(le)>/i' , '/$1\'.\'$2>' , $RTN );
			$RTN = preg_replace( '/<\!\-\-/i' , '<\'.\'!\'.\'--' , $RTN );
			$RTN = preg_replace( '/\-\->/i' , '--\'.\'>' , $RTN );
			return	$RTN;
		}

		if( is_null( $value ) ){
			#	ヌル
			return	'null';
		}

		if( is_object( $value ) ){
			#	オブジェクト型
			return	'\''.self::escape_singlequote( gettype( $value ) ).'\'';
		}

		if( is_resource( $value ) ){
			#	リソース型
			return	'\''.self::escape_singlequote( gettype( $value ) ).'\'';
		}

		if( is_bool( $value ) ){
			#	ブール型
			if( $value ){
				return	'true';
			}else{
				return	'false';
			}
		}

		return	'\'unknown\'';

	}//data2text()

	/**
	 * 変数をPHPのソースコードに変換する。
	 * 
	 * `include()` に対してそのままの値を返す形になるよう変換する。
	 *
	 * @param mixed $value 値
	 * @param array $options オプション (`self::data2text()`にバイパスされます。`self::data2text()`の項目を参照してください)
	 * @return string `include()` に対して値 `$value` を返すPHPコード
	 */
	private static function data2phpsrc( $value = null , $options = array() ){
		$RTN = '';
		$RTN .= '<'.'?php'."\n";
		$RTN .= '	/'.'* '.@mb_internal_encoding().' *'.'/'."\n";
		$RTN .= '	return '.self::data2text( $value , $options ).';'."\n";
		$RTN .= '?'.'>';
		return	$RTN;
	}

	/**
	 * シングルクオートで囲えるようにエスケープ処理する。
	 *
	 * @param string $text テキスト
	 * @return string エスケープされたテキスト
	 */
	private static function escape_singlequote($text){
		$text = preg_replace( '/\\\\/' , '\\\\\\\\' , $text);
		$text = preg_replace( '/\'/' , '\\\'' , $text);
		return	$text;
	}

}