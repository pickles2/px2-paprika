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
		$proc_type = $px->get_path_proc_type();
		if( $proc_type == 'php' ){
			$me = new self( $px );
			$me->apply($json);
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
	public function apply($json){
		if($this->px->req()->get_param('PX') == 'paprika.publish_template'){
			// PX=paprika.publish_template は、テンプレートソースを出力するリクエストにつけられるパラメータ。
			// テンプレート生成時には、通常のHTMLと同様に振る舞うべきなので、処理をしない。
			return;
		}

		$px = $this->px;
		$realpath_script = $this->px->fs()->get_realpath($this->px->get_realpath_docroot().$this->px->get_path_controot().$this->px->req()->get_request_file_path());
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
			$px->fs()->get_realpath($px->get_realpath_docroot().$px->get_path_controot().$this->px->req()->get_request_file_path())
		);

		$src = '';
		if( $this->px->is_publish_tool() ){
			// --------------------
			// パブリッシュ時

			// 内部パス情報の再計算
			$paprika_env->realpath_controot_preview = $px->fs()->get_relatedpath(
				$px->get_realpath_docroot().$px->get_path_controot(),
				$px->fs()->get_realpath($this->px->conf()->path_publish_dir.$this->px->get_path_controot().$this->px->req()->get_request_file_path())
			);
			$paprika_env->realpath_homedir = $px->fs()->get_relatedpath(
				$px->get_realpath_homedir(),
				$px->fs()->get_realpath($this->px->conf()->path_publish_dir.$this->px->get_path_controot().$this->px->req()->get_request_file_path())
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
			chdir( dirname($realpath_script) );
			$paprika = new paprika($paprika_env, $this->px);

			// 環境変数を偽装
			// ※ `$paprika` 内にもとの `$_SERVER` を記憶するため、 `$paprika` 生成後に偽装しないと壊れます。
			$_SERVER['SCRIPT_NAME'] = $this->px->req()->get_request_file_path();
			$_SERVER['SCRIPT_FILENAME'] = $realpath_script;

			include( $realpath_script );
		}

		exit();
	}

}
