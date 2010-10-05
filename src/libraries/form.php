<?php

/*
HTML Form Rendering Library

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

class lib_form {
	public $validator;
	private $templates;
	
	function exec($data) {
		$data;
		return false;
	}
	function render($type, $label, $name, $default='', $checked=false) {
		$elements = array();
		if(!empty($label) && $type != 'checkbox')
			$label = array(
				'name'=>'label',
				'attributes'=>array(
					'for'=>$name,
					'id'=>$name.'_context'
				),
				'value'=>$label
			);
		switch($type) {
			case 'textarea':
				$elements[] = $label;
				$elements[] = array(
					'name'=>'textarea',
					'attributes'=>array(
						'name'=>$name
					),
					'collapse'=>false,
					'value'=>$default
				);
				break;
			case 'checkbox':
				if($default=='')
					$default = 'true';
				$checkbox = array(
					'name'=>'input',
					'attributes'=>array(
						'name'=>$name,
						'type'=>$type,
						'class'=>'checkbox',
						'value'=>$default
					)
				);
				
				if($checked)
					$checkbox['attributes']['checked'] = 'checked';
				
				$label = array(
					'name'=>'label',
					'attributes'=>array(
						'class'=>'singular',
						'id'=>$name.'_context'
					),
					'value'=>array(
						$checkbox,
						' ',
						array(
							'name'=>'span',
							'value'=>$label
						)
					)
				);
				$elements[] = $label;
				
				break;
			default:
				$elements[] = $label;
				$elements[] = array(
					'name'=>'input',
					'attributes'=>array(
						'name'=>$name,
						'type'=>$type,
						'value'=>$default
					)
				);
		}
		return getLib('html')->renderHTMLElements($elements);
	}
	function render_options($options, $label, $name, $default='', $force='') {
		if(empty($force))
			$force = count($options)>2?'select':'radio';
		
		$output = '';
		
		$elements = array();
		
		if($force == 'select') {
			
			$elements[] = array(
				'name'=>'label',
				'attributes'=>array(
					'for'=>$name
				),
				'value'=>$label
			);
			
			$hoptions = array();
			foreach($options as $option=>$label) {
				$hoption = array(
					'name'=>'option',
					'attributes'=>array(
						'value'=>$option
					),
					'value'=>$label
				);
				
				if($default == $option)
					$hoption['attributes']['selected'] = 'selected';
					
				$hoptions[] = $hoption;
			}
			
			$select = array(
				'name'=>'select',
				'attributes'=>array(
					'name'=>$name
				),
				'value'=>$hoptions
			);
			
			$elements[] = $select;
			
		} elseif($force == 'radio') {
			
			$radiogroup = array(
				'name'=>'div',
				'attributes'=>array(
					'class'=>'radiogroup'
				),
				'value'=>array(
					array(
						'name'=>'label',
						'attributes'=>array(
							'for'=>$name
						),
						'value'=>$label
					)
				)
			);
			
			$c = 0;
			foreach($options as $option=>$label) {
				$c++;
				
				if(is_array($label)) {
					$option_label = $label['title'];
					$option_id = $label['id'];
				} else {
					$option_label = $label;
					$option_id = $name.'_'.$c;
				}
				
				$hoption = array(
					'name'=>'input',
					'attributes'=>array(
						'name'=>$name,
						'id'=>$option_id,
						'type'=>'radio',
						'class'=>'radio',
						'value'=>$option
					)
				);
				
				if($default == $option)
					$hoption['attributes']['checked'] = 'checked';
				
				$hlabel = array(
					'name'=>'label',
					'value'=>array(
						$hoption,
						' ',
						array(
							'name'=>'span',
							'value'=>$option_label
						)
					)
				);
					
				$radiogroup['value'][] = $hlabel;
				
			}
			
			$elements[] = $radiogroup;
			
		}
		return getLib('html')->renderHTMLElements($elements);
	}
	function render_checkgroup($checks) {
		$elements = array(
			'name'=>'div',
			'attributes'=>array(
				'class'=>'checkgroup'
			),
			'value'=>array()
		);
		foreach($checks as $check)
			$elements['value'][] = $this->render('checkbox', $check['label'], $check['name'], $check['value'], $check['checked']);
		
		return getLib('html')->renderHTMLElements($elements);
	}
	function hidden($name, $value) {
		$element = array(
			'name'=>'input',
			'attributes'=>array(
				'name'=>$name,
				'id'=>$name,
				'class'=>'hidden',
				'type'=>'hidden',
				'value'=>$value
			)
		);
		return getLib('html')->renderHTML($element);
	}
	function submit($label) {
		$elements = array(
			array(
				'name'=>'input',
				'attributes'=>array(
					'type'=>'submit',
					'value'=>$label,
					'class'=>'submit'
				)
			)
		);
		return getLib('html')->renderHTMLElements($elements);
	}
	
	function render_file_upload($label, $name, $explanation='', $caption='') {
		$elements = array(
			array(
				'name'=>'p',
				'attributes'=>array(
					'class'=>'upload'
				),
				'value'=>array(
					array(
						'name'=>'label',
						'attributes'=>array(
							'for'=>$name
						),
						'value'=>$label
					),
					array(
						'name'=>'input',
						'attributes'=>array(
							'class'=>'fileupload',
							'name'=>$name,
							'type'=>'file'
						)
					)
				)
			)
		);
		if(!empty($explanation)) {
			$elements[0]['value'][] = array(
				'name'=>'small',
				'value'=>$explanation
			);
		}
		if(!empty($caption)) {
			$elements[0]['value'][] = array(
				'name'=>'span',
				'value'=>$caption
			);
		}
		$elements[0]['value'][] = array(
			'name'=>'div',
			'attributes'=>array(
				'class'=>'clear'
			),
			'value'=>'&nbsp;'
		);
		return getLib('html')->renderHTMLElements($elements);
	}
	
	function build($form, $id='', $repopulate=false) {
		$template = $this->loadTemplate($form);
		$output = '';
		if(!empty($id))
			$id .= '_';
		foreach($template as $name=>$field) {
			$type = $field->type;
			$force = '';
			switch($type) {
				case 'text':
				case 'password':
				case 'textarea':
				case 'checkbox':
				default:
					$value = $field->default;
					if($repopulate && !empty($_REQUEST[$id.$name]))
						$value = $_REQUEST[$id.$name];
					$output .= $this->render($type, $field->label, $id.$name, $value);
					break;
					/*
				case 'radio':
					$force = 'radio';
				case 'select':
					if(empty($force))
						$force = 'select';
				case 'choices':
					$output .= $this->render($type, $field->label, $field->name, $field->default, $force);
					*/
			}
		}
		return $output;
	}
	function loadTemplate($template) {
		if(isset($this->templates[$template]))
			return $this->templates[$template];
			
		$data = file_get_contents($template);
		$xml = new DOMDocument();
		$xml->loadxml($data);
		
		
		$fields = $xml->getElementsByTagName('field');
		
		$output = array();
		
		foreach($fields as $field) {
			$name = $field->attributes->getNamedItem('name')->nodeValue;
			$type = $field->attributes->getNamedItem('type')->nodeValue;
			$checked = '';
			$default = '';
			$label = '';
			$explanation = '';
			$error = '';
			$transform = '';
			$rules = array();
			
			$children = $field->childNodes;
			foreach($children as $child) {
				$nname = $child->nodeName;
				switch($nname) {
					case 'checked':
					case 'default':
					case 'label':
					case 'error':
					case 'explanation':
					case 'transform':
						$$nname = $child->nodeValue;
						break;
					case 'rule':
						$rule_param = $child->nodeValue;
						$rule_type = $child->attributes->getNamedItem('type')->nodeValue;
						switch($rule_type) {
							 case 'enum':
							 case 'symbols':
								if(!isset($rules[$rule_type]))
									$rules[$rule_type] = array();
								$rules[$rule_type][] = $rule_param;
								break;
							default:
								$rules[$rule_type] = $rule_param;
						}
						break;
				}
			}
			
			$setname = $name;
			
			$newfield = new fieldtemplate($type, $setname, $label, $explanation);
			if(!empty($error))
				$newfield->setError($error);
			if(!empty($transform))
				$newfield->setTransform($transform);
			if(!empty($default))
				$newfield->default = $default;
			if(!empty($checked))
				$newfield->checked = $checked;
			foreach($rules as $key=>$rule)
				$newfield->setRule($key, $rule);
			
			if(empty($name))
				$output[] = $newfield;
			else
				$output[$name] = $newfield;
			
		}
		
		return $output;
	}
}

class fieldtemplate {
	public	$type,
			$name,
			$default,
			$checked,
			$label,
			$expanation,
			$transform,
			$error;
	public	$rules = array();
	
	function fieldTemplate($type, $name, $label, $explanation='') {
		$this->type = $type;
		$this->name = $name;
		$this->label = $label;
		$this->explanation = $explanation;
	}
	function setError($error) {$this->error = $error;}
	function setTransform($transform) {$this->transform = $transform;}
	function setRule($rule, $value) {
		if(is_array($value)) {
			foreach($value as $val)
				$this->setRule($rule, $val);
			return true;
		}
		switch($rule) {
			case 'enum':
			case 'symbols':
				if(!isset($this->rules[$rule]))
					$this->rules[$rule] = array();
				$this->rules[$rule][$value] = $value;
				break;
			default:
				$this->rules[$rule] = $value;
		}
	}
	
	function runValue($value) {
		$value = trim($value); // A necesesary evil
		switch($this->transform) {
			case 'sha1':
				return sha1($value);
			case 'md5':
				return md5($value);
			case 'upper':
				return strtoupper($value);
			case 'lower':
				return strtolower($value);
			default:
				return $value;
		}
	}
}