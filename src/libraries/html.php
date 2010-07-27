<?php

/*
Serverboy Interchange

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

class lib_html {
	public $dontSanitize = false;
	function exec($data) {
		$data;
		return false;
	}
	
	function renderHTMLElements($elements) {
		$output = '';
		foreach($elements as $element) {
			if(is_array($element))
				$output .= $this->renderHTML($element);
			else
				$output .= $element;
		}
		return $output;
	}
	function renderHTML($element) {
		$output = '<';
		$output .= $element['name'];
		if(isset($element['attributes']))
			foreach($element['attributes'] as $attribute=>$value)
				$output .= ' ' . $attribute . '="' . htmlentities($value) . '"';
		if(	(isset($element['collapse']) && !$element['collapse']) ||
			!empty($element['value'])
			) {
			$output .= '>';
			if(isset($element['value'])) {
				if(is_array($element['value']))
					$output .= $this->renderHTMLElements($element['value']);
				else {
					if($this->dontSanitize)
						$output .= $element['value'];
					else {
						if($element['value'] == '&nbsp;')
							$output .= '&nbsp;';
						else
							$output .= htmlentities($element['value']);
					}
				}
			}
			$output .= '</' . $element['name'] . '>';
		} else
			$output .= ' />';
		return $output;
	}
	
}