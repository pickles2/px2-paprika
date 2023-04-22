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

$paprika = new \picklesFramework2\paprikaFramework\fw\paprika(json_decode('{"file_default_permission":"775","dir_default_permission":"775","filesystem_encoding":"UTF-8","session_name":"PXSID","session_expire":1800,"directory_index":["index.html"],"realpath_controot":"../../","realpath_homedir":"../","path_controot":"/","realpath_files":"./migrate_files/","realpath_files_cache":"../../caches/c/paprika-files/bin/migrate_files/","href":null,"page_info":null,"parent":null,"breadcrumb":null,"bros":null,"children":null}'), false);

// コンテンツが標準出力する場合があるので、それを拾う準備
ob_start();

// コンテンツを実行する
// クロージャーの中で実行
$execute_php_content = function()use($paprika){
?>
<?php
echo '---------------- migrate'."\n";

$pdo = $paprika->pdo();

var_dump($pdo->getAttribute(PDO::ATTR_DRIVER_NAME));
// var_dump($pdo->getAttribute(PDO::ATTR_SERVER_INFO));
// var_dump($pdo->getAttribute(PDO::ATTR_SERVER_VERSION));

$result = $pdo->query('CREATE TABLE IF NOT EXISTS test_table (
	id INTEGER PRIMARY KEY AUTOINCREMENT,
	name VARCHAR
);');
// var_dump($result);



$stmt = $pdo->prepare('INSERT INTO test_table (name) VALUES (:name);');
// var_dump($stmt);
$name = 'Test Name';
$stmt->bindParam(':name', $name, \PDO::PARAM_STR);
$stmt->execute();



// $stmt = $pdo->prepare('SELECT name from test_table WHERE id = 1;');
$stmt = $pdo->query('SELECT name from test_table WHERE id = 1;');
$result = $stmt->fetch();
var_dump($result);

exit;
?><?php
};
$execute_php_content();
$content = ob_get_clean();
if(strlen($content)){
    $paprika->bowl()->put($content);
}
echo $paprika->bowl()->bind_template();
exit;
?>
