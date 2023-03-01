<?php
chdir($_SERVER['DOCUMENT_ROOT']);
$path = $_SERVER['REQUEST_URI'];
$path_controot = '/';
$path = preg_replace('/^'.preg_quote($path_controot, '/').'/', '/', $path);
$path_entryScript = '.'.'/.px_execute.php';
$script_name = '/.px_execute.php';
$querystring = '';
if( strpos($path, '?') !== false ){
    list($path, $querystring) = preg_split('/\?/', $path, 2);
}
if( strrpos($path, '/') === strlen($path)-1 || preg_match('/\.(?:html|htm|css|js)$/', $path) ){
    $_SERVER['SCRIPT_FILENAME'] = realpath($path_entryScript);
    $_SERVER['SCRIPT_NAME'] = $script_name;
    $_SERVER['PATH_INFO'] = $path;
    $_SERVER['PHP_SELF'] = $path_entryScript.$path;
    include($path_entryScript);
    return;
}
return false;
