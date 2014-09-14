<?php
/**
 * pickles2
 */
namespace pickles;

/**
 * tomk79/pickles2 core class
 * 
 * @author Tomoya Koyanagi <tomk79@gmail.com>
 */
class pickles{
	private $path_homedir;
	private $conf;
	private $fs, $req;

	/**
	 * constructor
	 * @param string $path_homedir
	 */
	public function __construct( $path_homedir ){
		// setup PHP
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

		// var_dump($this->conf);
		// var_dump($this->req->get_user_agent());
		// var_dump($this->req->get_request_file_path());

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
		<title>Pickles 2</title>
	</head>
	<body>
		<h1>Pickles 2</h1>
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
