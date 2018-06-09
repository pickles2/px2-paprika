<?php
// chdir
chdir(__DIR__);

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

$paprika = new \tomk79\pickles2\paprikaFramework2\paprika(json_decode('{"file_default_permission":"775","dir_default_permission":"775","filesystem_encoding":"UTF-8","session_name":"PXSID","session_expire":1800,"directory_index":["index.html"],"realpath_controot":"../../","realpath_controot_preview":"../../../../","realpath_homedir":"../../../","path_controot":"/","realpath_files":"./index_files/"}'), false);

?>
<?php
// -----------------------------------
// 1. テンプレート生成のリクエストに対する処理
// テンプレート生成時には `$paprika` は生成されず、
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
ob_start(); ?>
<p>この方法は、 コンテンツ自体を動的なPHPプログラムとして実装し、パブリッシュ後の環境でも同様に動作する仕組みです。</p>
<p>プレビュー環境では、動的に処理されたコンテンツを動的にテーマに包んで出力します。パブリッシュ後には、テーマを含んだテンプレートが別途出力され、これに動的な成果物をバインドして画面に出力するように振る舞います。</p>
<p>プログラマーは、コンテンツの処理の最初と最後に規定の処理を埋め込む必要がありますが、それ以外は直感的なPHPプログラムでウェブアプリケーションを実装できます。</p>
<p>グローバル空間に <code>$paprika</code> が自動的にロードされます。</p>

<p>次の例は、動的に環境変数を出力するサンプルです。</p>

<pre><code><?= realpath( '.' ); ?></code></pre>
<pre><code><?= htmlspecialchars( @$_SERVER['PATH_INFO'] ); ?></code></pre>
<pre><code><?php var_dump( $_SERVER ); ?></code></pre>
<?php if(isset($px) && $px->site()){ ?>
<pre><code><?php var_dump( $px->site()->get_current_page_info() ); ?></code></pre>
<?php } ?>
<?php
$content .= ob_get_clean();

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
