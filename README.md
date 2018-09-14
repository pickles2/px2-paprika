# Paprika Framework 2 for Pickles 2
Publishing Web Application with "Pickles 2".


## Setup - セットアップ手順

### [Pickles 2 プロジェクト](http://pickles2.pxt.jp/) をセットアップ

### 1. `composer.json` に、パッケージ情報を追加

```json
{
    "repositories": [
        {
            "type": "git",
            "url": "https://github.com/tomk79/px2-paprika-fw-2.x.git"
        }
    ],
    "require": {
        "tomk79/px2-paprika-fw-2.x": "dev-master"
    }
}
```

### 2. `composer update` を実行

更新したパッケージ情報を反映します。

```
$ composer update
```


### 3. `.htaccess` を開き、 `RewriteCond` の条件に 拡張子 `.php` を追加

```
#-------------------------
#  for pickles2
<IfModule mod_rewrite.c>

	# ...中略...

	RewriteCond %{REQUEST_URI} /(.*?\.(?:html|htm|css|js|php(?:/.*)?))?$
	RewriteRule ^(.*)$ \.px_execute\.php/$1 [L]

	# ...中略...

</IfModule>
```

### 4. `px-files/config.php` を開き、プラグインを設定

#### `paths_proc_type` を設定

`*.php` と `*.php/*` を追加する。

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

		'*.php' => 'php' , // <- add
		'*.php/*' => 'php' , // <- add

		// ...中略...
	);
```

#### `paths_enable_sitemap` を設定

`*.php` と `*.php/*` を追加する。

```php
	// 拡張子 `*.php` で、サイトマップを有効化
	$conf->paths_enable_sitemap = array(
		// ...中略...

		'*.php', // <- add (for Paprika)
		'*.php/*', // <- add (for Paprika)

	);
```

#### `funcs->before_content` を設定

```php
	/**
	 * funcs: Before content
	 */
	$conf->funcs->before_content = array(
		// ...中略...

		// Paprika - PHPアプリケーションフレームワーク
		'tomk79\pickles2\paprikaFramework2\main::exec()' ,

		// ...中略...
	);

	// php 用のプロセッサを追加

```

#### `funcs->processor->php` 設定を追加

```php
	/**
	 * processor
	 */
	$conf->funcs->processor->php = array(
		// html のデフォルトの処理を追加
		$conf->funcs->processor->html ,
	);
```

### 5. `px-files/config_paprika.php` を作成し、 Paprika Framework 2 を設定

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

	/** Excellent DB Config */
	$conf_paprika->exdb = new stdClass;
	$conf_paprika->exdb->prefix = 'paprika';
	$conf_paprika->exdb->path_definition_file = './px-files/db/db.xlsx';
	$conf_paprika->exdb->path_cache_dir = './px-files/_sys/ram/caches/exdb/';
	@mkdir('./px-files/_sys/ram/caches/exdb/');

	return $conf_paprika;
} );
```

## PXコマンド - PX Commands

- `paprika.init` - データベースを初期化する。

## ライセンス - License

MIT License


## 作者 - Author

- (C)Tomoya Koyanagi <tomk79@gmail.com>
- website: <http://www.pxt.jp/>
- Twitter: @tomk79 <http://twitter.com/tomk79/>
