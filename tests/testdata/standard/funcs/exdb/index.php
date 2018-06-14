<?php
if( !isset($paprika) ){
	echo '{$main_contents}'."\n";
	return;
}

$exdb = $paprika->exdb();
$records = $exdb->select('insert_test', array(), array());

ob_start();
echo '<pre><code>';
var_dump($records);
echo '</code></pre>';
$content = ob_get_clean();

$tpl = $paprika->bind_template(
	array('{$main_contents}'=>$content)
);
echo $tpl;
exit();
