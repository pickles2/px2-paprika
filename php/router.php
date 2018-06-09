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

	/** Path Params */
	private $path_param = false;

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
			$this->path_param = $this->is_match_dynamic_path($path, $PATH_INFO);

			if( $this->path_param ){
				$path_ok = true;
			}elseif( $path == $PATH_INFO ){
				$path_ok = true;
			}
			if(!$path_ok){
				return false;
			}
		}
		return true;
	}

	/**
	 * ダイナミックパスにマッチするか調べる
	 */
	private function is_match_dynamic_path( $dynamic_path, $current_page_path ){
		$tmp_preg_pattern = $dynamic_path;
		$preg_pattern = '';
		while(1){
			if( !preg_match('/^(.*?)\{(\$|\*)([a-zA-Z0-9\-\_]*)\}(.*)$/s', $tmp_preg_pattern, $tmp_matched) ){
				$preg_pattern .= preg_quote($tmp_preg_pattern,'/');
				break;
			}
			$preg_pattern .= preg_quote($tmp_matched[1],'/');
			switch( $tmp_matched[2] ){
				case '$':
					$preg_pattern .= '([a-zA-Z0-9\-\_]+)';break;
				case '*':
					$preg_pattern .= '(.*?)';break;
			}
			$tmp_preg_pattern = $tmp_matched[4];
			continue;
		}
		if($tmp_preg_pattern == $dynamic_path){
			return false;
		}
		preg_match_all('/\{(\$|\*)([a-zA-Z0-9\-\_]*)\}/', $dynamic_path, $pattern_map);
		$tmp_path_original = $dynamic_path;
		$dynamic_path = preg_replace('/'.preg_quote('{','/').'(\$|\*)([a-zA-Z0-9\-\_]*)'.preg_quote('}','/').'/s','$2',$dynamic_path);
		$dynamic_path_info = array(
			'path'=>$dynamic_path,
			'path_original'=>$tmp_path_original,
			'preg'=>'/^'.$preg_pattern.'$/s',
			'pattern_map'=>$pattern_map[2],
		);

		if( !preg_match( $dynamic_path_info['preg'] , $current_page_path, $matched ) ){
			return false;
		}
		$rtn = array();
		foreach($dynamic_path_info['pattern_map'] as $param_idx=>$param_name){
			$rtn[$param_name] = $matched[$param_idx+1];
		}
		return $rtn;

	}

	/**
	 * コントローラーを実行する
	 */
	private function execute_ctrl($ctrl){
		if( is_callable($ctrl) ){
			return $ctrl( $this->path_param );
		}
		return false;
	}
}
