<?php
/**
 * class log
 *
 * Paprika Framework のコアオブジェクトの1つ `$log` のオブジェクトクラスを定義します。
 *
 * @author Tomoya Koyanagi <tomk79@gmail.com>
 */
namespace picklesFramework2\paprikaFramework\fw;

/**
 * Log
 *
 * Paprika Framework のコアオブジェクトの1つ `$log` のオブジェクトクラスです。
 * このオブジェクトは、Paprika Framework の初期化処理の中で自動的に生成され、`$paprika` の内部に格納されます。
 *
 * メソッド `$paprika->bowl()` を通じてアクセスします。
 *
 * @author Tomoya Koyanagi <tomk79@gmail.com>
 */
class log{

	/**
	 * Paprikaオブジェクト
	 */
	private $paprika;


	/**
	 * Constructor
	 */
	public function __construct( $paprika ){
		$this->paprika = $paprika;
	}

}
