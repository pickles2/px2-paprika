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
));


$tpl = $paprika->bind_template(
	array('{$main_contents}'=>$content)
);
echo $tpl;
exit();
