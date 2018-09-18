<?php
if( !isset($paprika) ){
	return;
}

$form = $paprika->form();
$content = $form->form([
	[
		"title"=> [
			"type"=> "text",
			"label"=> "タイトル",
			"description"=>"タイトルを入力してください。",
			"required"=>true,
			"min"=>4,
			"max"=>18,
		],
		"description"=> [
			"type"=> "textarea",
			"label"=> "説明",
		],
	],
], null, function($paprika, $user_input_values){
	// 成功したら true を返します。
	// 失敗時には、 失敗画面に表示するHTMLを返してください。
	// var_dump($user_input_values);
	// return '<p style="color: #f00;">失敗しました。</p>';
	$exdb = $paprika->exdb();
	$result = $exdb->insert('insert_test', [
		'record_title'=>$user_input_values['title'],
		'description'=>$user_input_values['description'],
	]);

	if(!$result){
		return '<p style="color: #f00;">失敗しました。</p>';
	}

	return true;
}, [
]);

$paprika->bowl()->put($content);

echo $paprika->bowl()->bind_template();
exit();
