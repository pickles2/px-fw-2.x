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
		'/composer.json' ,
		'/composer.lock' ,
		'/README.md' ,
		'/vendor/*' ,
		'*.ignore/*' ,
		'*.ignore.*' ,
		'*/.DS_Store' ,
		'*/Thumbs.db' ,
		'*/.svn/*' ,
		'*/.git/*' ,
		'*/.gitignore' ,
	);

	// extensions
	$conf->extensions = new stdClass;
	$conf->extensions->html = [
		'pickles\\extensions\\html\\ext::exec' ,
	];
	$conf->extensions->css = [
	];
	$conf->extensions->js = [
	];
	$conf->extensions->md = [
		'pickles\\extensions\\html\\ext::exec' ,
	];


	// theme
	require_once(__DIR__.'/themes/pickles/theme.php');
	$conf->theme = 'pickles\\themes\\pickles\\theme::exec';

	// directory index
	$conf->directory_index = array(
		'index.html'
	);


	// filesystem
	$conf->file_default_permission = '0775';
	$conf->dir_default_permission = '0775';
	$conf->filesystem_encoding = 'utf-8';

	// session
	$conf->session_name = 'PXSID';
	$conf->session_expire = 1800;

	return $conf;
} );