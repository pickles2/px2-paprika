<?php
/**
 * Paprika `config.php`
 */
return call_user_func( function(){

	// initialize

	/** コンフィグオブジェクト */
	$conf = new stdClass;


	// Plugins
	$conf->prepend = [
		function($paprika){
			$paprika->set_conf('prepend1', 1);
		},
		function($paprika){
			$paprika->set_conf('prepend2', 'no-value');
		},
		function($paprika){
			$paprika->set_conf('prepend2', 2);
		},
		function($paprika){
			$paprika->add_custom_method('custom_function_a', function() use ($paprika){
				$paprika->set_conf('custom_func_a', 'called');
			});
		},
	];


	// -------- Project Custom Setting --------
	// プロジェクトが固有に定義する設定を行います。
	$conf->extra = new stdClass;



	/** サンプル1 */
	$conf->sample1 = 'config.php';

	/** サンプル2 */
	$conf->sample2 = new \stdClass();
	$conf->sample2->prop1 = 'config.php';
	$conf->sample2->prop2 = 'config.php';
	$conf->sample2->prop3 = 'config.php';

	/** サンプル3 */
	$conf->sample3 = 'config.php';


	// -------- PHP Setting --------

	/**
	 * `memory_limit`
	 *
	 * PHPのメモリの使用量の上限を設定します。
	 * 正の整数値で上限値(byte)を与えます。
	 *
	 *     例: 1000000 (1,000,000 bytes)
	 *     例: "128K" (128 kilo bytes)
	 *     例: "128M" (128 mega bytes)
	 *
	 * -1 を与えた場合、無限(システムリソースの上限まで)に設定されます。
	 * サイトマップやコンテンツなどで、容量の大きなデータを扱う場合に調整してください。
	 */
	// @ini_set( 'memory_limit' , -1 );

	/**
	 * `display_errors`, `error_reporting`
	 *
	 * エラーを標準出力するための設定です。
	 *
	 * PHPの設定によっては、エラーが発生しても表示されない場合があります。
	 * もしも、「なんか挙動がおかしいな？」と感じたら、
	 * 必要に応じてこれらのコメントを外し、エラー出力を有効にしてみてください。
	 *
	 * エラーメッセージは問題解決の助けになります。
	 */
	// @ini_set('display_errors', 1);
	// @ini_set('error_reporting', E_ALL);


	return $conf;
} );
