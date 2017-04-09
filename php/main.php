<?php
/**
 * px2-webapp-fw-2.x
 */
namespace tomk79\pickles2\px2_webapp_fw_2;

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
	 * @param object $json プラグインオプション
	 * @return string 加工後の出力コード
	 */
	public function apply($json){
		$px = $this->px;
		$proc_type = $this->px->get_path_proc_type();
		$current_path = $this->px->req()->get_request_file_path();
		$realpath_script = $this->px->fs()->get_realpath($this->px->get_realpath_docroot().$this->px->get_path_controot().$current_path);
		// var_dump($proc_type);
		// var_dump($current_path);
		// var_dump($realpath_script);

		$src = '';
		if( $this->px->is_publish_tool() ){
			// パブリッシュ時
			$src .= file_get_contents( __DIR__.'/resources/dist_src/header.php' );
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
			// プレビュー時
			include( $realpath_script );
		}

		exit();
	}

}
