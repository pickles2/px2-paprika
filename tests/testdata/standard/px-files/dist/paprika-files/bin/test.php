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

$paprika = new \picklesFramework2\paprikaFramework\fw\paprika(json_decode('{"file_default_permission":"775","dir_default_permission":"775","filesystem_encoding":"UTF-8","session_name":"PXSID","session_expire":1800,"directory_index":["index.html"],"realpath_controot":"../../","realpath_homedir":"../","path_controot":"/","realpath_files":"./test_files/","realpath_files_cache":"../../caches/c/paprika-files/bin/test_files/","href":null,"page_info":null,"parent":null,"breadcrumb":null,"bros":null,"children":null}'), false);

// コンテンツが標準出力する場合があるので、それを拾う準備
ob_start();

// コンテンツを実行する
// クロージャーの中で実行
$execute_php_content = function()use($paprika){
?>
<?php
echo '---------------- test'."\n";

$paprika->log()->trace('Trace Test.');
$paprika->log()->debug('Debug Test.');
$paprika->log()->info('Info Test.');
$paprika->log()->warn('Warning Test.');
$paprika->log()->error('Error Test.');
$paprika->log()->fatal('Fatal Test.');

exit;
?><?php
};
$execute_php_content();
$content = ob_get_clean();
if(strlen($content)){
    $paprika->bowl()->put($content);
}
echo $paprika->bowl()->bind_template();
exit;
?>
