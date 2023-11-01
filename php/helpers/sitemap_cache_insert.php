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

	/**
	 * サイトマップ定義のキーの配列
	 * @access private
	 */
	private $sitemap_definition_keys;

	/**
	 * INSERT文の VALUES の1行分のテンプレートのリスト
	 * @access private
	 */
	private $strs_insert_values_template_row = array();

	/**
	 * INSERT文の VALUES の1行分のデータのリスト
	 * @access private
	 */
	private $insert_values_row = array();

	/**
	 * INSERT文の前半部分
	 * @access private
	 */
	private $sql_insert_prefix;

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
		$this->sitemap_definition_keys = $sitemap_definition_keys;

		$this->strs_insert_values_template_row = array();
		$this->insert_values_row = array();

		// INSERT文の前半部分を作っておく
		ob_start(); ?>
INSERT INTO sitemap( <?php
foreach( $this->sitemap_definition_keys as $sitemap_definition_key ){
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
)VALUES
<?php
		$this->sql_insert_prefix = ob_get_clean();
	}

	/**
	 * 行を挿入する
	 *
	 * @param array $values 挿入する行データ
	 * @return void
	 */
	public function insert($values){
		$index_num = count($this->strs_insert_values_template_row);

		$new_values = array();
		ob_start(); ?>
(<?php
foreach( $this->sitemap_definition_keys as $sitemap_definition_key ){
	if( !preg_match('/^[0-9a-zA-Z\_]{1,30}$/', $sitemap_definition_key) ){
		// カスタムカラムのカラム名が定形外の場合、列を作らない
		continue;
	}
	echo ':row'.$index_num.'____'.$sitemap_definition_key.',';
	$this->insert_values_row[':row'.$index_num.'____'.$sitemap_definition_key] = $values[':'.$sitemap_definition_key] ?? null;
}
?>
	:row<?= $index_num?>________parent_page_id,
	:row<?= $index_num?>________order,
	:row<?= $index_num?>________originated_csv,
	:row<?= $index_num?>________originated_csv_row
)
<?php
		$this->insert_values_row[':row'.$index_num.'____'.'____parent_page_id'] = $values[':____parent_page_id'] ?? null;
		$this->insert_values_row[':row'.$index_num.'____'.'____order'] = $values[':____order'] ?? null;
		$this->insert_values_row[':row'.$index_num.'____'.'____originated_csv'] = $values[':____originated_csv'] ?? null;
		$this->insert_values_row[':row'.$index_num.'____'.'____originated_csv_row'] = $values[':____originated_csv_row'] ?? null;

		$partial_sql = ob_get_clean();
		array_push($this->strs_insert_values_template_row, trim($partial_sql));
	}

	/**
	 * 行の挿入を実行する
	 *
	 * @return void
	 */
	public function flush(){
		if( $this->pdo === false ){
			return;
		}
		if( !count($this->strs_insert_values_template_row) ){
			return;
		}

		// INSERT文をストア
		set_time_limit(0);
		$sql = $this->sql_insert_prefix.implode(', ', $this->strs_insert_values_template_row).';';
		$sth = $this->pdo->prepare( $sql );
		$sth->execute($this->insert_values_row);

		// リセットする
		set_time_limit(30);
		$this->strs_insert_values_template_row = array();
		$this->insert_values_row = array();

		return;
	}
}
