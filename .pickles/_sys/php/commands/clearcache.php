<?php
/**
 * PX Commands "clearcache"
 */
namespace pickles\commands;

/**
 * PX Commands "clearcache"
 */
class clearcache{

	private $px;
	private $path_homedir, $path_docroot;

	/**
	 * Starting function
	 */
	public static function funcs_starting( $px ){
		$pxcmd = $px->get_px_command();
		if( $pxcmd[0] != 'clearcache' ){
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

		print 'pickles '.$px->get_version().' - clearcache'."\n";
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
