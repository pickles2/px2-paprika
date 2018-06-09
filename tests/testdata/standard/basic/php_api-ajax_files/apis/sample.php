<?php
// AJAX API の実装サンプル
@header('Content-type: text/json');


$pdo = new PDO(
    $paprika->conf()->database->dbms.':'.$paprika->conf()->database->host,
    null,
    null,
    array(\PDO::ATTR_PERSISTENT => false)
);

$obj = array();
$obj['_SERVER'] = $_SERVER;
$obj['paprika'] = $paprika;
$obj['paprikaConf'] = $paprika->conf();
$obj['realpath_current_dir'] = $paprika->fs()->get_realpath('./');
echo json_encode( $obj );
exit;
