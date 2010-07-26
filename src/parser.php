<?

class interchange {
	
	function parse($directory) {
		// Load the JSON file
		$json = file_get_contents($directory);
		$data = json_decode($json);
		
		// Iterate each root (domain)
		foreach($data as $root) {
			$endpoint = self::domain($root);
			if(!empty($endpoint))
				break;
		}
		
		// Return an endpoint if it exists
		return (!empty($endpoint)) ? $endpoint : false;
	}
	
	function domain($node, $level = 0) {
		global $split_domain, $wildcards;
		
		$domain = $node->domain;
		
		// If this is a subdomain, allow for a wildcard
		if ($level > 0 && !empty($split_domain[$level]) && $domain == '_wildcard') {
			$varname = $node->variable;
			$wildcards[$varname] = $split_domain[$level];
		// If this domain/subdomain object doesn't match the current URL, then we
		// don't have any more instructions
		} elseif ($split_domain[$level] != $domain)
			return false;
		
		$output = self::doChildren($node->policies, $level + 1);
		if(is_string($output) && !empty($domain->log))
			define('IXG_LOG', (string)$domain->log);
		
		return $output;
	}
	
	function folder($node, $folder_level = 0) {
		global $path, $actual_file, $wildcards;
		
		$folder = $node->folder;
		
		if(!empty($path[$folder_level]) && $folder == '_wildcard') {
			$varname = $node->variable;
			$wildcards[$varname] = $path[$folder_level];
		} elseif($path[$folder_level] != (string)$folder)
			return false;
		
		// Shift the path up a directory.
		array_shift($actual_file);
		
		$result = self::doChildren($node->policies, 0, $folder_level + 1);
		
		if($result === false)
			return true;
		else
			return $result;
	}
	
	private function doChildren($node, $domain_level = 0, $folder_level = 0) {
		global $libraries;
		
		foreach($node as $child) {
			$type = $child->type;
			switch($type) {
				case 'app':
					if(isset($child->streaming))
						define("STREAMING", $child->streaming);
					return $child->endpoint;
				case 'redirect':
					$href = $child->href;
					
					if(isset($child->http)) {
						$http_code = intval($child->http);
						
						require_once(IXG_PATH_PREFIX . 'http_codes.php');
						if(isset($redirect_codes[$http_code]))
							header($redirect_codes[$http_code]);
					}
					
					header('Location: ' . $href);
					exit;
				case 'error':
					$http_code = intval($child->http);
					
					require_once(IXG_PATH_PREFIX . 'http_codes.php');
					if(!isset($error_codes[$http_code]))
						$http_code = 404;
					header($error_codes[$http_code]);
					
					if(isset($child->page))
						require(IXG_PATH_PREFIX . $child->page);
					exit;
				case 'lib':
					$libraries[] = (string) $child->library;
					continue;
				case 'domain':
				case 'subdomain':
					$result = self::domain($child, $domain_level);
					if($result)
						return $result;
					continue;
				case 'folder':
					$result = self::folder($child, $folder_level);
					if($result === true)
						return true;
					elseif($result)
						return $result;
					continue;
			}
		}
		return false;
	}
	
}