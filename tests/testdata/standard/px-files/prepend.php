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


/**
 * 機能拡張: フォームコントロールを生成する
 */
require_once(__DIR__.'/control/form.php');
$paprika->add_custom_method('form', function(){
	return new \tomk79\pickles2\paprikaFramework2\control_form($this);
});

/**
 * 機能拡張: Excellent DB オブジェクトを生成する
 * @return object $exdb オブジェクト
 */
$paprika->add_custom_method('exdb', function(){
	if( property_exists($this, 'exdb') && is_object($this->exdb) ){
		// すでに生成済みならそれを返す
		return $this->exdb;
	}

	// Database Access
	$pdo = null;
	if( is_object(@$this->conf('database')) && strlen(@$this->conf('database')->dbms) ){
		$pdo = new \PDO(
			$this->conf('database')->dbms.':'.$this->conf('database')->host,
			null, null
		);
	}

	// Excellent DB
	$this->exdb = new \excellent_db\create(
		$pdo,
		json_decode(json_encode($this->conf('exdb')), true)
	);

	return $this->exdb;
});
