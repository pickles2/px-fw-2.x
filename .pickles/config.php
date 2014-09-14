<?php
return call_user_func( function(){

	// initialize
	$conf = new stdClass;

	// paths
	$conf->path_top = '/';
	$conf->path_publish_dir = null; // パブリッシュ先ディレクトリパス
	$conf->paths_ignore = array(
		// パブリッシュ対象外パスの一覧(ワイルドカードとしてアスタリスクを使用可)
		'/.pickles/' ,
		'/vendor/' ,
		'/_px_execute.php' ,
		'/composer.json' ,
		'/composer.lock' ,
		'/README.md' ,
		'*.ignore/' ,
	);

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