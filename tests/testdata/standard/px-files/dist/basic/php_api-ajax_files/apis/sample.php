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

$paprika = new \tomk79\pickles2\paprikaFramework2\paprika(json_decode('{"file_default_permission":"775","dir_default_permission":"775","filesystem_encoding":"UTF-8","session_name":"PXSID","session_expire":1800,"directory_index":["index.html"],"realpath_controot":"../../../","realpath_controot_preview":"../../../../../","realpath_homedir":"../../../../","path_controot":"/","realpath_files":"./sample_files/","realpath_files_cache":"../../../caches/c/basic/php_api-ajax_files/apis/sample_files/","realpath_files_private_cache":"../../../../_sys/ram/caches/c/basic/php_api-ajax_files/apis/sample_files/"}'), false);

// 共通の prepend スクリプトを実行
if(is_file($paprika->env()->realpath_homedir.'paprika_prepend.php')){
    include($paprika->env()->realpath_homedir.'paprika_prepend.php');
}

// コンテンツが標準出力する場合があるので、それを拾う準備
ob_start();
?>
<?php
// AJAX API の実装サンプル
@header('Content-type: text/json');


$pdo = new PDO(
    $paprika->conf('database')->dbms.':'.$paprika->conf('database')->host,
    null,
    null,
    array(\PDO::ATTR_PERSISTENT => false)
);

$obj = array();
$obj['_SERVER'] = $_SERVER;
$obj['paprika'] = $paprika;
$obj['paprikaConf'] = array(
    'database'=>$paprika->conf('database'),
    'exdb'=>$paprika->conf('exdb'),
);
$obj['realpath_current_dir'] = $paprika->fs()->get_realpath('./');
echo json_encode( $obj );
exit;
?><?php
$content = ob_get_clean();
if(strlen($content)){
    $paprika->bowl()->put($content);
}
echo $paprika->bowl()->bind_template();
exit;
?>
