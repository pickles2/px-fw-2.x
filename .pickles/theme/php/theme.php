<?php
/**
 * theme "pickles"
 */
namespace picklesFramework2;

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
		$theme = new self($px);
		$src = $theme->bind($px);
		$px->bowl()->replace($src, '');
		return true;
	}

	/**
	 * bind content to theme
	 */
	private function bind( $px ){
		$theme = $this;
		ob_start();
		include( $this->path_tpl.$this->page['layout'].'.html' );
		$src = ob_get_clean();
		return $src;
	}


	/**
	 * グローバルナビを自動生成する
	 */
	public function mk_global_menu(){
		$global_menu = $this->px->site()->get_global_menu();
		if( !count($global_menu) ){
			return '';
		}

		$rtn = '';
		$rtn .= '<ul>'."\n";
		foreach( $global_menu as $global_menu_page_id ){
			$rtn .= '<li>'.$this->px->mk_link( $global_menu_page_id );
			$rtn .= $this->mk_global_sub_menu( $global_menu_page_id );
			$rtn .= '</li>'."\n";
		}
		$rtn .= '</ul>'."\n";
		return $rtn;
	}
	private function mk_global_sub_menu( $parent_page_id ){
		$rtn = '';
		$children = $this->px->site()->get_children( $parent_page_id );
		if( count($children) && $this->px->site()->get_category_top() == $parent_page_id ){
			$rtn .= '<ul>'."\n";
			foreach( $children as $child ){
				$rtn .= '<li>'.$this->px->mk_link( $child );
				$rtn .= $this->mk_global_sub_menu( $child );
				$rtn .= '</li>'."\n";
			}
			$rtn .= '</ul>'."\n";
		}
		return $rtn;
	}

}