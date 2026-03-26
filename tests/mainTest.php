<?php
/**
 * test for pickles2/px-fw-2.x
 */
class mainTest extends PHPUnit\Framework\TestCase{
	private $fs;

	public function setUp() : void{
		mb_internal_encoding('UTF-8');
		$this->fs = new tomk79\filesystem();
		require_once(__DIR__.'/libs/simple_html_dom.php');
	}


	/**
	 * 普通にインスタンス化して実行してみるテスト
	 */
	public function testStandard(){
		$cd = realpath('.');
		$SCRIPT_FILENAME = $_SERVER['SCRIPT_FILENAME'];
		chdir(__DIR__.'/testData/standard/');
		$_SERVER['SCRIPT_FILENAME'] = __DIR__.'/testData/standard/.px_execute.php';

		$px = new picklesFramework2\px('./px-files/');
		$toppage_info = $px->site()->get_page_info('');
		$this->assertEquals( $toppage_info['title'], '<HOME>' );
		$this->assertEquals( $toppage_info['path'], '/index.html' );
		$this->assertEquals( $_SERVER['HTTP_USER_AGENT'], '' );

		$this->assertEquals( $px->get_scheme(), 'http' );
		$this->assertEquals( $px->get_domain(), 'pickles2.pxt.jp' );

		$this->assertEquals( $px->lang(), 'ja' );
		$this->assertEquals( $px->set_lang('undefined-lang'), false );
		$this->assertEquals( $px->lang(), 'ja' );
		$this->assertEquals( $px->set_lang('en'), true );
		$this->assertEquals( $px->lang(), 'en' );


		// 動的なプロパティを登録する
		$custom_property = 'custom_property';
		$px->custom_property = $custom_property;
		$this->assertEquals( $px->custom_property, 'custom_property' );
		$px->conf = $custom_property;
		$this->assertEquals( $px->conf, 'custom_property' );


		// サブリクエストでキャッシュを消去
		$output = $px->internal_sub_request(
			'/index.html?PX=clearcache',
			array(),
			$vars
		);
		$error = $px->get_errors();
		$this->assertTrue( is_string($output) );
		$this->assertSame( 0, $vars ); // <- strict equals
		$this->assertSame( array(), $error );


		chdir($cd);
		$_SERVER['SCRIPT_FILENAME'] = $SCRIPT_FILENAME;

		$px->__destruct();// <- required on Windows
		unset($px);
	}

