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

	public static function parse($directory) {
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

	public static function domain($node, $level = 0) {
		global $split_domain;

		$domain = $node->domain;

		if($domain != "*" && (empty($split_domain[$level]) || $split_domain[$level] != $domain))
			return false;

		$output = self::traverse($node->policies, $level + 1);
		if(is_string($output) && !empty($domain->log))
			define('`', (string)$domain->log);

		return $output;
	}

	public static function folder($node, $folder_level = 0) {
		global $path, $actual_file;

		$folder = $node->folder;

		if(!isset($path[$folder_level]) || $path[$folder_level] != (string)$folder)
			return false;

		// Shift the path up a directory.
		array_shift($actual_file);

		$result = self::traverse($node->policies, 0, $folder_level + 1);

		if($result === false)
			return true;
		else
			return $result;
	}

	public static function traverse($node, $domain_level = 0, $folder_level = 0) {
		global $libraries;

		foreach($node as $child) {
			$type = $child->type;
			switch($type) {

				case 'subscript':
					return $this->parse(IXG_PATH_PREFIX . "/subscripts/" . $child->script);

				case 'proxy':
					define("IXG_PROXY", true);
				case 'methods':
					define("METHODICAL", true);
				case 'app':
					return $child->endpoint;

				case 'nosession':
					define("NOSESSION", true);
					break;

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
