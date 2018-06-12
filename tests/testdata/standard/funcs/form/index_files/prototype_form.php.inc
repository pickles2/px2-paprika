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
		$user_input_value = array();
		if(!strlen($progress)){
			// 初回
			$progress = 'i1';
			foreach($form_structure as $idx=>$form){
				foreach($form as $name=>$form_element){
					$user_input_value[$name] = @$preset[$name];
				}
			}
		}else{
			// 進捗中
			foreach($form_structure as $idx=>$form){
				foreach($form as $name=>$form_element){
					$user_input_value[$name] = $this->paprika->req()->get_param($name);
				}
			}
		}

		if( $progress == 't' ){
			// --------------------------------------
			// 完了画面
			return $this->bind_template('thanks', array());
		}
		if( $progress == 'c' ){
			// --------------------------------------
			// 確認画面
			$html_hidden = '';
			$html_form_contents = array();
			foreach($form_structure as $idx=>$form){
				$tmp_html_form_contents = '';
				foreach($form as $name=>$form_element){
					$html = $this->bind_template('form_elements/'.$form_element['type'].'/confirm', array(
						'form' => $form_element,
						'name' => $name,
						'value' => $user_input_value[$name],
					));
					$html = $this->bind_template('form_item', array(
						'form' => $form_element,
						'element' => $html,
					));
					$tmp_html_form_contents .= $html;
					$html_hidden .= '<input type="hidden" name="'.htmlspecialchars($name).'" value="'.htmlspecialchars($user_input_value[$name]).'" />';
				}
				array_push($html_form_contents, $tmp_html_form_contents);
			}
			return $this->bind_template('confirm', array(
				'form' => $html_form_contents,
				'hidden' => $html_hidden
			));
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
						$html = $this->bind_template('form_elements/'.$form_element['type'].'/input', array(
							'form' => $form_element,
							'name' => $name,
							'value' => @$user_input_value[$name],
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
		if( $progress == 'e' ){
			// --------------------------------------
			// 実行
			return $this->execute($action);
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
