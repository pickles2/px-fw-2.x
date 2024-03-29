<?php
/**
 * PX Commands "autoindex"
 */
namespace picklesFramework2\processors\autoindex;

/**
 * PX Commands "autoindex"
 */
class autoindex {

	/**
	 * Picklesオブジェクト
	 */
	private $px;

	/**
	 * プラグインオプション
	 */
	private $options;

	/**
	 * 機能別に値を記憶する領域
	 */
	private $func_data_memos = '\\<\\!\\-\\-\\s*autoindex\\s*\\-\\-\\>';

	/**
	 * extensions function
	 *
	 * @param object $px Picklesオブジェクト
	 * @param object $options オプション
	 */
	public static function exec( $px = null, $options = null ) {

		if( count(func_get_args()) <= 1 ){
			return __CLASS__.'::'.__FUNCTION__.'('.( is_array($px) ? json_encode($px) : '' ).')';
		}

		$autoindex = new self( $px, $options );

		//  autoindex
		if( strlen( $autoindex->func_data_memos ?? "" ) ){
			$px->bowl()->each( array($autoindex, 'apply_autoindex') );
		}

		return true;
	}

	/**
	 * constructor
	 *
	 * @param object $px Picklesオブジェクト
	 */
	public function __construct( $px, $options ) {
		$this->px = $px;
		$this->options = json_decode(json_encode($options));

		if (!is_object($this->options)) {
			$this->options = new \stdClass();
		}
		$this->options->class = $this->options->class ?? null;
	}


