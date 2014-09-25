<?php
return call_user_func( function(){

	// initialize
	$conf = new stdClass;

	// project
	$conf->name = 'Pickles 2';
	$conf->domain = null;
	$conf->path_docroot = null;

	// paths
	$conf->path_top = '/';
	$conf->path_publish_dir = null; // パブリッシュ先ディレクトリパス
	$conf->paths_ignore = array(
		// パブリッシュ対象外パスの一覧(ワイルドカードとしてアスタリスクを使用可)
		'/.htaccess' ,
		'/.px_execute.php' ,
		'/.pickles/*' ,
		'*.ignore/*' ,
		'*.ignore.*' ,
		'/composer.json' ,
		'/composer.lock' ,
		'/README.md' ,
		'/vendor/*' ,
		'*/.DS_Store' ,
		'*/Thumbs.db' ,
		'*/.svn/*' ,
		'*/.git/*' ,
		'*/.gitignore' ,
	);

	// directory index
	$conf->directory_index = array(
		'index.html'
	);


	// system
	$conf->file_default_permission = '0775';
	$conf->dir_default_permission = '0775';
	$conf->filesystem_encoding = 'utf-8';
	$conf->output_encoding = 'utf-8';
	$conf->output_eol_coding = 'lf';
	$conf->session_name = 'PXSID';
	$conf->session_expire = 1800;


	// -------- functions --------

	$conf->funcs = new stdClass;

	/**
	 * Starting
	 */
	require_once(__DIR__.'/_sys/php/commands/clearcache.php');
	require_once(__DIR__.'/_sys/php/commands/publish.php');
	$conf->funcs->starting = [
		'pickles\\commands\\clearcache::funcs_starting' ,
		'pickles\\commands\\publish::funcs_starting' ,
	];

	/**
	 * extensions
	 */
	$conf->funcs->extensions = new stdClass;
	// $conf->extensions->html = [
	// ];
	// $conf->extensions->css = [
	// ];
	// $conf->extensions->js = [
	// ];
	$conf->funcs->extensions->md = [
		'pickles\\extensions\\md::exec' ,
	];
	$conf->funcs->extensions->scss = [
		'pickles\\extensions\\scss::exec' ,
	];


	/**
	 * theme
	 */
	require_once(__DIR__.'/themes/pickles/php/theme.php');
	$conf->funcs->theme = 'pickles\\themes\\pickles\\theme::exec';


	return $conf;
} );