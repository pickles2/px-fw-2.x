<?php
return call_user_func( function(){

	// initialize
	$conf = new stdClass;

	// filesystem
	$conf->file_default_permission = '0775';
	$conf->dir_default_permission = '0775';
	$conf->filesystem_encoding = 'utf-8';

	// session
	$conf->session_name = 'PXSID';
	$conf->session_expire = 1800;

	return $conf;
} );