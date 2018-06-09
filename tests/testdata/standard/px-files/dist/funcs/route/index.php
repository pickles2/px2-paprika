<?php
// chdir
chdir(__DIR__);

// autoload.php をロード
$tmp_path_autoload = __DIR__;
while(1){
    if( is_file( $tmp_path_autoload.'/vendor/autoload.php' ) ){
        require_once( $tmp_path_autoload.'/vendor/autoload.php' );
        break;
    }

    if( $tmp_path_autoload == dirname($tmp_path_autoload) ){
        // これ以上、上の階層がない。
        break;
    }
    $tmp_path_autoload = dirname($tmp_path_autoload);
    continue;
}
unset($tmp_path_autoload);

$paprika = new \tomk79\pickles2\paprikaFramework2\paprika(json_decode('{"file_default_permission":"775","dir_default_permission":"775","filesystem_encoding":"UTF-8","session_name":"PXSID","session_expire":1800,"directory_index":["index.html"],"realpath_controot":"../../","realpath_controot_preview":"../../../../","realpath_homedir":"../../../","path_controot":"/","realpath_files":"./index_files/"}'), false);

?>
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
