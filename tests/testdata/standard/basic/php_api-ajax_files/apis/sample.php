<?php
// AJAX API の実装サンプル
@header('Content-type: text/json');

$obj = array();
$obj['_SERVER'] = $_SERVER;
$obj['paprika'] = $paprika;
$obj['paprikaConf'] = array(
    'undefined'=>$paprika->conf('undefined'),
    'sample1'=>$paprika->conf('sample1'),
    'sample2'=>$paprika->conf('sample2'),
    'sample3'=>$paprika->conf('sample3'),
);
$obj['realpath_current_dir'] = $paprika->fs()->get_realpath('./');
echo json_encode( $obj );
exit;
