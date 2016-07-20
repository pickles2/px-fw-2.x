<?php
chdir(__DIR__);
@ini_set('display_errors', 1);
@ini_set('error_reporting', E_ALL);
require_once( '../../../../vendor/autoload.php' );
new picklesFramework2\pickles('./px-files/');
