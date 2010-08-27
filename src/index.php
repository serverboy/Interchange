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

require("./constants.php");

if(HAPPY_HEADERS) {
	header('Date: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT');
	header('X-Powered-By: Interchange');
}

$location = dirname(__FILE__);
define('IXG_PATH_PREFIX', $location . (strlen($location) > 1 ? '/' : ''));

require("helpers/keyval.php");

$libraries = array();
$wildcards = array();
require("parser.php");
require("procedures/url_parser.php");

if(!defined('IXG_LOG'))
	define('IXG_LOG', IXG_PATH_PREFIX . 'access.log');

if(count($actual_file) == 0)
	define('FILE', '');
else
	define('FILE', urldecode(implode('/', $actual_file)));

/*
$directories = $actual_file;
if(defined('EXTENSION'))
	$directories = array_slice($directories, 0, count($directories) - 1);
define('FULLPATH', implode('/', $directories));
*/

require('procedures/local_files.php');
if($site === false) {
    $fulfilled = false;
    if(REQUESTED_FILE == "favicon.ico")
        $fulfilled = serve_favicon();
    if(!$fulfilled)
        load_page("404", 404);
} else {
	
	define('PATH_PREFIX', IXG_PATH_PREFIX . 'endpoints/' . $site);
	
	// Do some cleanup
	$initialized = array('port', 'directories', 'domain', 'split_domain', 'tld', 'file', 'url', 'site', 'final_path', 'expl');
	foreach($initialized as $i)
		if(isset($$i))
			unset($$i);
	
	require('sessionmanager.php');
	require('views.php');
	require('logging.php');
	require('procedures/libraries.php');
	require('pipes.php'); // Must be loaded after libraries.
	
	if(defined("METHODICAL")) {
		$method_level = 0;
		$path_name = '';
		$path_len = count($path);
		$method_base = null;
		$mathod_name = '';
		$method_arguments = array();
		for($i=0;$i<$path_len;$i++) {
			$pathlet = array_shift($path);
			$possible_match = PATH_PREFIX . "$path_name/$pathlet";
			switch($method_level) {
				case 0: // Seek Files
					if(is_file($possible_match) && substr($pathlet, -4) != ".php") {
						if(doload($possible_match, false)) {
							break 2;
						}
					} elseif(is_dir($possible_match)) {
						$path_name .= "/$pathlet";
						continue;
					} elseif(is_file($possible_match . ".methods.php")) {
						require("procedures/methodical_requirements.php");
						try {
							require($possible_match . ".methods.php");
							$method_base = new methods();
							$method_level++;
						} catch(Exception $e) {
							break 2;
						}
						break 2;
					}
					$i = $path_len - 1;
					break 2;
				case 1: // Seek Functions
					if(substr($pathlet, 0, 2) == "__")
						break 2;
					if(method_exists($method_base, $pathlet)) {
						$method_name = $pathlet;
						$method_level++;
						continue;
					}
					break 2;
				case 2: // Seek Arguments
					$method_arguments[] = $pathlet;
			}
		}
		
		# TODO : This might not fire if the error occurs on the last item;
		if($method_level < 2)
			load_page("404", 404);
		else {
			$result = call_user_func_array(
				array(
					$method_base,
					$method_name
				), $method_arguments
			);
			// This should have Django-like output (HttpResponse, etc.)
			echo $result;
		}
		
	} elseif(is_file(PATH_PREFIX . '/endpoint.php')) {
		load_script_file(PATH_PREFIX . '/endpoint.php');
	} else {
		ini_set("include_path", PATH_PREFIX . ':' . ini_get("include_path"));
		
		if(!doload(PATH_PREFIX . '/' . REQUESTED_FILE)) {
			load_page("404", 404);
		}
	}
}
