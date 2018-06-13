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

	/** Paprika Environment Settings */
	private $paprika_env;

	/** paths */
	private $path_script, $realpath_script;

	/** current page info */
	private $current_page_info;

	/**
	 * Starting function
	 * @param object $px Picklesオブジェクト
	 * @param object $json プラグイン設定オブジェクト
	 */
	public static function exec( $px, $json ){
		$px->pxcmd()->register('paprika', function($px){
			$pxcmd = $px->get_px_command();
			if( $pxcmd[1] == 'init' ){
				$me = new self( $px );
				$me->init();
				exit;
			}
		});

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

		$this->current_page_info = null;
		if( $px->site() ){
			$this->current_page_info = $px->site()->get_current_page_info();
		}
		$current_content_path = $this->px->req()->get_request_file_path();
		if( $this->current_page_info && strlen(@$this->current_page_info['content']) ){
			$current_content_path = $this->current_page_info['content'];
		}
		$this->path_script = $this->px->fs()->get_realpath('/'.$this->px->get_path_controot().$current_content_path);
		$this->realpath_script = $this->px->fs()->get_realpath($this->px->get_realpath_docroot().$this->path_script);
		// var_dump($this->realpath_script);

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
			$this->realpath_script
		);
		$paprika_env->realpath_controot_preview = $paprika_env->realpath_controot;
			// ↑プレビュー環境(パブリッシュ前)の controot を格納する。
		$paprika_env->realpath_homedir = $px->fs()->get_relatedpath(
			$px->get_realpath_homedir(),
			$this->realpath_script
		);
		$paprika_env->path_controot = $px->get_path_controot();
		$paprika_env->realpath_files = $px->fs()->get_relatedpath(
			$px->realpath_files(),
			$this->realpath_script
		);
		$this->paprika_env = $paprika_env;
	}

	/**
	 * $paprika を生成する
	 */
	private function paprika(){
		while( !is_file($this->realpath_script) ){
			if( $this->realpath_script == dirname($this->realpath_script) ){
				break;
			}
			$this->realpath_script = dirname($this->realpath_script);
		}
		chdir( dirname($this->realpath_script) );
		$paprika = new paprika($this->paprika_env, $this->px);
		return $paprika;
	}

	/**
	 * Paprika を初期化する
	 */
	private function init(){
		echo 'Initialize Paprika...'."\n";
		$paprika = $this->paprika();
		$exdb = $paprika->exdb();

		// データベーステーブルを初期化
		echo 'Migrate Database tables...'."\n";
		$exdb->migrate_init_tables();

		echo 'done!'."\n";
		exit;
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

		$src = '';
		if( $this->px->is_publish_tool() ){
			// --------------------
			// パブリッシュ時

			// 一度実行して、テンプレートを生成させる
			if( $this->current_page_info ){
				$this->px->internal_sub_request(
					$this->path_script,
					array(
						'output'=>'json',
						'user_agent'=>'Mozilla/1.0'
					)
				);
				// テンプレートが存在するなら、パブリッシュ先に加える
				if(is_file($this->px->realpath_files('/paprika/template'))){
					$this->px->add_relatedlink( $this->px->path_files('/paprika/template') );
				}
			}

			// 内部パス情報の再計算
			$this->paprika_env->realpath_controot_preview = $px->fs()->get_relatedpath(
				$px->get_realpath_docroot().$px->get_path_controot(),
				$px->fs()->get_realpath($this->px->conf()->path_publish_dir.$this->path_script)
			);
			$this->paprika_env->realpath_homedir = $px->fs()->get_relatedpath(
				$px->get_realpath_homedir(),
				$px->fs()->get_realpath($this->px->conf()->path_publish_dir.$this->path_script)
			);

			$template = file_get_contents( __DIR__.'/resources/dist_src/header.php.template' );
			$template = str_replace( '{$paprika_env}', escapeshellarg(json_encode($this->paprika_env,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)), $template );
			$src .= $template;
			$src .= file_get_contents( $this->realpath_script );

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
			$paprika = $this->paprika();

			// 環境変数を偽装
			// ※ `$paprika` 内にもとの `$_SERVER` を記憶するため、 `$paprika` 生成後に偽装しないと壊れます。
			$_SERVER['SCRIPT_NAME'] = $this->path_script;
			$_SERVER['SCRIPT_FILENAME'] = $this->realpath_script;
			if( is_string(@$_SERVER['PATH_INFO']) ){
				$_SERVER['PATH_INFO'] = preg_replace('/^'.preg_quote($this->path_script, '/').'/', '', $_SERVER['PATH_INFO']);
			}

			include( $this->realpath_script );
		}

		exit();
	}

}
