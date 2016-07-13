<?php
chdir(__DIR__);
@ini_set('display_errors', 'On');
@ini_set('error_reporting', 32767);
require_once( '../../../../vendor/autoload.php' );
new picklesFramework2\pickles('./px-files/');
