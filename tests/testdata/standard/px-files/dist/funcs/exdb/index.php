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

$paprika = new \tomk79\pickles2\paprikaFramework2\paprika(json_decode('{"file_default_permission":"775","dir_default_permission":"775","filesystem_encoding":"UTF-8","session_name":"PXSID","session_expire":1800,"directory_index":["index.html"],"realpath_controot":"../../","realpath_controot_preview":"../../../../","realpath_homedir":"../../../","path_controot":"/","realpath_files":"./index_files/","realpath_files_cache":"../../caches/c/funcs/exdb/index_files/","realpath_files_private_cache":"../../../_sys/ram/caches/c/funcs/exdb/index_files/"}'), false);

// 共通の prepend スクリプトを実行
if(is_file($paprika->env()->realpath_homedir.'paprika_prepend.php')){
    include($paprika->env()->realpath_homedir.'paprika_prepend.php');
}

// コンテンツが標準出力する場合があるので、それを拾う準備
ob_start();
?>
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
?><?php
$content = ob_get_clean();
if(strlen($content)){
    $paprika->bowl()->put($content);
}
echo $paprika->bowl()->bind_template();
exit;
?>
