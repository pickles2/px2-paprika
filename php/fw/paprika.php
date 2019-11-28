<?php
/**
 * Pickles 2 - Paprika Framework
 */
namespace picklesFramework2\paprikaFramework\fw;

/**
 * paprika.php
 */
class paprika{

	/** Plugin config object */
	private $paprika_env;

	/** Paprika config object */
	private $conf = array();

	/** Pickles Framework 2 Object */
	private $px;

	/** PDO */
	private $pdo;

	/**
	 * $bowl object
	 */
	private $bowl;

	/** $_SERVER のメモ */
	private $SERVER_MEMO;

	/** ユーザー定義メソッド */
	private $custom_methods = array();

	/**
	 * オブジェクト
	 * @access private
	 */
	private $fs, $req;

	/**
	 * constructor
	 * @param object $paprika_env Paprika Plugin Config
	 * @param object $px Picklesオブジェクト (プレビュー時は `$px` オブジェクト、パブリッシュ後には `false` を受け取ります)
	 */
	public function __construct( $paprika_env, $px ){
		$this->paprika_env = $paprika_env;
		$this->px = $px; // パブリッシュ後には `false` を受け取ります。
		// var_dump($this->paprika_env);

		$this->SERVER_MEMO = $_SERVER;

		// initialize PHP
		if( !extension_loaded( 'mbstring' ) ){
			trigger_error('mbstring not loaded.');
		}
		if( is_callable('mb_internal_encoding') ){
			mb_internal_encoding('UTF-8');
			@ini_set( 'mbstring.internal_encoding' , 'UTF-8' );
			@ini_set( 'mbstring.http_input' , 'UTF-8' );
			@ini_set( 'mbstring.http_output' , 'UTF-8' );
		}
		@ini_set( 'default_charset' , 'UTF-8' );
		if( is_callable('mb_detect_order') ){
			@ini_set( 'mbstring.detect_order' , 'UTF-8,SJIS-win,eucJP-win,SJIS,EUC-JP,JIS,ASCII' );
			mb_detect_order( 'UTF-8,SJIS-win,eucJP-win,SJIS,EUC-JP,JIS,ASCII' );
		}
		@header_remove('X-Powered-By');

		if( realpath('/') !== '/' ) {
			// Windows + PHP7 で CSV を正しく読み込めない問題への対策。
			// Windows では、 ja_JP.UTF-8 ロケールが正しく受け付けられない問題がある。
			// 代わりに、Cロケールを設定することで回避できる。
			setlocale(LC_CTYPE, 'C');
		}

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

		// デフォルトのHTTPレスポンスヘッダー
		@header('Content-type: text/html');

		// make instance $fs
		$this->fs = new \tomk79\filesystem( json_decode( json_encode( array(
			'file_default_permission' => @$this->paprika_env->file_default_permission,
			'dir_default_permission' => @$this->paprika_env->dir_default_permission,
			'filesystem_encoding' => @$this->paprika_env->filesystem_encoding,
		) ) ) );

		// パス系設定の解釈
		$this->paprika_env->realpath_controot = $this->fs->get_realpath($this->paprika_env->realpath_controot);
		$this->paprika_env->realpath_homedir = $this->fs->get_realpath($this->paprika_env->realpath_homedir);


		// config をロード
		$conf = null;
		if( is_file( $this->paprika_env->realpath_homedir.'/config.php' ) ){
			$conf = include( $this->paprika_env->realpath_homedir.'/config.php' );
		}
		$conf_local = null;
		if( is_file( $this->paprika_env->realpath_homedir.'/config_local.php' ) ){
			$conf_local = include( $this->paprika_env->realpath_homedir.'/config_local.php' );
		}
		$this->conf = (object) array_merge((array) $conf, (array) $conf_local);
		unset($conf, $conf_local);


		// make instance $log
		$this->log = new log( $this );


		// make instance $req
		$this->req = new \tomk79\request( json_decode( json_encode( array(
			'session_name' => @$this->paprika_env->session_name,
			'session_expire' => @$this->paprika_env->session_expire,
			'directory_index_primary' => @$this->paprika_env->directory_index[0],
			'cookie_default_path' => @$this->paprika_env->path_controot,
		) ) ) );

		// make instance $bowl
		$this->bowl = new bowl( $this, $this->px, $this->SERVER_MEMO );

		// prepend functions
		$prepend_fncs = $this->conf('prepend');
		if( is_array( $prepend_fncs ) ){
			$this->fnc_call_plugin_funcs( $prepend_fncs, $this );
		}
	}

