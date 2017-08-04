<?php
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

$paprika = new \tomk79\pickles2\paprikaFramework2\paprika(json_decode('{"file_default_permission":"775","dir_default_permission":"775","filesystem_encoding":"UTF-8","session_name":"PXSID","session_expire":1800,"directory_index":["index.html"],"realpath_controot":"../","realpath_controot_preview":"../../../","realpath_homedir":"../../","path_controot":"/","realpath_files":"./index_files/"}'), false);

?>
<?php
// -----------------------------------
// 1. テンプレート生成のリクエストに対する処理
// テンプレート生成自には `$paprika` は生成されず、
// 通常のHTMLコンテンツと同様に振る舞います。
// アプリケーションは、後でテンプレート中のコンテンツエリアのコードを置き換えるため、
// キーワード `{$main_contents}` を出力しておきます。
if( !isset($paprika) ){
	echo '{$main_contents}'."\n";
	return;
}

// -----------------------------------
// 2. 出力するHTMLコンテンツを生成
// あるいは、動的な処理を実装します。
$content = '';
$content .= '<p>テンプレート中の文字列 <code>{$main_contents}</code> を、HTMLコードに置き換えます。</p>'."\n";
$content .= '<p>アプリケーションの動的な処理を実装することもできます。</p>'."\n";

// -----------------------------------
// 3. テンプレートにバインド
// テンプレート生成時に埋め込んだキーワード `{$main_contents}` を、
// 生成したコンテンツのHTMLコードに置き換えます。
$tpl = $paprika->bind_template(
	array('{$main_contents}'=>$content)
);

// -----------------------------------
// 4. 出力して終了
echo $tpl;
exit();
