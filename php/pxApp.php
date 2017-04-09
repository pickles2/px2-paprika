<?php
/**
 * Pickles Application Framework
 */
namespace tomk79\pickles2\px2_webapp_fw_2;

/**
 * pxApp.php
 */
class pxApp{

	/** config object */
	private $conf;

	/**
	 * constructor
	 * @param object $px Picklesオブジェクト
	 */
	public function __construct( $conf ){
		$this->conf = $conf;
	}

	/**
	 * 設定を取得する
	 * @return object 設定オブジェクト
	 */
	public function conf(){
		return $this->conf;
	}

}
