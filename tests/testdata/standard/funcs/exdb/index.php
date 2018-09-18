<?php
if( !isset($paprika) ){
	return;
}

$exdb = $paprika->exdb();
$records = $exdb->select('insert_test', array(), array());

ob_start();
echo '<pre><code>';
var_dump($records);
echo '</code></pre>';
$content = ob_get_clean();

$paprika->bowl()->put($content);
