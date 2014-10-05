<?php
return call_user_func( function(){

	// initialize
	$conf = new stdClass;

	// project
	$conf->name = 'Pickles 2'; // サイト名
	$conf->domain = null; // ドメイン
	$conf->path_controot = '/'; // コンテンツルートのディレクトリ

	// paths
	$conf->path_top = '/'; // トップページのパス(デフォルト "/")
	$conf->path_publish_dir = null; // パブリッシュ先ディレクトリパス
	$conf->paths_proc_type = array(
		// 処理方法を設定
		//     - process = Pickles の加工処理を通して出力
		//     - ignore = 対象外パス
		//     - direct = 加工せずそのまま出力する(デフォルト)
		// パターンは先頭から検索され、はじめにマッチした設定を採用する。
		// ワイルドカードとして "*"(アスタリスク) を使用可。
		'/.htaccess' => 'ignore' ,
		'/.px_execute.php' => 'ignore' ,
		'/.pickles/*' => 'ignore' ,
		'*.ignore/*' => 'ignore' ,
		'*.ignore.*' => 'ignore' ,
		'/composer.json' => 'ignore' ,
		'/composer.lock' => 'ignore' ,
		'/README.md' => 'ignore' ,
		'/vendor/*' => 'ignore' ,
		'*/.DS_Store' => 'ignore' ,
		'*/Thumbs.db' => 'ignore' ,
		'*/.svn/*' => 'ignore' ,
		'*/.git/*' => 'ignore' ,
		'*/.gitignore' => 'ignore' ,

		'*.html' => 'process' ,
		'*.htm' => 'process' ,
		'*.css' => 'process' ,
		'*.js' => 'process' ,
		'*.png' => 'direct' ,
		'*.jpg' => 'direct' ,
		'*.gif' => 'direct' ,
		'*.svg' => 'direct' ,
	);

	// directory index
	$conf->directory_index = array(
		'index.html'
	);


	// system
	$conf->file_default_permission = '775';
	$conf->dir_default_permission = '775';
	$conf->filesystem_encoding = 'utf-8';
	$conf->output_encoding = 'utf-8';
	$conf->output_eol_coding = 'lf';
	$conf->session_name = 'PXSID';
	$conf->session_expire = 1800;
	$conf->allow_pxcommands = 0; // PX Commands の実行を許可

	// commands
	$conf->commands = new stdClass;
	$conf->commands->php = 'php';


	// -------- functions --------

	$conf->funcs = new stdClass;

	/**
	 * Starting
	 */
	require_once(__DIR__.'/_sys/php/commands/phpinfo.php');
	require_once(__DIR__.'/_sys/php/commands/clearcache.php');
	require_once(__DIR__.'/_sys/php/commands/publish.php');
	require_once(__DIR__.'/_sys/php/pkg/autoindex/autoindex.php');
	$conf->funcs->starting = [
		'pickles\commands\phpinfo::funcs_starting' ,
		'pickles\commands\clearcache::funcs_starting' ,
	];
	/**
	 * Before content
	 */
	$conf->funcs->before_content = [
		'pickles\commands\publish::funcs_before_content' ,
	];

	/**
	 * extensions
	 */
	$conf->funcs->extensions = new stdClass;
	$conf->funcs->extensions->html = [
		'tomk79\pickles2\autoindex\autoindex::exec' ,
	];
	// $conf->funcs->extensions->css = [
	// ];
	// $conf->funcs->extensions->js = [
	// ];
	$conf->funcs->extensions->md = [
		'pickles\extensions\md::exec' ,
		'tomk79\pickles2\autoindex\autoindex::exec' ,
	];
	$conf->funcs->extensions->scss = [
		'pickles\extensions\scss::exec' ,
	];



	/**
	 * theme
	 */
	require_once(__DIR__.'/themes/pickles/php/theme.php');
	$conf->funcs->theme = 'pickles\themes\pickles\theme::exec';


	/**
	 * output filter
	 */
	require_once(__DIR__.'/_sys/php/pkg/outputfilter.php');
	$conf->funcs->output_filter = [
		'tomk79\pickles2\outputfilter\outputfilter::output_filter' ,
	];


	return $conf;
} );