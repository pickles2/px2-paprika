<?php
/**
 * px2-paprika-fw-2.x
 */
namespace tomk79\pickles2\paprikaFramework2;

/**
 * main.php
 */
class main{

	/**
	 * Picklesオブジェクト
	 */
	private $px;

	/**
	 * Starting function
	 * @param object $px Picklesオブジェクト
	 * @param object $json プラグイン設定オブジェクト
	 */
	public static function exec( $px, $json ){
		$path_req = $px->req()->get_request_file_path();
		$proc_type = $px->get_path_proc_type();
		if( $proc_type == 'php' || preg_match('/\.php\//', $path_req) ){
			$me = new self( $px );
			$me->apply($json);
			return;
		}
	}

	/**
	 * constructor
	 * @param object $px Picklesオブジェクト
	 */
	public function __construct( $px ){
		$this->px = $px;
	}

	/**
	 * apply output filter
	 * @param object $json プラグイン設定
	 * @return string 加工後の出力コード
	 */
	private function apply($json){
		if($this->px->req()->get_param('PX') == 'paprika.publish_template'){
			// PX=paprika.publish_template は、テンプレートソースを出力するリクエストにつけられるパラメータ。
			// テンプレート生成時には、通常のHTMLと同様に振る舞うべきなので、処理をしない。
			return;
		}

		$px = $this->px;
		$current_page_info = array();
		if( $px->site() ){
			$current_page_info = $px->site()->get_current_page_info();
		}
		if( !strlen( @$current_page_info['content'] ) ){
			$current_page_info['content'] = $this->px->req()->get_request_file_path();
		}
		$path_script = $this->px->fs()->get_realpath('/'.$this->px->get_path_controot().$current_page_info['content']);
		$realpath_script = $this->px->fs()->get_realpath($this->px->get_realpath_docroot().$path_script);
		// var_dump($realpath_script);

		// making config object
		$paprika_env = json_decode('{}');

		// config for $fs
		$paprika_env->file_default_permission = $px->conf()->file_default_permission;
		$paprika_env->dir_default_permission = $px->conf()->dir_default_permission;
		$paprika_env->filesystem_encoding = $px->conf()->filesystem_encoding;

		// config for $req
		$paprika_env->session_name = $px->conf()->session_name;
		$paprika_env->session_expire = $px->conf()->session_expire;
		$paprika_env->directory_index = $px->conf()->directory_index;

		// 内部パス情報
		$paprika_env->realpath_controot = $px->fs()->get_relatedpath(
			$px->get_realpath_docroot().$px->get_path_controot(),
			$realpath_script
		);
		$paprika_env->realpath_controot_preview = $paprika_env->realpath_controot;
			// ↑プレビュー環境(パブリッシュ前)の controot を格納する。
		$paprika_env->realpath_homedir = $px->fs()->get_relatedpath(
			$px->get_realpath_homedir(),
			$realpath_script
		);
		$paprika_env->path_controot = $px->get_path_controot();
		$paprika_env->realpath_files = $px->fs()->get_relatedpath(
			$px->realpath_files(),
			$realpath_script
		);

		$src = '';
		if( $this->px->is_publish_tool() ){
			// --------------------
			// パブリッシュ時

			// 内部パス情報の再計算
			$paprika_env->realpath_controot_preview = $px->fs()->get_relatedpath(
				$px->get_realpath_docroot().$px->get_path_controot(),
				$px->fs()->get_realpath($this->px->conf()->path_publish_dir.$path_script)
			);
			$paprika_env->realpath_homedir = $px->fs()->get_relatedpath(
				$px->get_realpath_homedir(),
				$px->fs()->get_realpath($this->px->conf()->path_publish_dir.$path_script)
			);

			$template = file_get_contents( __DIR__.'/resources/dist_src/header.php.template' );
			$template = str_replace( '{$paprika_env}', escapeshellarg(json_encode($paprika_env,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)), $template );
			$src .= $template;
			$src .= file_get_contents( $realpath_script );

			// 最終出力
			// (`pickles.php` からコピー)
			switch( $px->req()->get_cli_option('-o') ){
				case 'json':
					$json = new \stdClass;
					$json->status = $px->get_status();
					$json->message = $px->get_status_message();
					$json->relatedlinks = $px->get_relatedlinks();
					$json->errors = $px->get_errors();
					$json->body_base64 = base64_encode($src);
					$json->header = $px->header_list();
					print json_encode($json);
					break;
				default:
					print $src;
					break;
			}

		}else{
			// --------------------
			// プレビュー時
			while( !is_file($realpath_script) ){
				if( $realpath_script == dirname($realpath_script) ){
					break;
				}
				$realpath_script = dirname($realpath_script);
				// trigger_error( 'File "'.$realpath_script.'" is NOT exists.' );
				// exit();
			}
			chdir( dirname($realpath_script) );
			$paprika = new paprika($paprika_env, $this->px);

			// 環境変数を偽装
			// ※ `$paprika` 内にもとの `$_SERVER` を記憶するため、 `$paprika` 生成後に偽装しないと壊れます。
			$_SERVER['SCRIPT_NAME'] = $path_script;
			$_SERVER['SCRIPT_FILENAME'] = $realpath_script;
			$_SERVER['PATH_INFO'] = preg_replace('/^'.preg_quote($path_script, '/').'/', '', $_SERVER['PATH_INFO']);

			include( $realpath_script );
		}

		exit();
	}

}
