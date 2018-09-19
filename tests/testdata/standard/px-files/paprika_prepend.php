<?php
/** database Config */
$conf_database = new stdClass;
$conf_database->dbms = 'sqlite';
$conf_database->host = __DIR__.'/_sys/ram/data/database.sqlite';
$conf_database->port = null;
$conf_database->dbname = null;
$conf_database->username = null;
$conf_database->password = null;

if($conf_database->dbms == 'sqlite' || $conf_database->dbms == 'sqlite2'){
	$conf_database->host = $paprika->fs()->get_realpath($conf_database->host);
}
$paprika->set_conf('database', $conf_database);


/** Excellent DB Config */
$conf_exdb = new stdClass;
$conf_exdb->prefix = 'paprika';
$conf_exdb->path_definition_file = __DIR__.'/db/db.xlsx';
$conf_exdb->path_cache_dir = __DIR__.'/db/exdb_caches/';
@mkdir(__DIR__.'/_sys/ram/caches/exdb/');

$conf_exdb->path_definition_file = $paprika->fs()->get_realpath($conf_exdb->path_definition_file);
$conf_exdb->path_cache_dir = $paprika->fs()->get_realpath($conf_exdb->path_cache_dir);
$paprika->set_conf('exdb', $conf_exdb);


// 初期化処理を追加
$paprika->add_initialize_method(function() use ($paprika){
	$exdb = $paprika->exdb();

	// データベーステーブルを初期化
	echo 'Migrate Database tables...'."\n";
	$exdb->migrate_init_tables();
});


/**
 * ログインユーザー情報欄にソースを出力する
 */
$paprika->bowl()->put('<p>Login User: test</p>', 'login_user');


/**
 * 機能拡張: フォームコントロールを生成する
 */
require_once(__DIR__.'/control/form.php');
$paprika->add_custom_method('form', function() use ($paprika){
	return new \tomk79\pickles2\paprikaFramework2\control_form($paprika);
});

/**
 * 機能拡張: Excellent DB オブジェクトを生成する
 * @return object $exdb オブジェクト
 */
$paprika->add_custom_method('exdb', function() use ($paprika){
	static $exdb;
	if( is_object($exdb) ){
		// すでに生成済みならそれを返す
		return $exdb;
	}

	// Database Access
	$pdo = null;
	if( is_object(@$paprika->conf('database')) && strlen(@$paprika->conf('database')->dbms) ){
		$pdo = new \PDO(
			$paprika->conf('database')->dbms.':'.$paprika->conf('database')->host,
			null, null
		);
	}

	// Excellent DB
	$exdb = new \excellent_db\create(
		$pdo,
		json_decode(json_encode($paprika->conf('exdb')), true)
	);

	return $exdb;
});
