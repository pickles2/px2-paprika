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

	return $conf_paprika;
} );
