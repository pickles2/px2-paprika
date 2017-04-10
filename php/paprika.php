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
	private $paprika_plugin_conf;

	/** Paprika config object */
	private $conf;

	/** Pickles Framework 2 Object */
	private $px;

	/**
	 * オブジェクト
	 * @access private
	 */
	private $fs, $req;

	/**
	 * constructor
	 * @param object $paprika_plugin_conf Paprika Plugin Config
	 * @param object $px Picklesオブジェクト (プレビュー時は `$px` オブジェクト、パブリッシュ後には `false` を受け取ります)
	 */
	public function __construct( $paprika_plugin_conf, $px ){
		$this->paprika_plugin_conf = $paprika_plugin_conf;
		$this->px = $px; // パブリッシュ後には `false` を受け取ります。

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

		// Paprika の設定を読み込む
		$this->conf = new \stdClass;
		if( @is_file( $this->paprika_plugin_conf->realpath_homedir.'config_paprika.php' ) ){
			$this->conf = include( $this->paprika_plugin_conf->realpath_homedir.DIRECTORY_SEPARATOR.'config_paprika.php' );
		}

		// make instance $fs
		$this->fs = new \tomk79\filesystem( json_decode( json_encode( array(
			'file_default_permission' => @$this->paprika_plugin_conf->file_default_permission,
			'dir_default_permission' => @$this->paprika_plugin_conf->dir_default_permission,
			'filesystem_encoding' => @$this->paprika_plugin_conf->filesystem_encoding,
		) ) ) );

		// make instance $req
		$this->req = new \tomk79\request( json_decode( json_encode( array(
			'session_name' => @$this->paprika_plugin_conf->session_name,
			'session_expire' => @$this->paprika_plugin_conf->session_expire,
			'directory_index_primary' => @$this->paprika_plugin_conf->directory_index[0],
		) ) ) );
	}

	/**
	 * 設定を取得する
	 * @return object 設定オブジェクト
	 */
	public function conf(){
		return $this->conf;
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
	 * テンプレートにコンテンツをバインドする
	 * @return string 完成したHTML
	 */
	public function bind_template($contents, $path_tpl){
		// -----------------------------------
		// テンプレートを取得する
		$tpl = '';
		if( $this->px ){
			// is preview
			// `$this->px` が存在する場合は、プレビュー環境だと判断。
			$tpl = $this->px->internal_sub_request($path_tpl);
		}else{
			// is finalized
			// `$this->px` が存在しなければ、パブリッシュ後の実行であると判断。
			$tpl = file_get_contents( $_SERVER['DOCUMENT_ROOT'].$this->conf->path_controot.$path_tpl );
		}

		// -----------------------------------
		// テンプレートにHTMLをバインドする
		foreach($contents as $search=>$content){
			$tpl = str_replace( $search, $content, $tpl );
		}

		return $tpl;
	}

}
