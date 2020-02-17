<?php
/**
 * config.php template
 */
return call_user_func( function(){

	// initialize

	/** コンフィグオブジェクト */
	$conf = new stdClass;


	// project

	/** サイト名 */
	$conf->name = 'Pickles Framework 2';
	/** コピーライト表記 */
	$conf->copyright = 'Pickles Project';
	/**
	 * スキーマ
	 * 本番環境のスキーマ
	 * (例: http, https)
	 */
	$conf->scheme = null;
	/**
	 * ドメイン
	 * 本番環境のドメイン
	 * (例: www.example.com, 192.168.0.1, www.example.com:8080, etc...)
	 */
	$conf->domain = null;
	/** コンテンツルートディレクトリ */
	$conf->path_controot = '/';


	// paths

	/** トップページのパス(デフォルト "/") */
	$conf->path_top = '/';
	/** パブリッシュ先ディレクトリパス */
	$conf->path_publish_dir = './px-files/dist/';
	/** 公開キャッシュディレクトリ */
	$conf->public_cache_dir = '/caches/';
	/** リソースディレクトリ(各コンテンツに対して1:1で関連付けられる)のパス */
	$conf->path_files = '{$dirname}/{$filename}_files/';
	/** Contents Manifesto のパス */
	$conf->contents_manifesto = '/common/contents_manifesto.ignore.php';


	/**
	 * commands
	 *
	 * Pickles2 が認識するコマンドのパスを設定します。
	 * コマンドのパスが通っていない場合は、絶対パスで設定してください。
	 */
	$conf->commands = new stdClass;
	$conf->commands->php = 'php';

	/** php.ini のパス。主にパブリッシュ時のサブクエリで使用する。 */
	$conf->path_phpini = null;


	/**
	 * directory index
	 *
	 * `directory_index` は、省略できるファイル名のリストを設定します。
	 * 複数指定可能です。
	 *
	 * この一覧にリストされたファイル名に対するリンクは、ファイル名なしのURLと同一視されます。
	 * ファイル名が省略されたアクセス(末尾が `/` の場合)に対しては、
	 * 最初のファイル名と同じものとして処理します。
	 */
	$conf->directory_index = array(
		'index.html'
	);


	/**
	 * paths_proc_type
	 *
	 * パスのパターン別に処理方法を設定します。
	 *
	 * - ignore = 対象外パス。Pickles 2 のアクセス可能範囲から除外します。このパスにへのアクセスは拒絶され、パブリッシュの対象からも外されます。
	 * - direct = 物理ファイルを、ファイルとして読み込んでから加工処理を通します。 (direct以外の通常の処理は、PHPファイルとして `include()` されます)
	 * - pass = 物理ファイルを、そのまま無加工で出力します。 (デフォルト)
	 * - その他 = extension名
	 *
	 * パターンは先頭から検索され、はじめにマッチした設定を採用します。
	 * ワイルドカードとして "*"(アスタリスク) が使用可能です。
	 * 部分一致ではなく、完全一致で評価されます。従って、ディレクトリ以下すべてを表現する場合は、 `/*` で終わるようにしてください。
	 *
	 * extensionは、 `$conf->funcs->processor` に設定し、設定した順に実行されます。
	 * 例えば、 '*.html' => 'html' にマッチしたリクエストは、
	 * $conf->funcs->processor->html に設定したプロセッサのリストに沿って、上から順に処理されます。
	 */
	$conf->paths_proc_type = array(
		'/LICENSE' => 'ignore' ,
		'/.htaccess' => 'ignore' ,
		'/.travis.yml' => 'ignore' ,
		'/appveyor.yml' => 'ignore' ,
		'/phpunit.xml' => 'ignore' ,
		'/.px_execute.php' => 'ignore' ,
		'/px-files/*' => 'ignore' ,
		'*.ignore/*' => 'ignore' ,
		'*.ignore.*' => 'ignore' ,
		'/composer.json' => 'ignore' ,
		'/composer.lock' => 'ignore' ,
		'/README.md' => 'ignore' ,
		'/vendor/*' => 'ignore' ,
		'/tests/*' => 'ignore' ,
		'/php/*' => 'ignore' ,
		'*/.DS_Store' => 'ignore' ,
		'*/Thumbs.db' => 'ignore' ,
		'*/.svn/*' => 'ignore' ,
		'*/.git/*' => 'ignore' ,
		'*/.gitignore' => 'ignore' ,

		'/sample_pages/phpdoc/*' => 'pass' ,
		'/sample_pages/page1/4/test2/*' => 'pass' ,

		'*.html' => 'html' ,
		'*.htm' => 'html' ,
		'*.css' => 'css' ,
		'*.js' => 'js' ,
		'*.png' => 'pass' ,
		'*.jpg' => 'pass' ,
		'*.gif' => 'pass' ,
		'*.svg' => 'pass' ,
	);


	/**
	 * paths_enable_sitemap
	 *
	 * サイトマップのロードを有効にするパスのパターンを設定します。
	 * ワイルドカードとして "*"(アスタリスク) が使用可能です。
	 *
	 * サイトマップ中のページ数が増えると、サイトマップのロード自体に時間を要する場合があります。
	 * サイトマップへのアクセスが必要ないファイルでは、この処理はスキップするほうがよいでしょう。
	 *
	 * 多くの場合では、 *.html と *.htm 以外ではロードする必要はありません。
	 */
	$conf->paths_enable_sitemap = array(
		'*.html',
		'*.htm',
	);


	// system

	/** ファイルに適用されるデフォルトのパーミッション */
	$conf->file_default_permission = '775';
	/** ディレクトリに適用されるデフォルトのパーミッション */
	$conf->dir_default_permission = '775';
	/** ファイルシステムの文字セット。ファイル名にマルチバイト文字を使う場合に参照されます。 */
	$conf->filesystem_encoding = 'UTF-8';
	/** 出力文字エンコーディング名 */
	$conf->output_encoding = 'UTF-8';
	/** 出力改行コード名 (cr|lf|crlf) */
	$conf->output_eol_coding = 'lf';
	/** セッション名 */
	$conf->session_name = 'PXSID';
	/** セッションの有効期間 */
	$conf->session_expire = 1800;
	/** PX Commands のウェブインターフェイスからの実行を許可 */
	$conf->allow_pxcommands = 1;
	/** タイムゾーン */
	$conf->default_timezone = 'Asia/Tokyo';



	// -------- functions --------

	$conf->funcs = new stdClass;

	/**
	 * funcs: Before sitemap
	 *
	 * サイトマップ読み込みの前に実行するプラグインを設定します。
	 */
	$conf->funcs->before_sitemap = array(
		// PX=clearcache
		'picklesFramework2\commands\clearcache::register' ,

		 // PX=config
		'picklesFramework2\commands\config::register' ,

		 // PX=phpinfo
		'picklesFramework2\commands\phpinfo::register' ,

		// PX=publish
		'picklesFramework2\commands\publish::register('.json_encode(array(
			'paths_ignore'=> array(
				// パブリッシュ対象から常に除外するパスを設定する。
				// (ここに設定されたパスは、動的なプレビューは可能)
				'/sample_pages/no_publish/*',
				'/sample_pages/phpdoc/*',
			)
		)).')' ,

	);

	/**
	 * funcs: Before content
	 *
	 * サイトマップ読み込みの後、コンテンツ実行の前に実行するプラグインを設定します。
	 */
	$conf->funcs->before_content = array(
		// PX=api
		'picklesFramework2\commands\api::register' ,

	);


	/**
	 * processor
	 *
	 * コンテンツの種類に応じた加工処理の設定を行います。
	 * `$conf->funcs->processor->{$paths_proc_typeに設定した処理名}` のように設定します。
	 * それぞれの処理は配列で、複数登録することができます。処理は上から順に実行されます。
	 *
	 * Tips: テーマは、html に対するプロセッサの1つとして実装されています。
	 */
	$conf->funcs->processor = new stdClass;

	$conf->funcs->processor->html = array(
		// ページ内目次を自動生成する
		'picklesFramework2\processors\autoindex\autoindex::exec' ,

		// テーマ
		'theme'=>'picklesFramework2\theme\theme::exec' ,

		// Apache互換のSSIの記述を解決する
		'picklesFramework2\processors\ssi\ssi::exec' ,
	);

	$conf->funcs->processor->css = array(
	);

	$conf->funcs->processor->js = array(
	);

	$conf->funcs->processor->md = array(
		// Markdown文法を処理する
		'picklesFramework2\processors\md\ext::exec' ,

		// html のデフォルトの処理を追加
		$conf->funcs->processor->html ,
	);

	$conf->funcs->processor->scss = array(
		// SCSS文法を処理する
		'picklesFramework2\processors\scss\ext::exec' ,

		// css のデフォルトの処理を追加
		$conf->funcs->processor->css ,
	);


	/**
	 * funcs: Before output
	 *
	 * 最終出力の直前で実行される処理を設定します。
	 * この処理は、拡張子によらずすべてのリクエストが対象です。
	 * (HTMLの場合は、テーマの処理の後のコードが対象になります)
	 */
	$conf->funcs->before_output = array(
		// output_encoding, output_eol_coding の設定に従ってエンコード変換する。
		'picklesFramework2\processors\encodingconverter\encodingconverter::exec('.json_encode(array(
			'ext'=>array( // 対象の拡張子。省略時はすべてのリクエストが適用される。
				'html',
				'htm',
				'css',
				'js',
			),
		)).')' ,
	);



	// -------- PHP Setting --------

	/**
	 * `memory_limit`
	 *
	 * PHPのメモリの使用量の上限を設定します。
	 * 正の整数値で上限値(byte)を与えます。
	 *
	 *     例: 1000000 (1,000,000 bytes)
	 *     例: "128K" (128 kilo bytes)
	 *     例: "128M" (128 mega bytes)
	 *
	 * -1 を与えた場合、無限(システムリソースの上限まで)に設定されます。
	 * サイトマップやコンテンツなどで、容量の大きなデータを扱う場合に調整してください。
	 */
	// @ini_set( 'memory_limit' , -1 );

	/**
	 * `display_errors`, `error_reporting`
	 *
	 * エラーを標準出力するための設定です。
	 *
	 * PHPの設定によっては、エラーが発生しても表示されない場合があります。
	 * もしも、「なんか挙動がおかしいな？」と感じたら、
	 * 必要に応じてこれらのコメントを外し、エラー出力を有効にしてみてください。
	 *
	 * エラーメッセージは問題解決の助けになります。
	 */
	// @ini_set('display_errors', 1);
	// @ini_set('error_reporting', E_ALL);


	return $conf;
} );
