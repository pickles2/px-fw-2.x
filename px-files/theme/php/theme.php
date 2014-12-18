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
			$rtn .= $this->mk_sub_menu( $global_menu_page_id );
			$rtn .= '</li>'."\n";
		}
		$rtn .= '</ul>'."\n";
		return $rtn;
	}
	/**
	 * ショルダーナビを自動生成する
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
	private function mk_sub_menu( $parent_page_id ){
		$rtn = '';
		$children = $this->px->site()->get_children( $parent_page_id );
		if( count($children) && $this->px->site()->is_page_in_breadcrumb( $parent_page_id ) ){
			$rtn .= '<ul>'."\n";
			foreach( $children as $child ){
				$rtn .= '<li>'.$this->px->mk_link( $child );
				$rtn .= $this->mk_sub_menu( $child );
				$rtn .= '</li>'."\n";
			}
			$rtn .= '</ul>'."\n";
		}
		return $rtn;
	}

	/**
	 * パンくずを自動生成する
	 */
	public function mk_breadcrumb(){
		$breadcrumb = $this->px->site()->get_breadcrumb_array();
		$rtn = '';
		$rtn .= '<ul>';
		foreach( $breadcrumb as $pid ){
			$rtn .= '<li>'.$this->px->mk_link( $pid, array('label'=>$this->px->site()->get_page_info($pid, 'title_breadcrumb'), 'current'=>false) ).'</li>';
		}
		$rtn .= '<li><span>'.htmlspecialchars( $this->px->site()->get_current_page_info('title_breadcrumb') ).'</span></li>';
		$rtn .= '</ul>';
		return $rtn;
	}



	#------------------------------------------------------------------------------------------------------------------
	#	カラーユーティリティ

	/**
	 * 16進数の色コードからRGBの10進数を得る。
	 *
	 * @param int|string $txt_hex 16進数色コード
	 * @return array 10進数のRGB色コードを格納した連想配列
	 */
	public function color_hex2rgb( $txt_hex ){
		if( is_int( $txt_hex ) ){
			$txt_hex = dechex( $txt_hex );
			$txt_hex = '#'.str_pad( $txt_hex , 6 , '0' , STR_PAD_LEFT );
		}
		$txt_hex = preg_replace( '/^#/' , '' , $txt_hex );
		if( strlen( $txt_hex ) == 3 ){
			#	長さが3バイトだったら
			if( !preg_match( '/^([0-9a-f])([0-9a-f])([0-9a-f])$/si' , $txt_hex , $matched ) ){
				return	false;
			}
			$matched[1] = $matched[1].$matched[1];
			$matched[2] = $matched[2].$matched[2];
			$matched[3] = $matched[3].$matched[3];
		}elseif( strlen( $txt_hex ) == 6 ){
			#	長さが6バイトだったら
			if( !preg_match( '/^([0-9a-f]{2})([0-9a-f]{2})([0-9a-f]{2})$/si' , $txt_hex , $matched ) ){
				return	false;
			}
		}else{
			return	false;
		}
		$RTN = array(
			"r"=>eval( 'return 0x'.$matched[1].';' ) ,
			"g"=>eval( 'return 0x'.$matched[2].';' ) ,
			"b"=>eval( 'return 0x'.$matched[3].';' ) ,
		);
		return	$RTN;
	}

	/**
	 * RGBの10進数の色コードから16進数を得る。
	 *
	 * @param int $int_r 10進数の色コード(Red)
	 * @param int $int_g 10進数の色コード(Green)
	 * @param int $int_b 10進数の色コード(Blue)
	 * @return string 16進数の色コード
	 */
	public function color_rgb2hex( $int_r , $int_g , $int_b ){
		$hex_r = dechex( $int_r );
		$hex_g = dechex( $int_g );
		$hex_b = dechex( $int_b );
		if( strlen( $hex_r ) > 2 || strlen( $hex_g ) > 2 || strlen( $hex_b ) > 2 ){
			return	false;
		}
		$RTN = '#';
		$RTN .= str_pad( $hex_r , 2 , '0' , STR_PAD_LEFT );
		$RTN .= str_pad( $hex_g , 2 , '0' , STR_PAD_LEFT );
		$RTN .= str_pad( $hex_b , 2 , '0' , STR_PAD_LEFT );
		return	$RTN;
	}

	/**
	 * 色相を調べる。
	 * 
	 * @param int|string $txt_hex 16進数の色コード
	 * @param int $int_round 小数点以下を丸める桁数
	 * @return int 色相値
	 */
	public function color_get_hue( $txt_hex , $int_round = 0 ){
		$int_round = intval( $int_round );
		if( $int_round < 0 ){ return false; }

		$rgb = $this->color_hex2rgb( $txt_hex );
		if( $rgb === false ){ return false; }

		foreach( $rgb as $key=>$val ){
			$rgb[$key] = $val/255;
		}

		$hue = 0;
		if( $rgb['r'] == $rgb['g'] && $rgb['g'] == $rgb['b'] ){//PxFW 0.6.2
			return	0;
		}
		if( $rgb['r'] >= $rgb['g'] && $rgb['g'] >= $rgb['b'] ){
			#	R>G>B
			$hue = 60 * ( ($rgb['g']-$rgb['b'])/($rgb['r']-$rgb['b']) );

		}elseif( $rgb['g'] >= $rgb['r'] && $rgb['r'] >= $rgb['b'] ){
			#	G>R>B
			$hue = 60 * ( 2-( ($rgb['r']-$rgb['b'])/($rgb['g']-$rgb['b']) ) );

		}elseif( $rgb['g'] >= $rgb['b'] && $rgb['b'] >= $rgb['r'] ){
			#	G>B>R
			$hue = 60 * ( 2+( ($rgb['b']-$rgb['r'])/($rgb['g']-$rgb['r']) ) );

		}elseif( $rgb['b'] >= $rgb['g'] && $rgb['g'] >= $rgb['r'] ){
			#	B>G>R
			$hue = 60 * ( 4-( ($rgb['g']-$rgb['r'])/($rgb['b']-$rgb['r']) ) );

		}elseif( $rgb['b'] >= $rgb['r'] && $rgb['r'] >= $rgb['g'] ){
			#	B>R>G
			$hue = 60 * ( 4+( ($rgb['r']-$rgb['g'])/($rgb['b']-$rgb['g']) ) );

		}elseif( $rgb['r'] >= $rgb['b'] && $rgb['b'] >= $rgb['g'] ){
			#	R>B>G
			$hue = 60 * ( 6-( ($rgb['b']-$rgb['g'])/($rgb['r']-$rgb['g']) ) );

		}else{
			return	0;
		}

		if( $int_round ){
			$hue = round( $hue , $int_round );
		}else{
			$hue = intval( $hue );
		}
		return $hue;
	}

	/**
	 * 彩度を調べる。
	 * 
	 * @param int|string $txt_hex 16進数の色コード
	 * @param int $int_round 小数点以下を丸める桁数
	 * @return int 彩度値
	 */
	public function color_get_saturation( $txt_hex , $int_round = 0 ){
		$int_round = intval( $int_round );
		if( $int_round < 0 ){ return false; }

		$rgb = $this->color_hex2rgb( $txt_hex );
		if( $rgb === false ){ return false; }

		sort( $rgb );
		$minval = $rgb[0];
		$maxval = $rgb[2];

		if( $minval == 0 && $maxval == 0 ){
			#	真っ黒だったら
			return	0;
		}

		$saturation = ( 100-( $minval/$maxval * 100 ) );

		if( $int_round ){
			$saturation = round( $saturation , $int_round );
		}else{
			$saturation = intval( $saturation );
		}
		return $saturation;
	}

	/**
	 * 明度を調べる。
	 * 
	 * @param int|string $txt_hex 16進数の色コード
	 * @param int $int_round 小数点以下を丸める桁数
	 * @return int 明度値
	 */
	public function color_get_brightness( $txt_hex , $int_round = 0 ){
		$int_round = intval( $int_round );
		if( $int_round < 0 ){ return false; }

		$rgb = $this->color_hex2rgb( $txt_hex );
		if( $rgb === false ){ return false; }

		sort( $rgb );
		$maxval = $rgb[2];

		$brightness = ( $maxval * 100/255 );

		if( $int_round ){
			$brightness = round( $brightness , $int_round );
		}else{
			$brightness = intval( $brightness );
		}
		return $brightness;
	}

	/**
	 * 16進数のRGBコードからHSB値を得る。
	 * 
	 * @param int|string $txt_hex 16進数の色コード
	 * @param int $int_round 小数点以下を丸める桁数
	 * @return array 色相値、彩度値、明度値を含む連想配列
	 */
	public function color_hex2hsb( $txt_hex , $int_round = 0 ){
		$int_round = intval( $int_round );
		if( $int_round < 0 ){ return false; }

		$hsb = array(
			'h'=>$this->color_get_hue( $txt_hex , $int_round ) ,
			's'=>$this->color_get_saturation( $txt_hex , $int_round ) ,
			'b'=>$this->color_get_brightness( $txt_hex , $int_round ) ,
		);
		return	$hsb;
	}

	/**
	 * RGB値からHSB値を得る。
	 * 
	 * @param int $int_r 10進数の色コード(Red)
	 * @param int $int_g 10進数の色コード(Green)
	 * @param int $int_b 10進数の色コード(Blue)
	 * @param int $int_round 小数点以下を丸める桁数
	 * @return array 色相値、彩度値、明度値を含む連想配列
	 */
	public function color_rgb2hsb( $int_r , $int_g , $int_b , $int_round = 0 ){
		$int_round = intval( $int_round );
		if( $int_round < 0 ){ return false; }

		$txt_hex = $this->color_rgb2hex( $int_r , $int_g , $int_b );
		$hsb = array(
			'h'=>$this->color_get_hue( $txt_hex , $int_round ) ,
			's'=>$this->color_get_saturation( $txt_hex , $int_round ) ,
			'b'=>$this->color_get_brightness( $txt_hex , $int_round ) ,
		);
		return	$hsb;
	}

	/**
	 * HSB値からRGB値を得る。
	 * 
	 * @param int $int_hue 10進数の色相値
	 * @param int $int_saturation 10進数の彩度値
	 * @param int $int_brightness 10進数の明度値
	 * @param int $int_round 小数点以下を丸める桁数
	 * @return array 10進数のRGB色コードを格納した連想配列
	 */
	public function color_hsb2rgb( $int_hue , $int_saturation , $int_brightness , $int_round = 0 ){
		$int_round = intval( $int_round );
		if( $int_round < 0 ){ return false; }

		$int_hue = round( $int_hue%360 , 3 );
		$int_saturation = round( $int_saturation , 3 );
		$int_brightness = round( $int_brightness , 3 );

		$maxval = round( $int_brightness * ( 255/100 ) , 3 );
		$minval = round( $maxval - ( $maxval * $int_saturation/100 ) , 3 );

		$keyname = array( 'r' , 'g' , 'b' );
		if(      $int_hue >=   0 && $int_hue <  60 ){
			$keyname = array( 'r' , 'g' , 'b' );
			$midval = $minval + ( ($maxval - $minval) * ( ($int_hue -  0)/60 ) );
		}elseif( $int_hue >=  60 && $int_hue < 120 ){
			$keyname = array( 'g' , 'r' , 'b' );
			$midval = $maxval - ( ($maxval - $minval) * ( ($int_hue - 60)/60 ) );
		}elseif( $int_hue >= 120 && $int_hue < 180 ){
			$keyname = array( 'g' , 'b' , 'r' );
			$midval = $minval + ( ($maxval - $minval) * ( ($int_hue -120)/60 ) );
		}elseif( $int_hue >= 180 && $int_hue < 240 ){
			$keyname = array( 'b' , 'g' , 'r' );
			$midval = $maxval - ( ($maxval - $minval) * ( ($int_hue -180)/60 ) );
		}elseif( $int_hue >= 240 && $int_hue < 300 ){
			$keyname = array( 'b' , 'r' , 'g' );
			$midval = $minval + ( ($maxval - $minval) * ( ($int_hue -240)/60 ) );
		}elseif( $int_hue >= 300 && $int_hue < 360 ){
			$keyname = array( 'r' , 'b' , 'g' );
			$midval = $maxval - ( ($maxval - $minval) * ( ($int_hue -300)/60 ) );
		}

		$tmp_rgb = array();
		if( $int_round ){
			$tmp_rgb = array(
				$keyname[0]=>round( $maxval , $int_round ) ,
				$keyname[1]=>round( $midval , $int_round ) ,
				$keyname[2]=>round( $minval , $int_round ) ,
			);
		}else{
			$tmp_rgb = array(
				$keyname[0]=>intval( $maxval ) ,
				$keyname[1]=>intval( $midval ) ,
				$keyname[2]=>intval( $minval ) ,
			);
		}
		$rgb = array( 'r'=>$tmp_rgb['r'] , 'g'=>$tmp_rgb['g'] , 'b'=>$tmp_rgb['b'] );
		return	$rgb;
	}
	/**
	 * HSB値から16進数のRGBコードを得る。
	 * 
	 * @param int $int_hue 10進数の色相値
	 * @param int $int_saturation 10進数の彩度値
	 * @param int $int_brightness 10進数の明度値
	 * @param int $int_round 小数点以下を丸める桁数
	 * @return string 16進数の色コード
	 */
	public function color_hsb2hex( $int_hue , $int_saturation , $int_brightness , $int_round = 0 ){
		$rgb = $this->color_hsb2rgb( $int_hue , $int_saturation , $int_brightness , $int_round );
		$hex = $this->color_rgb2hex( $rgb['r'] , $rgb['g'] , $rgb['b'] );
		return	$hex;
	}

}