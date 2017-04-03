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
	public static function exec( $px ){
		$me = new self( $px );
		$px->bowl()->each( array($me, 'apply') );
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
	 * @param string $src HTML, CSS, JavaScriptなどの出力コード
	 * @param string $current_path コンテンツのカレントディレクトリパス
	 * @return string 加工後の出力コード
	 */
	public function apply($src, $current_path = null){
		if( is_null($current_path) ){
			$current_path = $this->px->req()->get_request_file_path();
		}

		return $src;
	}

}
