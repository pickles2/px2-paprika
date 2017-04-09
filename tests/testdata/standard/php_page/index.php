<?php



// -----------------------------------
// 出力するHTMLコンテンツを生成
// あるいは、動的な処理を実装する
$content = '';
$content .= '<p>テンプレート中の文字列 <code>{$main_contents}</code> を、HTMLコードに置き換えます。</p>'."\n";
$content .= '<p>アプリケーションの動的な処理を実装することもできます。</p>'."\n";


$tpl = $paprika->bind_template(array('{$main_contents}'=>$content), '/php_page/index_files/templates/index.html');

// -----------------------------------
// 出力して終了する
echo $tpl;
exit();
