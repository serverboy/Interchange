<?php

/*
Serverboy Interchange
index.json interpreter

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
		
		$output = self::traverse($node->policies, $level + 1);
		if(is_string($output) && !empty($domain->log))
			define('`', (string)$domain->log);
		
		return $output;
	}
	
	function folder($node, $folder_level = 0) {
		global $path, $actual_file, $wildcards;
		
		$folder = $node->folder;
		
		if(!empty($path[$folder_level]) && $folder == '_wildcard') {
			$varname = $node->variable;
			$wildcards[$varname] = $path[$folder_level];
		} elseif(!isset($path[$folder_level]) || $path[$folder_level] != (string)$folder)
			return false;
		
		// Shift the path up a directory.
		array_shift($actual_file);
		
		$result = self::traverse($node->policies, 0, $folder_level + 1);
		
		if($result === false)
			return true;
		else
			return $result;
	}
	
	private function traverse($node, $domain_level = 0, $folder_level = 0) {
		global $libraries;
		
		foreach($node as $child) {
			$type = $child->type;
			switch($type) {
				case 'app':
					if(isset($child->streaming))
						define("STREAMING", $child->streaming);
					return $child->endpoint;
				case 'methods':
					define("METHODICAL", true);
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