	/**
	 * 設定を取得する
	 * @return object 設定オブジェクト
	 */
	public function conf( $name ){
		if( is_array( $this->conf ) ){
			if( !array_key_exists( $name, $this->conf ) ){
				return null;
			}
			return $this->conf[$name];
		}
		if( !property_exists( $this->conf, $name ) ){
			return null;
		}
		return $this->conf->{$name};
	}

	/**
	 * 設定をセットする
	 * @return object 設定オブジェクト
	 */
	public function set_conf( $name, $val ){
		if( is_array( $this->conf ) ){
			return $this->conf[$name] = $val;
		}
		return $this->conf->{$name} = $val;
	}

	/**
	 * Paprika の環境情報を取得する
	 */
	public function env(){
		return $this->paprika_env;
	}

	/**
	 * `$fs` オブジェクトを取得する。
	 *
	 * `$fs`(class [tomk79\filesystem](tomk79.filesystem.html))のインスタンスを返します。
	 *
	 * @see https://github.com/tomk79/filesystem
	 * @return object $fs オブジェクト
	 */
	public function fs(){
		return $this->fs;
	}

	/**
	 * `$req` オブジェクトを取得する。
	 *
	 * `$req`(class [tomk79\request](tomk79.request.html))のインスタンスを返します。
	 *
	 * @see https://github.com/tomk79/request
	 * @return object $req オブジェクト
	 */
	public function req(){
		return $this->req;
	}

	/**
	 * `$bowl` オブジェクトを取得する。
	 *
	 * @return object $bowl オブジェクト
	 */
	public function bowl(){
		return $this->bowl;
	}

	/**
	 * `$log` オブジェクトを取得する。
	 *
	 * @return object $log オブジェクト
	 */
	public function log(){
		return $this->log;
	}

	/**
	 * `$pdo` オブジェクトを生成して取得する。
	 *
	 * @return object $pdo オブジェクト
	 */
	public function pdo(){
		if( $this->pdo ){
			// 既にPDOが生成済みならそれを返す
			return $this->pdo;
		}

		// 設定を整理
		$db = (object) $this->conf('db');
		if( !is_object($db) ){
			return false;
		}
		if( !property_exists($db, 'connection') || !strlen($db->connection) ){
			return false;
		}
		if( !property_exists($db, 'dsn') ){
			$db->dsn = null;
		}
		if( !property_exists($db, 'host') ){
			$db->host = null;
		}
		if( !property_exists($db, 'port') ){
			$db->port = null;
		}
		if( !property_exists($db, 'username') ){
			$db->username = null;
		}
		if( !property_exists($db, 'password') ){
			$db->password = null;
		}

		// PDOオプションを生成
		$dsn = $db->dsn;
		$options = array();
		if( !strlen($dsn) ){
			switch( $db->connection ){
				case 'sqlite':
					$dsn = $db->connection.':'.$db->database;
					break;
				case 'mysql':
					$dsn = $db->connection.':host='.$db->host.';port='.$db->port.';dbname='.$db->database;
					break;
				case 'pgsql':
					$dsn = $db->connection.':host='.$db->host.';port='.$db->port.';dbname='.$db->database.';user='.$db->username.';password='.$db->password;
					break;
				case 'oci':
					$dbname = '';
					if( strlen($db->host) ){
						$dbname .= '//'.$db->host;
						if( strlen($db->port) ){
							$dbname .= ':'.$db->port;
						}
						$dbname .= '/';
					}
					$dbname .= $db->database;
					$dsn = $db->connection.':dbname='.$dbname;
					break;
			}
		}

		// 接続
		$this->pdo = new \PDO(
			$dsn,
			$db->username,
			$db->password,
			$options
		);

		return $this->pdo;
	}

	/**
	 * ユーザー定義のメソッドを追加する
	 */
	public function add_custom_method( $name, \Closure $callback ){
		$this->custom_methods[$name] = $callback;
	}

	/**
	 * ユーザー定義のメソッドを呼び出す
	 */
	public function __call( $name, array $args ){
		return call_user_func_array( $this->custom_methods[$name], $args );
	}

	/**
	 * call plugin functions
	 *
	 * @param mixed $func_list List of plugins function
	 * @return bool 成功時 `true`、失敗時 `false`
	 */
	private function fnc_call_plugin_funcs( $func_list ){
		if( is_null($func_list) ){ return false; }
		$param_arr = func_get_args();
		array_shift($param_arr);

		if( @!empty( $func_list ) ){
			// functions
			if( is_array($func_list) ){
				foreach( $func_list as $fnc_id=>$fnc_name ){
					if( is_callable($fnc_name) ){
						call_user_func_array( $fnc_name, $param_arr);
					}
				}
			}
			unset($fnc_name);
		}
		return true;
	}

}
