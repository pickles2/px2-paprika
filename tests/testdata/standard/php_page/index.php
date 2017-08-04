<?php

if( $px ){
	if($px->req()->get_param('PX') == 'paprika.publish_template'){
		echo '{$main_contents}'."\n";
		return;
	}


	$current_page_path = $px->req()->get_request_file_path();
	$tpl = $px->internal_sub_request(
		$current_page_path.'?PX=paprika.publish_template',
		array(
			'user_agent'=>'PicklesCrawler'
		)
	);

	$path_files = $px->realpath_files();
	$path_template = $path_files.'paprika/template';
	$px->fs()->mkdir_r( dirname($path_template) );
	$px->fs()->save_file( $path_template, $tpl );
}



// -----------------------------------
// 出力するHTMLコンテンツを生成
// あるいは、動的な処理を実装する
$content = '';
$content .= '<p>テンプレート中の文字列 <code>{$main_contents}</code> を、HTMLコードに置き換えます。</p>'."\n";
$content .= '<p>アプリケーションの動的な処理を実装することもできます。</p>'."\n";

$tpl = file_get_contents(__DIR__.'/index_files/paprika/template');
$content = str_replace('{$main_contents}', $content, $tpl);

echo $content;
exit();
