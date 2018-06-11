<?php
class form{
	private $paprika;
	public function __construct($paprika){
		$this->paprika = $paprika;
	}

	/**
	 * フォームを生成する
	 */
	public function form($form_structure, $preset, $action, $options = array()){
		$max_input_page_count = count($form_structure);
		$progress = $this->paprika->req()->get_param('P');
		if(!strlen($progress)){
			$progress = 'i1';
		}

		if( $progress == 't' ){
			// 完了画面
			return $this->bind_template('thanks', array());
		}
		if( $progress == 'c' ){
			// 確認画面
			return $this->bind_template('confirm', array());
		}
		if( preg_match('/^i([1-9][0-9]*)$/', $progress, $matched) ){
			// 入力画面 (複数ステップに分かれている場合があります)
			$input_page_index = intval($matched[1]);
			return $this->bind_template('input', array(
				'next' => ($input_page_index==$max_input_page_count ? 'c' : $input_page_index+1),
			));
		}
		if( $progress == 'e' ){
			// 実行
			return $this->execute();
		}

		$rtn = 'TODO: Unimplemented';
		return $rtn;
	}


	private function execute(){
		@header("Location: ?P=t");
		exit;
	}


	/**
	 * Twigテンプレートを処理する
	 */
	private function bind_template( $template, $data ){
		$loader = new \Twig_Loader_Filesystem(__DIR__.'/twig/');
		$twig = new \Twig_Environment($loader, array('debug'=>true));
		$twig->addExtension(new \Twig_Extension_Debug());
		$fin = $twig->render($template.'.twig', $data);
		return $fin;
	}

}
