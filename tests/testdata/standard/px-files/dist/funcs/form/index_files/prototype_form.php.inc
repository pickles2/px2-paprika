<?php
class form{

	/** Paprika Object */
	private $paprika;

	/**
	 * コンストラクタ
	 */
	public function __construct($paprika){
		$this->paprika = $paprika;
	}

	/**
	 * フォームを生成する
	 */
	public function form($form_structure, $preset, $action, $options = array()){
		$max_input_page_count = count($form_structure);
		$progress = $this->paprika->req()->get_param('P');
		$user_input_values = array();
		$user_input_errors = array();
		if(!strlen($progress)){
			// 初回
			$progress = 'i1';
			foreach($form_structure as $idx=>$form){
				foreach($form as $name=>$form_element){
					$user_input_values[$name] = @$preset[$name];
				}
			}
		}else{
			// 進捗中
			foreach($form_structure as $idx=>$form){
				foreach($form as $name=>$form_element){
					$user_input_values[$name] = $this->paprika->req()->get_param($name);
				}
			}

			// Validator で入力エラーチェック
			$user_input_errors = $this->validate($form_structure, $user_input_values);
		}


		if( $progress == 't' ){
			// --------------------------------------
			// 完了画面
			return $this->bind_template('thanks', array());
		}
		if( $progress == 'c' && !count($user_input_errors) ){
			// --------------------------------------
			// 確認画面
			$html_hidden = '';
			$html_form_contents = array();
			foreach($form_structure as $idx=>$form){
				$tmp_html_form_contents = '';
				foreach($form as $name=>$form_element){
					$html = $this->bind_form_element($form_element['type'], 'confirm', array(
						'form' => $form_element,
						'name' => $name,
						'value' => $user_input_values[$name],
						'error' => @$user_input_errors[$name]['message'],
					));
					$html = $this->bind_template('form_item', array(
						'form' => $form_element,
						'element' => $html,
					));
					$tmp_html_form_contents .= $html;
					$html_hidden .= '<input type="hidden" name="'.htmlspecialchars($name).'" value="'.htmlspecialchars($user_input_values[$name]).'" />';
				}
				array_push($html_form_contents, $tmp_html_form_contents);
			}
			return $this->bind_template('confirm', array(
				'form' => $html_form_contents,
				'hidden' => $html_hidden
			));
		}
		if( $progress == 'e' && !count($user_input_errors) ){
			// --------------------------------------
			// 実行
			return $this->execute($action);
		}

		if( ($progress == 'c' || $progress == 'e') && count($user_input_errors) ){
			// confirm または execute の要求に対して、
			// Validator がエラーを検出した場合、
			// エラーが検出された最初の入力画面に戻す。
			$min_input_page_index = count($form_structure);
			foreach( $user_input_errors as $error ){
				if( $min_input_page_index > $error['input_page_index'] ){
					$min_input_page_index = $error['input_page_index'];
				}
			}
			$progress = 'i'.($min_input_page_index+1);
		}

		if( preg_match('/^i([1-9][0-9]*)$/', $progress, $matched) ){
			// --------------------------------------
			// 入力画面 (複数ステップに分かれている場合があります)
			$input_page_index = intval($matched[1]);

			$html_hidden = '';
			$html_form_contents = '';
			foreach($form_structure as $idx=>$form){
				if($idx == $input_page_index-1){
					// 現在のページの項目
					foreach($form as $name=>$form_element){
						$html = $this->bind_form_element($form_element['type'], 'input', array(
							'form' => $form_element,
							'name' => $name,
							'value' => @$user_input_values[$name],
							'error' => @$user_input_errors[$name]['message'],
						));
						$html = $this->bind_template('form_item', array(
							'form' => $form_element,
							'element' => $html,
						));
						$html_form_contents .= $html;
					}
				}else{
					// 他の入力ページの項目
					foreach($form as $name=>$form_element){
						$html_hidden .= '<input type="hidden" name="'.htmlspecialchars($name).'" value="'.htmlspecialchars($preset[$name]).'" />';
					}
				}
			}

			return $this->bind_template('input', array(
				'form' => $html_form_contents,
				'next' => ($input_page_index==$max_input_page_count ? 'c' : $input_page_index+1),
			)).$html_hidden;
		}

		@header('HTTP/1.1 404 Not Found');
		$rtn = '404 Not Found';
		return $rtn;
	}


	/**
	 * フォーム処理を実行する
	 */
	private function execute($action){
		if( is_callable($action) ){
			$result = $action( array() );
		}
		@header("Location: ?P=t");
		exit;
	}


	/**
	 * 入力値をチェックする
	 */
	private function validate($form_structure, $user_input_values){
		$rtn = array();
		foreach($form_structure as $idx=>$form){
			foreach($form as $name=>$form_element){
				$validator_realpath = __DIR__.'/form_elements/'.urlencode($form_element['type']).'/validate.php.inc';
				if(is_file( $validator_realpath )){
					$validator = include( $validator_realpath );
					$result = $validator($this->paprika, $form_element, $user_input_values[$name]);
					if( $result !== true ){
						$rtn[$name] = array(
							'input_page_index' => $idx,
							'message' => $result,
						);
					}
				}
			}
		}
		return $rtn;
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

	/**
	 * フォーム要素をテンプレート処理する
	 */
	private function bind_form_element( $type, $progress, $data ){
		$loader = new \Twig_Loader_Filesystem(__DIR__.'/form_elements/');
		$twig = new \Twig_Environment($loader, array('debug'=>true));
		$twig->addExtension(new \Twig_Extension_Debug());
		$fin = $twig->render(urlencode($type).'/'.urlencode($progress).'.twig', $data);
		return $fin;
	}

}
