<?php
/**
 * theme "pickles"
 */
namespace pickles\themes\pickles;

/**
 * theme "pickles" class
 */
class theme{
	private $px;
	private $path_tpl;
	private $page;

	/**
	 * constructor
	 */
	public function __construct($px){
		$this->px = $px;
		$this->path_tpl = $px->fs()->get_realpath(__DIR__.'/../').DIRECTORY_SEPARATOR;
		$this->page = $this->px->site()->get_current_page_info();
		if( @!strlen( $this->page['layout'] ) ){
			$this->page['layout'] = 'default';
		}
		if( !$px->fs()->is_file( $this->path_tpl.$this->page['layout'].'.html' ) ){
			$this->page['layout'] = 'default';
		}
		// $this->px->realpath_plugin_private_cache('/test/abc/test.inc');
		$this->px->fs()->copy_r( __DIR__.'/../theme_files/', $this->px->realpath_plugin_files('/') );
	}

	/**
	 * entry method
	 */
	public static function exec( $px ){
		$self = new self($px);
		$src = $self->bind($px);
		$px->bowl()->replace($src, '');
		return true;
	}

	/**
	 * bind content to theme
	 */
	private function bind( $px ){
		ob_start();
		include( $this->path_tpl.$this->page['layout'].'.html' );
		$src = ob_get_clean();
		return $src;
	}

}