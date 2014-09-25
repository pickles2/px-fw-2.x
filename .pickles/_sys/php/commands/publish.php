<?php
/**
 * PX Commands "publish"
 */
namespace pickles\commands;

/**
 * PX Commands "publish"
 */
class publish{

	private $px;
	private $path_homedir, $path_docroot;

	/**
	 * Starting function
	 */
	public static function funcs_starting( $px ){
		$pxcmd = $px->get_px_command();
		if( $pxcmd[0] != 'publish' ){
			return;
		}
		new self( $px );
		exit;
	}

	/**
	 * constructor
	 */
	public function __construct( $px ){
		header('Content-type: text/plain;');
		$this->path_homedir = $px->fs()->get_realpath( $px->get_path_homedir() ).DIRECTORY_SEPARATOR;
		$this->path_docroot = $px->fs()->get_realpath( $px->get_path_docroot() ).DIRECTORY_SEPARATOR;

		print 'pickles '.$px->get_version().' - publish'."\n";
		print @date('Y-m-d H:i:s')."\n";
		print '------'."\n";
		print 'pickles home directory: '.$this->path_homedir."\n";
		print 'pickles docroot directory: '.$this->path_docroot."\n";
		print '------'."\n";
		print '開発中です。'."\n";
		print '------'."\n";
		print 'end;'."\n";
	}
}
