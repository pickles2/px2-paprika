<?php
/**
 * config_paprika.php template
 */
return call_user_func( function(){

	// initialize

	/** コンフィグオブジェクト */
	$conf_paprika = new stdClass;

	/** database setting */
	$conf_paprika->database = new stdClass;
	$conf_paprika->database->dbms = 'sqlite';
	$conf_paprika->database->host = './px-files/_sys/ram/data/database.sqlite';
	$conf_paprika->database->port = null;
	$conf_paprika->database->dbname = null;
	$conf_paprika->database->username = null;
	$conf_paprika->database->password = null;

	/** Excellent DB Config */
	$conf_paprika->exdb = new stdClass;
	$conf_paprika->exdb->prefix = 'paprika';
	$conf_paprika->exdb->path_definition_file = './px-files/db/db.xlsx';
	$conf_paprika->exdb->path_cache_dir = './px-files/_sys/ram/caches/exdb/';
	@mkdir('./px-files/_sys/ram/caches/exdb/');

	return $conf_paprika;
} );
