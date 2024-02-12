<?php
/**
 * px2-paprika
 */
namespace picklesFramework2\paprikaFramework;

/**
 * main.php
 */
class main{

	/**
	 * Picklesオブジェクト
	 */
	private $px;

	/**
	 * プラグイン設定オブジェクト
	 */
	private $plugin_conf;

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
	public static function before_content( $px = null, $conf = null ){
		if( count(func_get_args()) <= 1 ){
			return __CLASS__.'::'.__FUNCTION__.'('.( is_array($px) ? json_encode($px) : '' ).')';
		}

		// PX=paprika を登録
		$px->pxcmd()->register('paprika', function($px) use ($conf){
			$pxcmd = $px->get_px_command();
			if( ($pxcmd[1] ?? null) == '_' ){
				return;
			}

			$me = new self( $px, $conf );
			$paprika = $me->paprika();

			$command_name = $pxcmd[1] ?? '';
			echo $paprika->get_realpath_homedir().'commands/'.urlencode($command_name).'.php'."\n";
			if( !is_file($paprika->get_realpath_homedir().'commands/'.urlencode($command_name).'.php') ){
				echo 'Command not found.'."\n";
				exit();
			}

			include($paprika->get_realpath_homedir().'commands/'.urlencode($command_name).'.php');

			exit();
		});

		// コンテンツを処理
		$exts = array('php'); // Paprika を適用する拡張子の一覧
		if( is_object($conf) && property_exists($conf, 'exts') && is_array($conf->exts) ){
			$exts = $conf->exts;
		}
		$path_req = $px->req()->get_request_file_path();
		$path_content = $px->get_path_content();
		$proc_type = $px->get_path_proc_type();
		$is_paprika_target_contents = false;
		foreach($exts as $ext){
			if( $proc_type == $ext || preg_match('/\.(?:'.preg_quote($ext, '/').')$/', $path_content) ){
				$is_paprika_target_contents = true;
				break;
			}
		}
		if( $is_paprika_target_contents ){
			$me = new self( $px, $conf );
			$me->execute_php_contents();
			return;
		}
	}

	/**
	 * plugin - contents processor
	 * @param object $px Picklesオブジェクト
	 * @param object $conf プラグイン設定オブジェクト
	 */
	public static function processor( $px = null, $conf = null ){

		if( count(func_get_args()) <= 1 ){
			return __CLASS__.'::'.__FUNCTION__.'('.( is_array($px) ? json_encode($px) : '' ).')';
		}

		if($px->req()->get_param('PX') == 'paprika._.publish_template'){
			foreach( $px->bowl()->get_keys() as $key ){
				$px->bowl()->replace( '{$'.$key.'}', $key );
			}
		}
	}

	/**
	 * constructor
	 * @param object $px Picklesオブジェクト
	 * @param object $plugin_conf プラグイン設定
	 */
	public function __construct( $px, $plugin_conf ){
		$this->px = $px;
		$this->plugin_conf = $plugin_conf;

		$this->current_page_info = null;
		if( $px->site() ){
			$this->current_page_info = $px->site()->get_current_page_info();
		}
		$current_content_path = $this->px->req()->get_request_file_path();
		if( $this->current_page_info && strlen($this->current_page_info['content'] ?? '') ){
			$current_content_path = $this->current_page_info['content'];
		}
		$this->path_script = $this->px->fs()->get_realpath('/'.$this->px->get_path_controot().$current_content_path);
		$this->path_script = $this->px->fs()->normalize_path($this->path_script);
		$this->realpath_script = $this->px->fs()->get_realpath($this->px->get_realpath_docroot().$this->path_script);
		$this->realpath_script = $this->px->fs()->normalize_path($this->realpath_script);
		if( !is_file($this->realpath_script) ){
			$proc_types = array_keys( get_object_vars( $this->px->conf()->funcs->processor ) );
			foreach($proc_types as $proc_type){
				// 2重拡張子の場合に、実際のコンテンツファイルの名前を検索する
				if( is_file($this->realpath_script.'.'.$proc_type) ){
					$this->realpath_script = $this->realpath_script.'.'.$proc_type;
					break;
				}
			}
		}

		$pxcmd = $px->get_px_command();
		$current_dir = realpath('.').'/';
		if( $pxcmd[1]??'' == '_' ){
			$current_dir = realpath('.').'/';
		}elseif( $px->is_publish_tool() && strlen($px->conf()->path_publish_dir ?? '') ){
			$current_dir = dirname($px->conf()->path_publish_dir.$this->path_script);
		}else{
			$current_dir = dirname($this->realpath_script);
		}

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
		$paprika_env->realpath_controot = $px->fs()->normalize_path($paprika_env->realpath_controot);

		$paprika_env->realpath_homedir = $px->fs()->get_relatedpath(
			$px->get_realpath_homedir().'/paprika/',
			$current_dir
		);

		$paprika_env->realpath_homedir = $px->fs()->normalize_path($paprika_env->realpath_homedir);

		$paprika_env->path_controot = $px->get_path_controot();
		$paprika_env->path_controot = $px->fs()->normalize_path($paprika_env->path_controot);

		$paprika_env->realpath_files = $px->fs()->get_relatedpath(
			$px->realpath_files(),
			dirname($this->realpath_script)
		);
		$paprika_env->realpath_files = $px->fs()->normalize_path($paprika_env->realpath_files);

		$paprika_env->realpath_files_cache = $px->fs()->get_relatedpath(
			$px->realpath_files_cache(),
			dirname($this->realpath_script)
		);
		$paprika_env->realpath_files_cache = $px->fs()->normalize_path($paprika_env->realpath_files_cache);

		// pageinfo
		$paprika_env->href = null;
		$paprika_env->page_info = $this->current_page_info;
		$paprika_env->parent = null;
		$paprika_env->breadcrumb = null;
		$paprika_env->bros = null;
		$paprika_env->children = null;

		if( $px->site() && !is_null($paprika_env->page_info) ){
			$paprika_env->href = $px->href($paprika_env->page_info['path']);

			if( is_string($px->site()->get_parent()) ){
				$parent = $px->site()->get_page_info( $px->site()->get_parent() );
				$paprika_env->parent = array(
					'title' => $parent['title'],
					'title_label' => $parent['title_label'],
					'href' => $px->href($parent['path']),
				);
			}

			$paprika_env->breadcrumb = array();
			foreach($px->site()->get_breadcrumb_array() as $pid){
				$page_info = $px->site()->get_page_info( $pid );
				array_push($paprika_env->breadcrumb, array(
					'title' => $page_info['title'],
					'title_label' => $page_info['title_label'],
					'href' => $px->href($page_info['path']),
				));
			}
			$paprika_env->bros = array();
			foreach($px->site()->get_bros() as $pid){
				$page_info = $px->site()->get_page_info( $pid );
				array_push($paprika_env->bros, array(
					'title' => $page_info['title'],
					'title_label' => $page_info['title_label'],
					'href' => $px->href($page_info['path']),
				));
			}
			$paprika_env->children = array();
			foreach($px->site()->get_children() as $pid){
				$page_info = $px->site()->get_page_info( $pid );
				array_push($paprika_env->children, array(
					'title' => $page_info['title'],
					'title_label' => $page_info['title_label'],
					'href' => $px->href($page_info['path']),
				));
			}
		}

		$px->fs()->mkdir_r($px->realpath_files_cache()); // ←これをしないと、ページを持たないPHP(リソースフォルダ内など) でリンク切れが起きる。

		$this->paprika_env = $paprika_env;
	}