	/**
	 * ページ内の目次をソースに反映する。
	 * 
	 * マーク文字列 `<!-- autoindex -->` を、自動生成した目次に置き換えます。
	 * 目次は、`<hx>` タグを検出し、自動的に生成されます。
	 * 
	 * マーク文字列が検出されない場合は、何も行わず処理をスキップします。
	 * 
	 * @param string $content 予告状態の コンテンツ HTMLソース
	 * 
	 * @return string 目次が反映されたHTMLソース
	 */
	public function apply_autoindex( $content ) {
		if( !preg_match( '/'.$this->func_data_memos.'/s', $content ) ){
			// アンカーリンクが使われてなかったら、処理しない。
			return $content;
		}

		$tmp_cont = $content;
		$content = '';
		$index = array();
		$indexCounter = array();
		$i = 0;

		while( 1 ){
			set_time_limit(60*30);
			if( !preg_match( '/^(.*?)(<\!\-\-(?:.*?)\-\->|<script(?:\s.*?)?>(?:.*?)<\/script>|<h([2-6])(?:\s.*?)?>(.*?)<\/h\3>)(.*)$/is' , $tmp_cont , $matched ) ){
				$content .= $tmp_cont;
				break;
			}
			$i ++;
			$tmp = array();
			$tmp['label'] = $matched[4];
			$tmp['label'] = strip_tags( $tmp['label'] ); // ラベルからHTMLタグを除去
			// IDに含められない文字をアンダースコアに変換;
			$label_for_anch = $tmp['label'];
			$label_for_anch = preg_replace('/[ #%]/', '_', $label_for_anch);
			$label_for_anch = preg_replace('/[\[\{\<]/', '(', $label_for_anch);
			$label_for_anch = preg_replace('/[\]\}\>]/', ')', $label_for_anch);

			$tmp['anch'] = 'hash_'.($label_for_anch);
			$tmp_count = 1;
			while( array_key_exists($tmp['anch'], $indexCounter) ){
				$tmp_count ++;
				$tmp['anch'] = 'hash_'.$tmp_count.'_'.($label_for_anch);
			}
			$indexCounter[$tmp['anch']] = 1;

			$tmp['headlevel'] = intval($matched[3]);

			$content .= $matched[1];
			if( $tmp['headlevel'] ){
				// 引っかかったのが見出しの場合
				$hxSrc = $matched[2];
				if( preg_match('/^\<h'.preg_quote($tmp['headlevel'],'/').'(.*?)\>(.*)\<\/h'.preg_quote($tmp['headlevel'],'/').'\>$/i', $hxSrc, $matched_hxSrc) ){
					$hxSrc_attrs = $matched_hxSrc[1];
					$hxSrc_innerHTML = $matched_hxSrc[2];
					if( strlen($hxSrc_attrs) ){
						if( preg_match('/\sid\=(\"|\')(.*?)\1/i', ' '.$hxSrc_attrs, $matched_hxSrc_id) ){
							// id属性をもともと持っていたら
							$tmp['anch'] = $matched_hxSrc_id[2];
						}else{
							$hxSrc_attrs = trim($hxSrc_attrs).' id="'.htmlspecialchars($tmp['anch']).'"';
						}
					}else{
						$hxSrc_attrs = trim($hxSrc_attrs).' id="'.htmlspecialchars($tmp['anch']).'"';
					}
					$hxSrc_attrs = ' '.trim($hxSrc_attrs);
					$content .= '<h'.$tmp['headlevel'].$hxSrc_attrs.'>'.$hxSrc_innerHTML.'</h'.$tmp['headlevel'].'>';
					unset($hxSrc_attrs, $hxSrc_innerHTML, $matched_hxSrc, $matched_hxSrc_id);
				}else{
					$content .= '<span';
					$content .= ' id="'.htmlspecialchars($tmp['anch'] ?? "").'"';
					$content .= '></span>';
					$content .= $hxSrc;
				}
			}else{
				$content .= $matched[2];
			}
			$tmp_cont = $matched[5];

			if( $tmp['headlevel'] ){
				// 引っかかったのが見出しの場合
				array_push( $index , $tmp );
			}
		}
		set_time_limit(30);

		$style_ul = (strlen($this->options->class ?? '') ? '' : ' style="margin:0; padding:0 0 0 2em;"');
		$style_li = (strlen($this->options->class ?? '') ? '' : ' style="list-style-type:disc; display:list-item;"');

		$anchorlinks = '';
		$topheadlevel = 2;
		$headlevel = $topheadlevel;

		if ( count( $index ) ) {
			$anchorlinks .= '<!-- == autoindex == -->'."\n";
			$anchorlinks .= '<div '.(strlen($this->options->class ?? '') ? 'class="'.htmlspecialchars($this->options->class).'"' : 'style="margin:2em 5%; border:1px solid #bbb8; padding:1em; background:rgba(124, 124, 124, 0.05); border-radius:5px;"').'>'."\n";
			$anchorlinks .= '<div '.(strlen($this->options->class ?? '') ? 'class="'.htmlspecialchars($this->options->class).'__title"' : 'style="margin:0; padding:0; text-align:center; font-weight:bold;"').'>INDEX</div>';

			foreach ($index as $key=>$row) {
				$csa = $row['headlevel'] - $headlevel;
				$nextLevel = $index[$key+1]['headlevel'] ?? null;
				$nsa = null;
				if( strlen( $nextLevel ?? "" ) ){
					$nsa = $nextLevel - $row['headlevel'];
				}
				$headlevel = $row['headlevel'];
				if( $csa>0 ){
					// いま下がるとき
					if( $key == 0 ){
						$anchorlinks .= '<ul'.$style_ul.'><li'.$style_li.'>';
					}
					for( $i = $csa; $i>0; $i -- ){
						$anchorlinks .= '<ul'.$style_ul.'><li'.$style_li.'>';
					}
				}elseif( $csa<0 ){
					// いま上がるとき
					if( $key == 0 ){
						$anchorlinks .= '<ul'.$style_ul.'><li'.$style_li.'>';
					}
					for( $i = $csa; $i<0; $i ++ ){
						$anchorlinks .= '</li></ul>';
					}
					$anchorlinks .= '</li><li'.$style_li.'>';
				}else{
					// いま現状維持
					if( $key == 0 ){
						$anchorlinks .= '<ul'.$style_ul.'>';
					}
					$anchorlinks .= '<li'.$style_li.'>';
				}
				$anchorlinks .= '<a href="#'.htmlspecialchars($row['anch'] ?? "").'">'.($row['label']).'</a>';
				if( is_null($nsa) ){
					break;
				}elseif( $nsa>0 ){
					// つぎ下がるとき
				}elseif( $nsa<0 ){
					// つぎ上がるとき
				}else{
					// つぎ現状維持
					$anchorlinks .= '</li>'."\n";
				}
			}
			while ($headlevel >= $topheadlevel) {
				$anchorlinks .= '</li></ul>'."\n";
				$headlevel --;
			}
			$anchorlinks .= '</div>'."\n";
			$anchorlinks .= '<!-- / == autoindex == -->'."\n";
		}

		$content = preg_replace( '/'.$this->func_data_memos.'/s' , $anchorlinks , $content );
		return $content;
	}
}
