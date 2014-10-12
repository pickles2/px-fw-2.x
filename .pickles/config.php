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
	$conf->public_cache_dir = '/caches/'; // 公開キャッシュディレクトリ

	// directory index
	$conf->directory_index = array(
		'index.html'
	);


	// system
	$conf->file_default_permission = '775';
	$conf->dir_default_permission = '775';
	$conf->filesystem_encoding = 'utf-8';
	$conf->output_encoding = 'shift_jis';
	$conf->output_eol_coding = 'lf';
	$conf->session_name = 'PXSID';
	$conf->session_expire = 1800;
	$conf->allow_pxcommands = 0; // PX Commands の実行を許可

	// commands
	$conf->commands = new stdClass;
	$conf->commands->php = 'php';

	// processor
	$conf->paths_proc_type = array(
		// パスのパターン別に処理方法を設定
		//     - ignore = 対象外パス
		//     - direct = 加工せずそのまま出力する(デフォルト)
		//     - その他 = extension 名
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

		'*.html' => 'html' ,
		'*.htm' => 'html' ,
		'*.css' => 'css' ,
		'*.js' => 'js' ,
		'*.png' => 'direct' ,
		'*.jpg' => 'direct' ,
		'*.gif' => 'direct' ,
		'*.svg' => 'direct' ,
	);


	// -------- functions --------

	$conf->funcs = new stdClass;

	// Starting
	$conf->funcs->starting = [
		'pickles\commands\phpinfo::funcs_starting' , // PX=phpinfo
		'pickles\commands\clearcache::funcs_starting' , // PX=clearcache
	];

	// Before content
	$conf->funcs->before_content = [
		'pickles\commands\publish::funcs_before_content' , // PX=publish
	];


	// processors
	$conf->funcs->process = new stdClass;
	$conf->funcs->process->html = [
		'tomk79\pickles2\autoindex\autoindex::exec' , // ページ内目次を自動生成する
		'pickles\themes\pickles\theme::exec' , // テーマにくるむ
		'tomk79\pickles2\outputfilter\encodingconverter::output_filter' , // output_encoding, output_eol_coding の設定に従ってエンコード変換する。
	];
	$conf->funcs->process->css = [
		'tomk79\pickles2\outputfilter\encodingconverter::output_filter' , // output_encoding, output_eol_coding の設定に従ってエンコード変換する。
	];
	$conf->funcs->process->js = [
		'tomk79\pickles2\outputfilter\encodingconverter::output_filter' , // output_encoding, output_eol_coding の設定に従ってエンコード変換する。
	];
	$conf->funcs->process->md = [
		'pickles\processors\md::exec' , // Markdown文法を処理する
		$conf->funcs->process->html , // html の処理を追加
	];
	$conf->funcs->process->scss = [
		'pickles\processors\scss::exec' , // SCSS文法を処理する
		$conf->funcs->process->css , // css の処理を追加
	];


	// output filter
	$conf->funcs->output_filter = [
	];


	return $conf;
} );