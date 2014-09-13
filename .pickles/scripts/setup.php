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
	private $fs;

	/**
	 * constructor
	 */
	public function __construct( $path_homedir ){
		$this->path_homedir = $path_homedir;
		$this->fs = new \tomk79\filesystem();

	}
}
