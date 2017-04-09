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

### `config.php` を開き、プラグインを設定します。

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


## ライセンス - License

MIT License


## 作者 - Author

- (C)Tomoya Koyanagi <tomk79@gmail.com>
- website: <http://www.pxt.jp/>
- Twitter: @tomk79 <http://twitter.com/tomk79/>
