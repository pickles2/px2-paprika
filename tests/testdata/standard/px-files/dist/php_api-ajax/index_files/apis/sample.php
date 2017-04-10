<?php
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

$paprika = new \tomk79\pickles2\paprikaFramework2\paprika(json_decode('{"file_default_permission":"775","dir_default_permission":"775","filesystem_encoding":"UTF-8","session_name":"PXSID","session_expire":1800,"directory_index":["index.html"],"realpath_controot":"../../../","realpath_homedir":"../../../../","path_controot":"/"}'), false);

?>
<?php
// AJAX API の実装サンプル
@header('Content-type: text/json');
$obj = array();
$obj['_SERVER'] = $_SERVER;
$obj['paprika'] = $paprika;
$obj['paprikaConf'] = $paprika->conf();
$obj['realpath_current_dir'] = $paprika->fs()->get_realpath('.');
echo json_encode( $obj );
exit;
