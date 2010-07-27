<?php

/*
Form Binding Library

Copyright 2010 Serverboy Software; Matt Basta

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

   http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.

*/

class lib_formbinding {
	function exec($data) {
		$data;
		return false;
	}
	
	function prepForm($token, $id='') {
		
		if(!empty($id))
			$id .= '_';
		
		$timestamp = time();
		$elements = $this->prepGeneral($timestamp, $id);
		
		$serial = $this->encodeToken($timestamp, $token);
		
		$elements[] = getLib('form')->hidden($id.'token', $serial);
		
		return implode('', $elements);
	}
	function prepFormMulti($tokens, $labelColumn, $id='', $label='Choose a record') {
		
		if(!empty($id))
			$id .= '_';
		
		$timestamp = time();
		$elements = $this->prepGeneral($timestamp, $id);
		
		$options = array();
		foreach($tokens as $token) {
			$serial = $this->encodeToken($timestamp, $token);
			$options[$serial] = $token->getValue($labelColumn);
		}
		
		$elements[] = getLib('form')->render_options($options, $label, $id.'token');
		
		return implode('', $elements);
	}
	function prepFormIndefinite($table, $id='') {
		
		if(!empty($id))
			$id .= '_';
		
		$timestamp = time();
		$elements = $this->prepGeneral($timestamp, $id);
		
		$elements[] = getLib('form')->hidden($id.'token', $this->encodeToken($timestamp, null, $table));
		
		return implode('', $elements);
		
	}
	private function encodeToken($timestamp, $token='', $table='') {
		global $uid;
		if(!empty($token))
			$serial = $token->serialize();
		else
			$serial = $table.':INDEFINITE';
		$serial = getLib('encrypt_xor')->encrypt($serial, sha1($uid . $timestamp));
		return $serial;
	}
	private function prepGeneral($timestamp, $id='') {
		global $uid;
		$elements = array(
			getLib('form')->hidden($id.'token_key', sha1($uid . $timestamp . 'binding')),
			getLib('form')->hidden($id.'timestamp', $timestamp)
		);
		return $elements;
	}
	function getValidated($template, $id='') {
		$validation = getLib('formvalidation');
		$set = array();
		$errors = array();
		if(!empty($id))
			$id .= '_';
		foreach($template as $key=>$value) {
			if(isset($_REQUEST[$id.$key]) || $value->type == 'checkbox') {
				if(!isset($_REQUEST[$id.$key])) $_REQUEST[$id.$key] = '';
				$result = $validation->validateValue($key, $_REQUEST[$id.$key], $value);
				if(count($result) == 0) {
					if($value->name == '')
						continue;
						
					if($value->type == 'checkbox')
						$set[$key] = !empty($_REQUEST[$id.$key]);
					else {
						$set[$key] = $value->runValue($_REQUEST[$id.$key]);
					}
				} else
					$errors[$key] = $result;
			}
		}
		return array(
			'errors' => $errors,
			'values' => $set,
			'success' => count($errors) == 0
		);
	}
	function bindback($token, $template, $id='') {
		
		$result = $this->getValidated($template, $id);
		$errors = $result['errors'];
		$set = $result['values'];
		
		if(count($errors) > 0)
			return $errors;
		else
			$token->setValues($set);
		return true;
	}
	
}