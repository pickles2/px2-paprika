<?php
// デフォルトのHTTPレスポンスヘッダー
@header('Content-type: text/html');

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

$pxApp = new \tomk79\pickles2\px2_webapp_fw_2\pxApp(json_decode("{\"test\":\"test\"}"));

?>
<?php
// AJAX API の実装サンプル
@header('Content-type: text/json');
$obj = array();
$obj['_SERVER'] = $_SERVER;
$obj['pxApp'] = $pxApp;
$obj['pxAppConf'] = $pxApp->conf();
echo json_encode( $obj );
exit;
