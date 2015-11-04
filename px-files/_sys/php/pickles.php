<?php
/**
 * pickles2
 */
namespace picklesFramework2;

/**
 * pickles2 entry and controller class
 *
 * @author Tomoya Koyanagi <tomk79@gmail.com>
 */
class pickles{

	/**
	 * constructor
	 *
	 * @param string $path_homedir Pickles のホームディレクトリのパス
	 */
	public function __construct( $path_homedir ){

		if( !array_key_exists( 'REMOTE_ADDR' , $_SERVER ) ){
			// commandline only
			if( realpath($_SERVER['SCRIPT_FILENAME']) === false ||
				dirname(realpath($_SERVER['SCRIPT_FILENAME'])) !== realpath('./')
			){
				if( array_key_exists( 'PWD' , $_SERVER ) && is_file($_SERVER['PWD'].'/'.$_SERVER['SCRIPT_FILENAME']) ){
					$_SERVER['SCRIPT_FILENAME'] = realpath($_SERVER['PWD'].'/'.$_SERVER['SCRIPT_FILENAME']);
				}else{
					// for Windows
					// .px_execute.php で chdir(__DIR__) されていることが前提。
					$_SERVER['SCRIPT_FILENAME'] = realpath('./'.basename($_SERVER['SCRIPT_FILENAME']));
				}
			}
		}
		chdir( dirname($_SERVER['SCRIPT_FILENAME']) );

		$px = new px($path_homedir);


		// 最終出力
		switch( $px->req()->get_cli_option('-o') ){
			case 'json':
				$json = new \stdClass;
				$json->status = $px->get_status();
				$json->message = $px->get_status_message();
				$json->relatedlinks = $px->get_relatedlinks();
				$json->errors = $px->get_errors();
				$json->body_base64 = base64_encode($px->bowl()->pull());
				print json_encode($json);
				break;
			default:
				print $px->bowl()->pull();
				break;
		}

		exit;
	}

}
