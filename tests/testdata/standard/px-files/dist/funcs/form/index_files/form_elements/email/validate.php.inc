<?php
/** Validator */
return function($paprika, $form_element, $user_input_value){
	if( strlen(@$form_element['required']) ){
		if( !strlen($user_input_value) ){
			return '必須項目です。';
		}
	}
	if( strlen(@$form_element['max']) ){
		if( strlen($user_input_value) > intval($form_element['max']) ){
			return $form_element['max'].'バイト以内で入力してください。';
		}
	}
	if( strlen(@$form_element['min']) ){
		if( strlen($user_input_value) < intval($form_element['min']) ){
			return $form_element['min'].'バイト以上で入力してください。';
		}
	}
	if( strlen(@$user_input_value) ){
		preg_match_all('/(\@)/', $user_input_value, $matched);
		if( !count($matched[1]) ){
			return 'アットマークが含まれていません。';
		}
		if( count($matched[1]) > 1 ){
			return 'アットマークが複数含まれています。';
		}
		if( preg_match('/^\@/s', $user_input_value) ){
			return 'アットマークの前が空白です。';
		}
		if( preg_match('/\@$/s', $user_input_value) ){
			return 'アットマークの後が空白です。';
		}
		if( !preg_match('/^\S+\@\S+$/s', $user_input_value) ){
			return '空白が含まれています。';
		}
	}
	return true;
};
