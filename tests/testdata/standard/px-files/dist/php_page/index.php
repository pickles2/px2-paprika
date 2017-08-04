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
