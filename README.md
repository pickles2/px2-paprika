# Paprika Framework 2 for Pickles 2
Publishing Web Application with "Pickles 2".


## Setup - セットアップ手順

### `.htaccess` を開き、 RewriteCond の条件に 拡張子 `.php` を追加します。

```
#-------------------------
#  for pickles2
<IfModule mod_rewrite.c>

	# ...中略...

	RewriteCond %{REQUEST_URI} /(.*?\.(?:html|htm|css|js|php))?$
	RewriteRule ^(.*)$ \.px_execute\.php/$1 [L]

	# ...中略...

</IfModule>
```

### `px-files/config.php` を開き、プラグインを設定します。

```php
<?php
/**
 * config.php template
 */
return call_user_func( function(){

	// ...中略...

	// 拡張子 `*.php` を、php用プロセッサに関連付け
	$conf->paths_proc_type = array(
		// ...中略...

		'*.php' => 'php' ,

		// ...中略...
	);

	// ...中略...

	// php 用のプロセッサを追加

	/**
	 * funcs: Before content
	 */
	$conf->funcs->before_content = array(
		// ...中略...

		// PHPアプリケーションフレームワーク
		'tomk79\pickles2\paprikaFramework2\main::exec()' ,

		// ...中略...
	);

```

### `px-files/config_paprika.php` を作成し、 Paprika Framework 2 を設定します。

```php
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
```


## ライセンス - License

MIT License


## 作者 - Author

- (C)Tomoya Koyanagi <tomk79@gmail.com>
- website: <http://www.pxt.jp/>
- Twitter: @tomk79 <http://twitter.com/tomk79/>
