<?php
/** Validator */
return function($paprika, $form_element, $user_input_value){
	if( strlen(@$form_element['required']) ){
		if( !strlen($user_input_value) ){
			return '必須項目です。';
		}
	}
	if( strlen(@$form_element['max']) ){
		if( mb_strlen($user_input_value) > intval($form_element['max']) ){
			return $form_element['max'].'文字以内で入力してください。';
		}
	}
	if( strlen(@$form_element['min']) ){
		if( mb_strlen($user_input_value) < intval($form_element['min']) ){
			return $form_element['min'].'文字以上で入力してください。';
		}
	}
	return true;
};
