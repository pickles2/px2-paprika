<?php
// AJAX API の実装サンプル
@header('Content-type: text/json');
$obj = array();
$obj['_SERVER'] = $_SERVER;
echo json_encode( $obj );
exit;