	/**
	 * $paprika を生成する
	 */
	private function paprika(){
		$proc_types = array_keys( get_object_vars( $this->px->conf()->funcs->processor ) );
		while( !is_file($this->realpath_script) ){
			foreach($proc_types as $proc_type){
				// 2重拡張子の場合に、実際のコンテンツファイルの名前を検索する
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
		$paprika = new fw\paprika($this->paprika_env, $this->px);
		return $paprika;
	}

	/**
	 * Execute PHP Contents
	 * @return string 加工後の出力コード
	 */
	private function execute_php_contents(){
		$conf = $this->plugin_conf;
		$px = $this->px;

		if($px->req()->get_param('PX') == 'paprika._.publish_template'){
			// PX=paprika._.publish_template は、テンプレートソースを出力するリクエストにつけられるパラメータ。
			// テンプレート生成時には、通常のHTMLと同様に振る舞うべきなので、
			// ここでコンテンツの処理は実行せず、Pickles 2 の自然なコンテンツ処理に進む。
			// ※ここでは $paprika を生成しない。
			// ※コンテンツは、 $paprika がないことを確認したら、コンテンツの処理をキャンセルする。
			$px->bowl()->replace('{$main}', 'main');
			if( property_exists($conf, 'bowls') && is_array($conf->bowls) ){
				foreach($conf->bowls as $bowl_name){
					$px->bowl()->replace('{$'.$bowl_name.'}', $bowl_name);
				}
			}
			return;
		}

		$src = '';
		if( $px->is_publish_tool() ){
			// --------------------
			// パブリッシュ時

			// 一度実行して、テンプレートを生成させる
			if( $this->current_page_info ){
				$output_json = $px->internal_sub_request(
					$this->path_script,
					array(
						'output'=>'json',
						'user_agent'=>'Mozilla/1.0'
					)
				);
				if(is_object($output_json) && property_exists($output_json, 'relatedlinks') && is_array($output_json->relatedlinks)){
					foreach($output_json->relatedlinks as $url){
						$px->add_relatedlink($url);
					}
				}

				// テンプレートが存在するなら、パブリッシュ先に加える
				if(is_file($px->realpath_files_cache('/paprika/template'))){
					$px->add_relatedlink( $px->path_files_cache('/paprika/template') );
				}
			}

			// 内部パス情報の再計算
			// 相対パスで捉え直す。
			$tmp_realpath_script = dirname($px->fs()->get_realpath($px->conf()->path_publish_dir.$this->path_script));

			$header_template = file_get_contents( __DIR__.'/resources/dist_src/header.php.template' );
			$header_template = str_replace( '{$paprika_env}', var_export(json_encode($this->paprika_env, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE), true), $header_template );
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
			if( array_key_exists('PATH_INFO', $_SERVER) && is_string($_SERVER['PATH_INFO']) ){
				$_SERVER['PATH_INFO'] = preg_replace('/^'.preg_quote($this->path_script, '/').'/', '', $_SERVER['PATH_INFO']);
			}

			// コンテンツを実行
			$content = '';
			if( is_file($this->realpath_script) ){
				ob_start();
				include( $this->realpath_script );
				$content = ob_get_clean();
			}
			if(strlen($content ?? '')){
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
	 * @param string $src_php 検査対象となるPHPソースコード
	 * @return boolean 閉じられている場合に `true` 閉じられていない場合に `false`。
	 */
	private function is_php_closed( $src_php ){
		preg_match_all( '/\<\?(?:php|\=)?|\?\>/s', $src_php, $matches );
		if( count($matches) && count($matches[0]) ){
			if( $matches[0][(count($matches[0])-1)] != '?'.'>' ){
				return false;
			}
		}
		return true;
	}
}
