<?php
/**
 * class sitemap_cache_insert
 *
 * @author Tomoya Koyanagi <tomk79@gmail.com>
 */
namespace picklesFramework2\helpers;

/**
 * Sitemap cache insert helper
 *
 * @author Tomoya Koyanagi <tomk79@gmail.com>
 */
class sitemap_cache_insert {
	/**
	 * Picklesオブジェクト
	 * @access private
	 */
	private $px;
	/**
	 * PDOインスタンス
	 * $sitemap_page_tree のキャッシュにSQLiteを使用するためのデータベース。
	 * @access private
	 */
	private $pdo;

    /** $sth */
	private $sth;

	/**
	 * Constructor
	 *
	 * @param object $px Picklesオブジェクト
	 * @param object $pdo PDOオブジェクト
	 * @param array $sitemap_definition_keys サイトマップ定義のキーの配列
	 */
	public function __construct( $px, $pdo, $sitemap_definition_keys ){
		$this->px = $px;
		$this->pdo = $pdo;

		if( $this->pdo !== false ){
			// INSERT文をストア
			ob_start(); ?>
INSERT INTO sitemap( <?php
foreach( $sitemap_definition_keys as $sitemap_definition_key ){
	if( !preg_match('/^[0-9a-zA-Z\_]{1,30}$/', $sitemap_definition_key) ){
		// カスタムカラムのカラム名が定形外の場合、列を作らない
		continue;
	}
	echo $sitemap_definition_key.',';
}
?>
	____parent_page_id,
	____order,
	____originated_csv,
	____originated_csv_row
)VALUES(<?php
foreach( $sitemap_definition_keys as $sitemap_definition_key ){
	if( !preg_match('/^[0-9a-zA-Z\_]{1,30}$/', $sitemap_definition_key) ){
		// カスタムカラムのカラム名が定形外の場合、列を作らない
		continue;
	}
	echo ':'.$sitemap_definition_key.',';
}
?>
	:____parent_page_id,
	:____order,
	:____originated_csv,
	:____originated_csv_row
);
<?php
			$this->sth = $this->pdo->prepare( ob_get_clean() );
		}

	}

    /**
     * 行を挿入する
     */
    public function insert($values){
        $this->sth->execute($values);
    }

    /**
     * 行の挿入を実行する
     */
    public function flush(){
        // TODO: 実装する
    }
}
