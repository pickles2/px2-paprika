# Paprika Framework for Pickles 2

Publishing Web Application with "Pickles 2".


## Setup - セットアップ手順

### [Pickles 2 プロジェクト](https://pickles2.com/) をセットアップ

### 1. `composer.json` に、パッケージ情報を追加

```
$ composer require pickles2/px2-paprika
```


### 2. `.htaccess` を開き、 `RewriteCond` の条件に 拡張子 `.php` を追加

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

### 3. `px-files/config.php` を開き、プラグインを設定

#### `paths_proc_type` を設定

`*.php` を追加する。

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

		'*.php' => 'php', // <- for Paprika Framework

		// ...中略...
	);
```

#### `paths_enable_sitemap` を設定

`*.php` を追加する。

```php
	// 拡張子 `*.php` で、サイトマップを有効化
	$conf->paths_enable_sitemap = array(
		// ...中略...

		'*.php', // <- for Paprika Framework

		// ...中略...
	);
```

#### `funcs->before_content` を設定

```php
	/**
	 * funcs: Before content
	 */
	$conf->funcs->before_content = array(

		// Paprika - PHPアプリケーションフレームワーク
		// before_content の先頭に設定してください。
		'picklesFramework2\paprikaFramework\main::before_content('.json_encode( array(
			// アプリケーションが動的に生成したコンテンツエリアの名称
			'bowls'=>array('custom_area_1', 'custom_area_2', ),

			// Paprika を適用する拡張子の一覧
			'exts' => array('php'),
		) ).')' ,

		// ...中略...
	);
```

#### `funcs->processor->php` 設定を追加

```php
	/**
	 * processor
	 */
	$conf->funcs->processor->php = array(
		// Paprika - PHPアプリケーションフレームワーク
		'picklesFramework2\paprikaFramework\main::processor' ,

		// html のデフォルトの処理を追加
		$conf->funcs->processor->html ,
	);
```


## Paprika を `.html` 拡張子のページにも適用するには

1. `exts` オプションに `html` を追加します。
2. `$conf->funcs->processor->html` の先頭にも `picklesFramework2\paprikaFramework\main::processor` を追加します。
3. パブリッシュ先のディレクトリに、 `.html` 拡張子でも PHPが実行されるよう設定します。 `.htaccess` で 設定する場合、 `AddHandler application/x-httpd-php .php .html` のように書きます。


## PXコマンド - PX Commands

- `paprika.${xxxxx}` - カスタムコマンドを実行する。
- `paprika._.publish_template` - アプリケーションのためのテンプレートファイルを生成する。(フレームワークの内部で暗黙的にコールされます)


## 変更履歴 - Change Log

### pickles2/px2-paprika v0.5.0 (リリース日未定)

- 同梱のプラグインが、より直接的な表現で設定できるようになった。
- (破壊的変更) `paprika-files/` のパスを `px-files/paprika/` に変更した。
- (破壊的変更) `config_local.php` を廃止した。
- (破壊的変更) コンテンツのカレントディレクトリは、コンテンツルートに揃えられるようになった。
- `PX=paprika` を追加した。これに伴い、`PX=paprika.publish_template` を `PX=paprika._.publish_template` に変更し、 `PX=paprika._` 以下を予約語とした。
- 2重拡張子でPHPをパブリッシュできるようになった。

### pickles2/px2-paprika v0.4.0 (2023年4月22日)

- サポートするPHPのバージョンを `>=7.3.0` に変更。
- 内部コードの細かい修正。

### pickles2/px2-paprika v0.3.1 (2020年1月18日)

- `$paprika->redirect()` を追加。
- `$paprika->download()` を追加。

### pickles2/px2-paprika v0.3.0 (2019年11月28日)

- PXコマンド `PX=paprika.init` を廃止。
- `$conf->prepend` を追加。
- `$paprika->log()` を追加。
- `$conf->realpath_log_dir` を追加。
- `$conf->log_reporting` を追加。
- `$paprika->pdo()` を追加。
- `$conf->db` を追加。
- `$paprika->env()->href` を追加。
- `$paprika->env()->page_info` を追加。
- `$paprika->env()->breadcrumb` を追加。
- `$paprika->env()->parent` を追加。
- `$paprika->env()->bros` を追加。
- `$paprika->env()->children` を追加。

### pickles2/px2-paprika v0.2.0 (2019年11月21日)

- `paprika_prepend.php` の仕組みを廃止。
- Paprika Framework の config ファイルの仕組みを追加。
- Paprika環境変数から、 `realpath_controot_preview`、`realpath_files_private_cache` を削除。
- Paprika環境変数 `realpath_homedir` は、Pickles 2 のホームディレクトリではなく、 Paprika のホームディレクトリを返すようになった。
- 空間名を `tomk79\pickles2\paprikaFramework2` から `picklesFramework2\paprikaFramework` に変更。

### pickles2/px2-paprika v0.1.1 (2019年11月17日)

- Windows + PHP7 の環境で、CSV ファイルを正しく読み込めない問題に対応した。

### pickles2/px2-paprika v0.1.0 (2018年9月25日)

- Pickles 2 グループへ移管した。

### tomk79/px2-paprika v0.0.1 (2018年9月19日)

- Initial Release.


## 開発者向け情報 - for Developer


### テスト - Test

```
$ cd {$documentRoot}
$ php vendor/phpunit/phpunit/phpunit;
```


### ドキュメント出力 - phpDocumentor

```
$ wget https://phpdoc.org/phpDocumentor.phar;
$ composer run-script documentation;
```


## ライセンス - License

MIT License


## 作者 - Author

- (C)Tomoya Koyanagi <tomk79@gmail.com>
- website: <https://www.pxt.jp/>
- Twitter: @tomk79 <https://twitter.com/tomk79/>
