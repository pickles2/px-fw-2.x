<?php
/**
 * theme "pickles"
 */
namespace picklesFramework2\theme;

/**
 * theme "pickles" class
 */
class theme{

	/**
	 * objects
	 */
	private $px;

	/**
	 * テーマテンプレートの格納先パス
	 */
	private $path_tpl;

	/**
	 * current_page_info
	 */
	private $page;

	/**
	 * constructor
	 * @param object $px $px object
	 */
	public function __construct($px){
		$this->px = $px;
		$this->path_tpl = $px->fs()->get_realpath(__DIR__.'/../').DIRECTORY_SEPARATOR;
		$this->page = $this->px->site()->get_current_page_info();
		if( !strlen( $this->page['layout'] ?? '' ) ){
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
	 * @param object $px $px object
	 * @return bool true
	 */
	public static function exec( $px ){
		$theme = new self($px);
		$src = $theme->bind($px);
		$px->bowl()->replace($src, '');
		return true;
	}

	/**
	 * bind content to theme
	 * @param object $px $px object
	 * @return string HTML Source code
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
	 * @return string HTML Source code
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
			$rtn .= $this->mk_sub_menu( $global_menu_page_id );
			$rtn .= '</li>'."\n";
		}
		$rtn .= '</ul>'."\n";
		return $rtn;
	}
	/**
	 * ショルダーナビを自動生成する
	 * @return string HTML Source code
	 */
	public function mk_shoulder_menu(){
		$shoulder_menu = $this->px->site()->get_shoulder_menu();
		if( !count($shoulder_menu) ){
			return '';
		}

		$rtn = '';
		$rtn .= '<ul>'."\n";
		foreach( $shoulder_menu as $shoulder_menu_page_id ){
			$rtn .= '<li>'.$this->px->mk_link( $shoulder_menu_page_id );
			$rtn .= $this->mk_sub_menu( $shoulder_menu_page_id );
			$rtn .= '</li>'."\n";
		}
		$rtn .= '</ul>'."\n";
		return $rtn;
	}
	/**
	 * 指定されたページの小階層のメニューを展開する
	 *
	 * @param string $parent_page_id 親ページのページID
	 * @return string ページリストのHTMLソース
	 */
	public function mk_sub_menu( $parent_page_id ){
		$rtn = '';
		$children = $this->px->site()->get_children( $parent_page_id );
		if( count($children) ){
			$rtn .= '<ul>'."\n";
			foreach( $children as $child ){
				$rtn .= '<li>'.$this->px->mk_link( $child );
				if( $this->px->site()->is_page_in_breadcrumb($child) ){//←カレントページ以外の下層は辿らない
					$rtn .= $this->mk_sub_menu( $child );//←再帰的呼び出し
				}
				$rtn .= '</li>'."\n";
			}
			$rtn .= '</ul>'."\n";
		}
		return $rtn;
	}

	/**
	 * グローバルナビを自動生成する
	 * @return string HTML Source code
	 */
	public function mk_megafooter_menu(){
		$global_menu = $this->px->site()->get_global_menu();
		if( !count($global_menu) ){
			return '';
		}

		$rtn = '';
		$rtn .= '<ul>'."\n";
		foreach( $global_menu as $global_menu_page_id ){
			$rtn .= '<li>'.$this->px->mk_link( $global_menu_page_id );
			$children = $this->px->site()->get_children( $global_menu_page_id );
			if( count( $children ) ){
				$rtn .= '<ul>'."\n";
				foreach( $children as $child_page_id ){
					$rtn .= '<li>'.$this->px->mk_link( $child_page_id );
					$rtn .= '</li>'."\n";
				}
				$rtn .= '</ul>'."\n";
			}
			$rtn .= '</li>'."\n";
		}
		$rtn .= '</ul>'."\n";
		return $rtn;
	}

	/**
	 * パンくずを自動生成する
	 * @return string HTML Source code
	 */
	public function mk_breadcrumb(){
		$breadcrumb = $this->px->site()->get_breadcrumb_array();
		$rtn = '';
		$rtn .= '<ul>';
		foreach( $breadcrumb as $pid ){
			$rtn .= '<li>'.$this->px->mk_link( $pid, array('label'=>htmlspecialchars(''.$this->px->site()->get_page_info($pid, 'title_breadcrumb')), 'current'=>false) ).'</li>';
		}
		$rtn .= '<li><span>'.htmlspecialchars( ''.$this->px->site()->get_current_page_info('title_breadcrumb') ).'</span></li>';
		$rtn .= '</ul>';
		return $rtn;
	}

}
