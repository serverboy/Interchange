<?

class interchange {
	
	function parse($directory) {
		$xml = file_get_contents($directory);
		$sxml = new SimpleXMLElement($xml);
		
		$roots = $sxml->children();
		$endpoint = '';
		foreach($roots as $root) {
			$endpoint = self::domain($root);
			if(!empty($endpoint))
				break;
		}
		if(!empty($endpoint))
			return $endpoint;
		else
			return false;
	}
	
	function domain($node, $level = 0) {
		global $split_domain, $wildcards;
		
		$domain = $node['domain'];
		
		if (!empty($split_domain[$level]) && $domain == '_wildcard') {
			$varname = $node['var'];
			$wildcards[$varname] = $split_domain[$level];
		} elseif ($split_domain[$level] != $domain)
			return false;
		
		return self::doChildren($node, 'domain', $level);
	}
	
	function folder($node, $level = 0) {
		global $path, $actual_file, $wildcards;
		
		$folder = $node['folder'];
		
		if(!empty($path[$level]) && $folder == '_wildcard') {
			$varname = $node['var'];
			$wildcards[$varname] = $path[$level];
		} elseif($path[$level] != (string)$folder)
			return false;
		
		// Shift the path up a directory.
		array_shift($actual_file);
		
		return self::doChildren($node, $level);
	}
	
	private function doChildren($node, $type = 'domain', $level = 0) {
		global $libraries;
		
		foreach($node->children() as $child) {
			$name = $child->getName();
			switch($name) {
				case 'app':
					return (string) $child['endpoint'];
				case 'redirect':
					$href = (string) $child['href'];
					//TODO: Implement HTTP code
					//$http = (string) $child['http'];
					header('Location: ' . $href);
					exit;
				case 'lib':
					$libraries[] = (string) $child['library'];
					continue;
				case 'wildcard':
					$params = (string) $child['params'];
					$params = explode('/', $params);
					$levels = count($params);
					continue;
				case 'domain':
				case 'subdomain':
					$result = self::domain($child, $level + 1);
					if($result)
						return $result;
					continue;
				case 'folder':
					$result = self::folder($child, ($type=='folder' ? $level + 1 : 0));
					if($result)
						return $result;
					continue;
			}
		}
	}
	
}