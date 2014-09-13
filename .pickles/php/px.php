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
	private $fs;

	/**
	 * constructor
	 * @param string $path_homedir
	 */
	public function __construct( $path_homedir ){
		$this->path_homedir = $path_homedir;
		$this->conf = json_decode( file_get_contents( $this->path_homedir.DIRECTORY_SEPARATOR.'config.json' ) );

		$this->fs = new \tomk79\filesystem( $this->conf );
		$this->path_homedir = $this->fs->get_realpath($this->path_homedir).'/';

var_dump($this->conf);
var_dump(0777);
var_dump(octdec( "0777" ));

	}

}
