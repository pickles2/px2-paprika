<?php
/**
 * Test for pickles2/px2-webapp-fw-2.x
 */

class mainTest extends PHPUnit_Framework_TestCase{

	/**
	 * setup
	 */
	public function setup(){
		$this->fs = new \tomk79\filesystem();
	}

	/**
	 * プレビュー表示時のテスト
	 */
	public function testPreview(){

		// トップページの出力コードを検査
		$indexHtml = $this->passthru( [
			'php',
			__DIR__.'/testdata/standard/.px_execute.php' ,
			'-u', 'Mozilla/0.5',
			'/index.html' ,
		] );
		// var_dump($indexHtml);


		// 後始末
		$output = $this->passthru( [
			'php',
			__DIR__.'/testdata/standard/.px_execute.php' ,
			'/?PX=clearcache' ,
		] );

	}//testPreview()



	/**
	 * コマンドを実行し、標準出力値を返す
	 * @param array $ary_command コマンドのパラメータを要素として持つ配列
	 * @return string コマンドの標準出力値
	 */
	private function passthru( $ary_command ){
		$cmd = array();
		foreach( $ary_command as $row ){
			$param = '"'.addslashes($row).'"';
			array_push( $cmd, $param );
		}
		$cmd = implode( ' ', $cmd );
		ob_start();
		passthru( $cmd );
		$bin = ob_get_clean();
		return $bin;
	}// passthru()

}
