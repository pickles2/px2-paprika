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
