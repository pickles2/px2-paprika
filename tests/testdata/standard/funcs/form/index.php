<?php
if( !isset($paprika) ){
	echo '{$main_contents}'."\n";
	return;
}

require_once(__DIR__.'/index_files/prototype_form.php.inc');
$form = new form($paprika);
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
], function($input){
	return $input;
}, [
	"cancel" => "",
	"next" => "",
]);

$tpl = $paprika->bind_template(
	array('{$main_contents}'=>$content)
);
echo $tpl;
exit();
