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

$paprika = new \tomk79\pickles2\paprikaFramework2\paprika(json_decode('{"file_default_permission":"775","dir_default_permission":"775","filesystem_encoding":"UTF-8","session_name":"PXSID","session_expire":1800,"directory_index":["index.html"],"realpath_controot":"../","realpath_controot_preview":"../../../","realpath_homedir":"../../","path_controot":"/","realpath_files":"./php_page_files/","realpath_files_cache":"../caches/c/basic/php_page_files/","realpath_files_private_cache":"../../_sys/ram/caches/c/basic/php_page_files/"}'), false);

// 共通の prepend スクリプトを実行
if(is_file($paprika->env()->realpath_homedir.'paprika_prepend.php')){
    include($paprika->env()->realpath_homedir.'paprika_prepend.php');
}

// コンテンツが標準出力する場合があるので、それを拾う準備
ob_start();

// コンテンツを実行する
// クロージャーの中で実行
$execute_php_content = function()use($paprika){
?>
<?php
// -----------------------------------
// 1. テンプレート生成のリクエストに対する処理
// テンプレート生成時には `$paprika` は生成されず、
// 通常のHTMLコンテンツと同様に振る舞います。
// アプリケーションは、後でテンプレート中のコンテンツエリアのコードを置き換えるため、
// キーワード `{$main}` を出力しておきます。
if( !isset($paprika) ){
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

<h2>realpath( '.' );</h2>
<p>パブリッシュの前後でパスが変わります。</p>
<pre><code><?= realpath( '.' ); ?></code></pre>

<h2>$_SERVER['PATH_INFO']</h2>
<pre><code><?= htmlspecialchars( @$_SERVER['PATH_INFO'] ); ?></code></pre>

<h2>$_SERVER</h2>
<pre><code><?php var_dump( $_SERVER ); ?></code></pre>

<h2>Current page info</h2>
<p>パブリッシュ後のコードは <code>$px</code> にアクセスできません。 ページ情報にアクセスできるのはパブリッシュ前だけです。</p>
<?php if(isset($px) && $px->site()){ ?>
<pre><code><?php var_dump( $px->site()->get_current_page_info() ); ?></code></pre>
<?php }else{ ?>
<pre><code>$px が存在しません。</code></pre>
<?php } ?>

<?php
$content .= ob_get_clean();

// -----------------------------------
// 3. テンプレートにバインド
// テンプレート生成時に埋め込んだキーワード `{$main}` を、
// 生成したコンテンツのHTMLコードに置き換えます。
// echo $content;
$paprika->bowl()->put($content);
?><?php
};
$execute_php_content();
$content = ob_get_clean();
if(strlen($content)){
    $paprika->bowl()->put($content);
}
echo $paprika->bowl()->bind_template();
exit;
?>
