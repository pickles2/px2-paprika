<?php
// デフォルトのHTTPレスポンスヘッダー
@header('Content-type: text/html');

// autoload.php をロード
$tmp_path_autoload = __DIR__;
while(1){
    if( is_file( $tmp_path_autoload.'/vendor/autoload.php' ) ){
        require_once( $tmp_path_autoload.'/vendor/autoload.php' );
        break;
    }

    if( $tmp_path_autoload == dirname($tmp_path_autoload) ){
        // これ以上、上の階層がない。
        break;
    }
    $tmp_path_autoload = dirname($tmp_path_autoload);
    continue;
}
unset($tmp_path_autoload);

$paprika = new \tomk79\pickles2\paprikaFramework2\paprika(json_decode("{}"));

?>
<?php

// -----------------------------------
// テンプレートを取得する
$tpl = '';
if( is_file( './.px_execute.php' ) ){
	// is preview
	// .px_execute.php が存在する場合は、プレビュー環境だと判断。
	ob_start();
	passthru(implode( ' ', array(
		'php',
		'./.px_execute.php',
		'/php_page/index_files/templates/index.html'
	) ));
	$tpl = ob_get_clean();
}else{
	// is finalized
	// .px_execute.php が存在しなければ、パブリッシュ後の実行であると判断。
	$tpl = file_get_contents( __DIR__.'/index_files/templates/index.html' );
}


// -----------------------------------
// 出力するHTMLコンテンツを生成
// あるいは、動的な処理を実装する
$content = '';
$content .= '<p>テンプレート中の文字列 <code>{$main_contents}</code> を、HTMLコードに置き換えます。</p>'."\n";
$content .= '<p>アプリケーションの動的な処理を実装することもできます。</p>'."\n";


// -----------------------------------
// テンプレートにHTMLをバインドする
$tpl = str_replace( '{$main_contents}', $content, $tpl );


// -----------------------------------
// 出力して終了する
echo $tpl;
exit();