	/**
	 * session.cookie_secure の自動切替条件をテスト
	 */
	public function testSessionCookieSecureAutoSwitch(){
		$cd = realpath('.');
		$SCRIPT_FILENAME = $_SERVER['SCRIPT_FILENAME'] ?? null;
		$server_backup = $_SERVER;

		chdir(__DIR__.'/testData/standard/');
		$_SERVER['SCRIPT_FILENAME'] = __DIR__.'/testData/standard/.px_execute.php';
		$_SERVER['REQUEST_METHOD'] = 'GET';
		$_SERVER['HTTP_USER_AGENT'] = '';

		$px_factory = function($mocked_sapi_name){
			return new class('./px-files/', $mocked_sapi_name) extends picklesFramework2\px {
				private $mocked_sapi_name;
				public function __construct($path_homedir, $mocked_sapi_name){
					$this->mocked_sapi_name = $mocked_sapi_name;
					parent::__construct($path_homedir);
				}
				protected function get_php_sapi_name(){
					return $this->mocked_sapi_name;
				}
			};
		};

		$get_should_disable = function($px){
			$reflection = new \ReflectionMethod('picklesFramework2\px', 'should_disable_session_cookie_secure_for_local_dev');
			$reflection->setAccessible(true);
			return $reflection->invoke($px);
		};

		$clean_headers = function(){
			unset(
				$_SERVER['HTTP_X_FORWARDED_FOR'],
				$_SERVER['HTTP_X_FORWARDED_PROTO'],
				$_SERVER['HTTP_X_FORWARDED_HOST'],
				$_SERVER['HTTP_X_REAL_IP'],
				$_SERVER['HTTP_FORWARDED'],
				$_SERVER['HTTP_VIA'],
				$_SERVER['HTTP_HOST'],
				$_SERVER['SERVER_NAME']
			);
		};

		// 5条件成立: REMOTE_ADDR=ローカル, HTTPS=off, built-in server, プロキシヘッダなし, Hostローカル
		$clean_headers();
		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		$_SERVER['HTTPS'] = 'off';
		$px = $px_factory('cli-server');
		$this->assertTrue($get_should_disable($px));
		$px->__destruct();// <- required on Windows
		unset($px);

		// 5条件成立: Host=localhost:8080
		$clean_headers();
		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		$_SERVER['HTTPS'] = 'off';
		$_SERVER['HTTP_HOST'] = 'localhost:8080';
		$px = $px_factory('cli-server');
		$this->assertTrue($get_should_disable($px));
		$px->__destruct();// <- required on Windows
		unset($px);

		// 5条件成立: Host=プライベートIP
		$clean_headers();
		$_SERVER['REMOTE_ADDR'] = '192.168.1.100';
		$_SERVER['HTTPS'] = 'off';
		$_SERVER['HTTP_HOST'] = '192.168.1.100:8080';
		$px = $px_factory('cli-server');
		$this->assertTrue($get_should_disable($px));
		$px->__destruct();// <- required on Windows
		unset($px);

		// 条件1不成立: グローバルIP
		$clean_headers();
		$_SERVER['REMOTE_ADDR'] = '8.8.8.8';
		$_SERVER['HTTPS'] = 'off';
		$px = $px_factory('cli-server');
		$this->assertFalse($get_should_disable($px));
		$px->__destruct();// <- required on Windows
		unset($px);

		// 条件2不成立: HTTPS=on
		$clean_headers();
		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		$_SERVER['HTTPS'] = 'on';
		$px = $px_factory('cli-server');
		$this->assertFalse($get_should_disable($px));
		$px->__destruct();// <- required on Windows
		unset($px);

		// 条件3不成立: built-in server ではない
		$clean_headers();
		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		$_SERVER['HTTPS'] = 'off';
		$px = $px_factory('cli');
		$this->assertFalse($get_should_disable($px));
		$px->__destruct();// <- required on Windows
		unset($px);

		// 条件4不成立: X-Forwarded-For が存在 (nginx等のプロキシ経由)
		$clean_headers();
		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		$_SERVER['HTTPS'] = 'off';
		$_SERVER['HTTP_X_FORWARDED_FOR'] = '203.0.113.50';
		$px = $px_factory('cli-server');
		$this->assertFalse($get_should_disable($px));
		$px->__destruct();// <- required on Windows
		unset($px);

		// 条件4不成立: X-Forwarded-Proto が存在
		$clean_headers();
		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		$_SERVER['HTTPS'] = 'off';
		$_SERVER['HTTP_X_FORWARDED_PROTO'] = 'http';
		$px = $px_factory('cli-server');
		$this->assertFalse($get_should_disable($px));
		$px->__destruct();// <- required on Windows
		unset($px);

		// 条件4不成立: X-Real-IP が存在
		$clean_headers();
		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		$_SERVER['HTTPS'] = 'off';
		$_SERVER['HTTP_X_REAL_IP'] = '203.0.113.50';
		$px = $px_factory('cli-server');
		$this->assertFalse($get_should_disable($px));
		$px->__destruct();// <- required on Windows
		unset($px);

		// 条件4不成立: Forwarded (RFC 7239) が存在
		$clean_headers();
		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		$_SERVER['HTTPS'] = 'off';
		$_SERVER['HTTP_FORWARDED'] = 'for=203.0.113.50;proto=http';
		$px = $px_factory('cli-server');
		$this->assertFalse($get_should_disable($px));
		$px->__destruct();// <- required on Windows
		unset($px);

		// 条件4不成立: Via が存在
		$clean_headers();
		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		$_SERVER['HTTPS'] = 'off';
		$_SERVER['HTTP_VIA'] = '1.1 proxy.example.com';
		$px = $px_factory('cli-server');
		$this->assertFalse($get_should_disable($px));
		$px->__destruct();// <- required on Windows
		unset($px);

		// 条件5不成立: Host がドメイン名 (本番環境を示唆)
		$clean_headers();
		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		$_SERVER['HTTPS'] = 'off';
		$_SERVER['HTTP_HOST'] = 'www.example.com';
		$px = $px_factory('cli-server');
		$this->assertFalse($get_should_disable($px));
		$px->__destruct();// <- required on Windows
		unset($px);

		// 条件5不成立: Host がグローバルIP
		$clean_headers();
		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		$_SERVER['HTTPS'] = 'off';
		$_SERVER['HTTP_HOST'] = '203.0.113.50:80';
		$px = $px_factory('cli-server');
		$this->assertFalse($get_should_disable($px));
		$px->__destruct();// <- required on Windows
		unset($px);

		// 複合: プロキシヘッダ + Host=localhost → プロキシヘッダ優先で false
		$clean_headers();
		$_SERVER['REMOTE_ADDR'] = '127.0.0.1';
		$_SERVER['HTTPS'] = 'off';
		$_SERVER['HTTP_HOST'] = 'localhost';
		$_SERVER['HTTP_X_FORWARDED_FOR'] = '203.0.113.50';
		$px = $px_factory('cli-server');
		$this->assertFalse($get_should_disable($px));
		$px->__destruct();// <- required on Windows
		unset($px);

		$clean_headers();
		chdir($cd);
		if( is_null($SCRIPT_FILENAME) ){
			unset($_SERVER['SCRIPT_FILENAME']);
		}else{
			$_SERVER['SCRIPT_FILENAME'] = $SCRIPT_FILENAME;
		}
		$_SERVER = $server_backup;
	}

}
