<?php
/**
 * Pickles 2 - Paprika Framework
 */
namespace tomk79\pickles2\paprikaFramework2;

/**
 * paprika.php
 */
class paprika{

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
