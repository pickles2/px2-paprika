<?php
if( !isset($paprika) ){
	echo '{$main_contents}'."\n";
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
	"cancel" => "",
	"next" => "",
]);

$tpl = $paprika->bind_template(
	array('{$main_contents}'=>$content)
);
echo $tpl;
exit();
