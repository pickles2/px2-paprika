<?php
// AJAX API の実装サンプル
@header('Content-type: text/json');

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
