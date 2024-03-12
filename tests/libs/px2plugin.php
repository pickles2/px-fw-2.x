<?php
namespace picklesFramework2\tests;

class px2plugin {
    public static function register( $px = null, $conf = null ){
		if( count(func_get_args()) <= 1 ){
			return __CLASS__.'::'.__FUNCTION__.'('.( is_array($px) ? json_encode($px) : '' ).')';
		}

		// PX=test-px2plugin を登録
		$px->pxcmd()->register('test-px2plugin', function($px) use ($conf){
            $json = (object) array();
            $json->realpath_plugin_files = $px->realpath_plugin_private_cache();
            echo json_encode($json);
			exit();
		});
    }
}
