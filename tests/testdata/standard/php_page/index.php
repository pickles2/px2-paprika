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
<p>テンプレート中の文字列 <code>{$main_contents}</code> を、HTMLコードに置き換えます。</p>
<p>アプリケーションの動的な処理を実装することもできます。</p>
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
