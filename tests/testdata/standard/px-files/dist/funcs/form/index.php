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

// 共通の prepend スクリプトを実行
if(is_file($paprika->env()->realpath_homedir.'prepend.php')){
    include($paprika->env()->realpath_homedir.'prepend.php');
}
?>
<?php
if( !isset($paprika) ){
	return;
}

$form = $paprika->form();
$content = $form->form([
	[
		"name"=> [
			"type"=> "text",
			"label"=> "名前",
			"description"=>"お名前を入力してください。",
			"required"=>true,
			"min"=>4,
			"max"=>12,
		],
		"email"=> [
			"type"=> "email",
			"label"=> "メールアドレス",
			"required"=>true,
		],
		"comment"=> [
			"type"=> "textarea",
			"label"=> "コメント",
		],
	],
], [
	"name" => "漬物 太郎",
	"comment" => "ノーコメントです。"
], function($paprika, $user_input_values){
	// 成功したら true を返します。
	// 失敗時には、 失敗画面に表示するHTMLを返してください。
	// var_dump($user_input_values);
	// return '<p style="color: #f00;">失敗しました。</p>';
	return true;
}, [
]);

$paprika->bowl()->put($content);

echo $paprika->bowl()->bind_template();
exit();
