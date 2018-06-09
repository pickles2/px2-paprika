<?php
if( !isset($paprika) ){
	echo '{$main_contents}'."\n";
	return;
}

// ルーティング処理
$content = $paprika->route(array(
	array(
		"/",
		function(){
			ob_start(); ?>
				<p>はじめの画面です。</p>
				<ul>
					<li><a href="<?= htmlspecialchars( $_SERVER['SCRIPT_NAME'] ) ?>/1">/1 (GET)</a></li>
					<li><form action="<?= htmlspecialchars( $_SERVER['SCRIPT_NAME'] ) ?>/1" method="post"><button type="submit">/1 (POST)</button></form></li>
				</ul>
				<h2>ダイナミックパス</h2>
				<p>Paprika では、 Pickles 2 と似たダイナミックパス機能を利用できます。</p>
				<ul>
					<li><a href="<?= htmlspecialchars( $_SERVER['SCRIPT_NAME'] ) ?>/dynamic_path1/hoge789/fuga123">/dynamic_path1/hoge789/fuga123</a></li>
					<li><a href="<?= htmlspecialchars( $_SERVER['SCRIPT_NAME'] ) ?>/dynamic_path2/f/o/o/b/a/r/bar/hogefuga123">/dynamic_path2/f/o/o/b/a/r/bar/hogefuga123</a></li>
				</ul>
			<?php
			return ob_get_clean();
		}
	),
	array(
		array("POST", "/1"),
		function(){
			return "<p>画面 1 (POST) です。</p>";
		}
	),
	array(
		"/1",
		function(){
			return "<p>画面 1 (GET)です。</p>";
		}
	),
	array(
		'/dynamic_path1/{$hoge}/fuga{$fuga}',
		function($path_param){
			ob_start(); ?>
			<p>ダイナミックパス1</p>
			<p><code>/dynamic_path1/{$hoge}/fuga{$fuga}</code></p>
			<pre><code><?php var_dump($path_param); ?></code></pre>
			<?php
			return ob_get_clean();
		}
	),
	array(
		'/dynamic_path2/{*foo}/bar{*}',
		function($path_param){
			ob_start(); ?>
			<p>ダイナミックパス2</p>
			<p><code>/dynamic_path2/{*foo}/bar{*}</code></p>
			<pre><code><?php var_dump($path_param); ?></code></pre>
			<?php
			return ob_get_clean();
		}
	),
));


$tpl = $paprika->bind_template(
	array('{$main_contents}'=>$content)
);
echo $tpl;
exit();
