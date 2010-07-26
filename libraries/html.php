<?

/*
*
*	HTML Output Library
*
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