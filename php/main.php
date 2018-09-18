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
	 * plugin - before content
	 * @param object $px Picklesオブジェクト
	 * @param object $conf プラグイン設定オブジェクト
	 */
	public static function before_content( $px, $conf ){
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
		if( $proc_type == 'php' || preg_match('/\.(?:php)\//', $path_req) ){
			$me = new self( $px );
			$me->execute_php_contents($conf);
			return;
		}
	}

	/**
	 * plugin - contents processor
	 * @param object $px Picklesオブジェクト
	 * @param object $conf プラグイン設定オブジェクト
	 */
	public static function processor( $px, $conf ){
		$pxcmd = $px->get_px_command();
		if( $pxcmd[1] == 'publish_template' ){
			foreach( $px->bowl()->get_keys() as $key ){
				$px->bowl()->replace( '{$'.$key.'}', $key );
			}
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
		if( !is_file($this->realpath_script) ){
			$proc_types = array_keys( get_object_vars( $this->px->conf()->funcs->processor ) );
			foreach($proc_types as $proc_type){
				if( is_file($this->realpath_script.'.'.$proc_type) ){
					$this->realpath_script = $this->realpath_script.'.'.$proc_type;
					break;
				}
			}
		}
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
			dirname($this->realpath_script)
		);
		$paprika_env->realpath_controot_preview = $paprika_env->realpath_controot;
			// ↑プレビュー環境(パブリッシュ前)の controot を格納する。
		$paprika_env->realpath_homedir = $px->fs()->get_relatedpath(
			$px->get_realpath_homedir(),
			dirname($this->realpath_script)
		);
		$paprika_env->path_controot = $px->get_path_controot();
		$paprika_env->realpath_files = $px->fs()->get_relatedpath(
			$px->realpath_files(),
			dirname($this->realpath_script)
		);
		$paprika_env->realpath_files_cache = $px->fs()->get_relatedpath(
			$px->realpath_files_cache(),
			dirname($this->realpath_script)
		);
		$px->fs()->mkdir_r($px->realpath_files_cache()); // ←これをしないと、ページを持たないPHP(リソースフォルダ内など) でリンク切れが起きる。

		$paprika_env->realpath_files_private_cache = $px->realpath_files_private_cache();
		$this->paprika_env = $paprika_env;
	}

	/**
	 * $paprika を生成する
	 */
	private function paprika(){
		$proc_types = array_keys( get_object_vars( $this->px->conf()->funcs->processor ) );
		while( !is_file($this->realpath_script) ){
			foreach($proc_types as $proc_type){
				if( is_file($this->realpath_script.'.'.$proc_type) ){
					$this->realpath_script = $this->realpath_script.'.'.$proc_type;
					break 2;
				}
			}
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
	 * アプリケーションを初期化する
	 * 初期セットアップ時に1回だけ実行します。
	 */
	private function init(){
		echo 'Initialize Paprika...'."\n";
		$paprika = $this->paprika();

		// 共通の prepend スクリプトを実行
		if(is_file($paprika->env()->realpath_homedir.'prepend.php')){
			include($paprika->env()->realpath_homedir.'prepend.php');
		}

		// 外部より注入された初期化メソッドを実行する
		$paprika->execute_initialize_methods();

		echo 'done!'."\n";
		exit;
	}

	/**
	 * Execute PHP Contents
	 * @param object $conf プラグイン設定
	 * @return string 加工後の出力コード
	 */
	private function execute_php_contents($conf){
		if($this->px->req()->get_param('PX') == 'paprika.publish_template'){
			// PX=paprika.publish_template は、テンプレートソースを出力するリクエストにつけられるパラメータ。
			// テンプレート生成時には、通常のHTMLと同様に振る舞うべきなので、処理をしない。
			$this->px->bowl()->replace('{$main}', 'main');
			if( property_exists($conf, 'bowls') && is_array($conf->bowls) ){
				foreach($conf->bowls as $bowl_name){
					$this->px->bowl()->replace('{$'.$bowl_name.'}', $bowl_name);
				}
			}
			return;
		}

		$px = $this->px;

		$src = '';
		if( $this->px->is_publish_tool() ){
			// --------------------
			// パブリッシュ時

			// 一度実行して、テンプレートを生成させる
			if( $this->current_page_info ){
				$output_json = $this->px->internal_sub_request(
					$this->path_script,
					array(
						'output'=>'json',
						'user_agent'=>'Mozilla/1.0'
					)
				);
				foreach($output_json->relatedlinks as $url){
					$this->px->add_relatedlink($url);
				}

				// テンプレートが存在するなら、パブリッシュ先に加える
				if(is_file($this->px->realpath_files_cache('/paprika/template'))){
					$this->px->add_relatedlink( $this->px->path_files_cache('/paprika/template') );
				}
			}

			// 内部パス情報の再計算
			// 相対パスで捉え直す。
			$tmp_realpath_script = dirname($px->fs()->get_realpath($this->px->conf()->path_publish_dir.$this->path_script));
			$this->paprika_env->realpath_controot_preview = $px->fs()->get_relatedpath(
				$px->get_realpath_docroot().$px->get_path_controot(),
				$tmp_realpath_script
			);
			$this->paprika_env->realpath_homedir = $px->fs()->get_relatedpath(
				$px->get_realpath_homedir(),
				$tmp_realpath_script
			);
			$this->paprika_env->realpath_files_private_cache = $px->fs()->get_relatedpath(
				$px->realpath_files_private_cache(),
				$tmp_realpath_script
			);

			$header_template = file_get_contents( __DIR__.'/resources/dist_src/header.php.template' );
			$header_template = str_replace( '{$paprika_env}', escapeshellarg(json_encode($this->paprika_env,JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)), $header_template );
			$src .= $header_template;
			$src .= file_get_contents( $this->realpath_script );
			$footer_template = file_get_contents( __DIR__.'/resources/dist_src/footer.php.template' );
			if( !$this->is_php_closed($src) ){
				$src .= '?'.'>';
			}
			$src .= $footer_template;

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

			// 共通の prepend スクリプトを実行
			if(is_file($paprika->env()->realpath_homedir.'prepend.php')){
				include($paprika->env()->realpath_homedir.'prepend.php');
			}

			// コンテンツを実行
			ob_start();
			include( $this->realpath_script );
			$content = ob_get_clean();
			if(strlen($content)){
				$paprika->bowl()->put($content);
			}

			$src = $paprika->bowl()->bind_template();
		}

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

		exit();
	}

	/**
	 * PHPコードブロックが閉じられているか確認する
	 * @return boolean 閉じられている場合に `true` 閉じられていない場合に `false`。
	 */
	private function is_php_closed( $php ){
		preg_match_all( '/\<\?(?:php|\=)?|\?\>/s', $php, $matches );
		if( count($matches) && count($matches[0]) ){
			if( $matches[0][(count($matches[0])-1)] != '?'.'>' ){
				return false;
			}
		}
		return true;
	}
}
