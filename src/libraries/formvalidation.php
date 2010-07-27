<?php

/*
Form Validation Library

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

class lib_formvalidation {
	public $templates;
	
	function exec($data) {
		$data;
		return false;
	}
	
	function validateValue($field, $value, $template, $id='') {
		$errors = array();
		$length = strlen($value);
		
		if(!empty($id))
			$id .= '_';
		
		foreach($template->rules as $rule=>$rval) {
			switch($rule) {
				case 'min_length':
					$min = intval($rval);
					if($min > $length)
						$errors[] = $rule;
					break;
				case 'max_length':
					$max = intval($rval);
					if($max < $length)
						$errors[] = $rule;
					break;
				case 'symbols':
					$symbols = '';
					foreach($rval as $sym) {
						switch($sym) {
							case 'alpha':
								if(!AVOID_NEW_REGEX)
									$symbols .= '\p{L}';
								else
									$symbols .= 'a-zA-Z';
								break;
							case 'numeric':
								if(!AVOID_NEW_REGEX)
									$symbols .= '\d';
								else
									$symbols .= '0-9';
								break;
							case 'whitespace':
								$symbols .= '\s';
								break;
							default:
								$symbols .= $sym;
						}
					}
					$symbols = "/^[$symbols]*$/";
					$matches = preg_match($symbols, $value);
					if($matches != 1)
						$errors[] = $rule;
					break;
				case 'format':
					switch($rval) {
						case 'date':
							if(($timestamp = strtotime($str)) === false)
								$errors[] = $rule;
							break;
						case 'email':
							$pattern = '/^(?:[a-z0-9!#$%&\'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+/=?^_`{|}~-]+)*|"(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])$/';
							if(preg_match($pattern, $value) == 0)
								$errors[] = $rule;
							break;
						case 'phone':
							$pattern = '/^(\d-?)?(\d{3}-?)?\d{3}-?\d{4}$/';
							if(preg_match($pattern, $value) == 0)
								$errors[] = $rule;
							break;
						case 'internation_phone':
							$pattern = '/^\+?\d{0,2}(\s|-)?\d{4}(\s|-)?\d{3}(\s|-)?\d{3}$/';
							if(preg_match($pattern, $value) == 0)
								$errors[] = $rule;
							break;
						case 'zip':
							$pattern = '/^\d{5}([- /]?\d{4})?$/';
							if(preg_match($pattern, $value) == 0)
								$errors[] = $rule;
							break;
					}
					break;
				case 'regex':
					$pattern = $rval;
					if(preg_match($pattern, $value) == 0)
						$errors[] = $rule;
					break;
				case 'enum':
					$passes = false;
					foreach($rval as $enum) {
						if($enum == $value) {
							$passes = true;
							break;
						}
					}
					if(!$passes) 
						$errors[] = $rule;
					break;
				case 'matches':
					$cval = $_REQUEST[$id.$rval];
					if($cval != $value)
						$errors[] = $rule;
					break;
			}
			
		}
		
		return $errors;
	}
	
}