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
	private $conf;
	private $fs, $req, $site;
	private $directory_index;

	/**
	 * constructor
	 * @param string $path_homedir
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
		$this->req = new \tomk79\request( $conf );

		// make instance $site
		$this->site = new site($this);


		// execute content
		$src = self::exec_content( $this->req->get_request_file_path(), $this );

		// execute theme
		$src = self::exec_theme( $src, $this );

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
	public function get_conf(){
		return $this->conf;
	}

	/**
	 * get home directory path
	 */
	public function get_path_homedir(){
		return $this->path_homedir;
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
	 * execute content
	 * @return string Content source.
	 */
	private static function exec_content( $path_content, $px ){
		if( !is_file( './'.$path_content ) ){
			@header('HTTP/1.1 404 NotFound');
			return '<p>404 - File not found.</p>';
		}
		ob_start();
		include( './'.$path_content );
		$src = ob_get_clean();
		return $src;
	}

	/**
	 * execute theme
	 * @return string Finalized source.
	 */
	private static function exec_theme( $src_content, $px ){
		ob_start(); ?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8" />
		<title><?= htmlspecialchars($px->site()->get_page_info( $px->req()->get_request_file_path(), 'title')); ?> | Pickles 2</title>
	</head>
	<body>
		<h1><?= htmlspecialchars($px->site()->get_page_info( $px->req()->get_request_file_path(), 'title')); ?></h1>
		<div class="contents">
<?= $src_content; ?>

		</div>
	</body>
</html>
<?php
		$src = ob_get_clean();
		return $src;
	}
}
