<?php
/**
 * Pickles 2 - Paprika Framework
 */
namespace tomk79\pickles2\paprikaFramework2;

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

	/**
	 * $bowl object
	 */
	private $bowl;

	/** $_SERVER のメモ */
	private $SERVER_MEMO;

	/** ユーザー定義メソッド */
	private $custom_methods = array();

	/** 初期化メソッド */
	private $initialize_methods = array();

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

		// make instance $req
		$this->req = new \tomk79\request( json_decode( json_encode( array(
			'session_name' => @$this->paprika_env->session_name,
			'session_expire' => @$this->paprika_env->session_expire,
			'directory_index_primary' => @$this->paprika_env->directory_index[0],
			'cookie_default_path' => @$this->paprika_env->path_controot,
		) ) ) );

		// make instance $bowl
		$this->bowl = new bowl( $this, $this->px, $this->SERVER_MEMO );
	}

	/**
	 * 設定を取得する
	 * @return object 設定オブジェクト
	 */
	public function conf( $name ){
		return $this->conf[$name];
	}

	/**
	 * 設定をセットする
	 * @return object 設定オブジェクト
	 */
	public function set_conf( $name, $val ){
		return $this->conf[$name] = $val;
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
	 * 初期化メソッドを追加する
	 */
	public function add_initialize_method( $callback ){
		array_push( $this->initialize_methods, $callback );
		return;
	}

	/**
	 * 初期化メソッドを実行する
	 */
	public function execute_initialize_methods(){
		foreach( $this->initialize_methods as $callback ){
			$callback();
		}
		return;
	}

}
