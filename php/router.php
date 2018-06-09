<?php
/**
 * Pickles 2 - Paprika Framework
 */
namespace tomk79\pickles2\paprikaFramework2;

/**
 * router.php
 */
class router{

	/** Paprika object */
	private $paprika;

	/**
	 * constructor
	 * @param object $paprika Paprika Object
	 */
	public function __construct( $paprika ){
		$this->paprika = $paprika;
	}


	/**
	 * ルーティングする
	 */
	public function route($routes){
		foreach( $routes as $route ){
			if( !$this->check_cond($route[0]) ){
				continue;
			}
			return $this->execute_ctrl( $route[1] );
		}
		@header("HTTP/1.1 404 Not Found");
		return "404 Not Found";
	}

	/**
	 * 条件を評価する
	 */
	private function check_cond($cond){
		$conds = $cond;
		$method = null;
		$path = null;
		if( is_string($cond) ){
			$conds = array($cond);
		}
		foreach( $conds as $conds_row ){
			if( is_string($conds_row) ){
				if( preg_match('/^\//', $conds_row) ){
					$path = $conds_row;
				}else{
					switch( strtolower($conds_row) ){
						case "get":
						case "post":
						case "put":
						case "delete":
						case "head":
						case "options":
						case "trace":
							$method = strtolower($conds_row);
							break;
					}
				}
			}
		}
		if( strlen($method) && strtolower($_SERVER['REQUEST_METHOD']) != $method ){
			return false;
		}

		$PATH_INFO = @$_SERVER['PATH_INFO'];
		if( !strlen($PATH_INFO) ){
			$PATH_INFO = '/';
		}
		if( strlen($path) ){
			$path_ok = false;
			if( $path == $PATH_INFO ){
				$path_ok = true;
			}
			if(!$path_ok){
				return false;
			}
		}
		return true;
	}

	/**
	 * コントローラーを実行する
	 */
	private function execute_ctrl($ctrl){
		if( is_callable($ctrl) ){
			return $ctrl();
		}
		return false;
	}
}
