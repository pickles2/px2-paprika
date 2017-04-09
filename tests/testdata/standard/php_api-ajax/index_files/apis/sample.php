<?php
// AJAX API の実装サンプル
@header('Content-type: text/json');
$obj = array();
$obj['_SERVER'] = $_SERVER;
$obj['pxApp'] = $pxApp;
$obj['pxAppConf'] = $pxApp->conf();
echo json_encode( $obj );
exit;
