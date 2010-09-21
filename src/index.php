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
	$session = new session_manager();
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
		
		function load_methodfile($path) {
			global $method_base, $method_level;
			
			// Load in the stuff methodical endpoints use.
			require("procedures/methodical_requirements.php");
			
			// If something gets fried, don't stop the world.
			try {
				// Load up the PHP file that contains the methods. Note that we
				// can just do this because the URL parser sanitizes everything
				// for us.
				require($path);
				
				// If the file implements full methodical functionality, proceed
				// with the methodical flow. Otherwise, just exit.
				if(class_exists('methods')) {
					// The methods file should implement the class "methods" based on
					// the abstract class "methods_base"
					$method_base = new methods();
					$method_level++;
				} else {
					// The PHP file was loaded, but there was no methodical
					// code, so we assume it ran successfully.
					exit;
				}
				
			} catch(Exception $e) {
				return false;
			}
			return true;
		}
		
		if(!empty($path)) {
			// Loop through each of the path segments
			for($i=0;$i<$path_len;$i++) {
				$pathlet = array_shift($path);
				
				// Let's assume that the user is referring to the file:
				// /endpoints/endpoint_name/path/so/far/whatever_this_segment_is
				$possible_match = PATH_PREFIX . "$path_name/$pathlet";
				
				switch($method_level) {
					case 0: // Seek Files
						
						// If it's a file (without a PHP extension), load it like a static endpoint
						if(is_file($possible_match) && substr($pathlet, -4) != ".php") {
							// If it can be loaded, we're done. Make sure we don't load it as a
							// directory.
							// This should match the code to load a static file in
							// /procedures/local_files.php
							load_local_file($possible_match, EXTENSION, false);
							exit; // We're done, so break
						
						// If it's a directory, we're looking for a class within it. Append the
						// search path with the current segment and continue searching.
						} elseif(is_dir($possible_match)) {
							$path_name .= "$pathlet/";
							break;
						
						// If it's a file (ending in .methods.php), start the proverbial car because
						// we're probably going to wind up with a methodical endpoint.
						} elseif(is_file($possible_match . ".methods.php") && $pathlet != "__default") {
							
							if(load_methodfile($possible_match . ".methods.php"))
								break;
							
							break 2;
						}
						
						// Nothing was found that applies.
						break 2;
					case 1: // Seek Functions
						// Disallow access to magic functions
						if(substr($pathlet, 0, 2) == "__")
							break 2;
						if(method_exists($method_base, $pathlet)) {
							$method_name = $pathlet;
							$method_level++;
							continue;
						}
						break 2;
					case 2: // Seek Arguments
						// Any further arguments after a method has already been chosen
						// are used as arguments to the method.
						$method_arguments[] = $pathlet;
				}
			}
		}
		
		if($method_level == 0 && is_file(PATH_PREFIX . "$path_name/__default.methods.php")) {
			if(!load_methodfile(PATH_PREFIX . "$path_name/__default.methods.php"))
				exit;
		}
		
		// If the methods class implements a default method, call that and don't fail.
		if($method_level == 1 && method_exists($method_base, '__default')) {
			$method_name = '__default';
			$method_level++;
		}
		
		# TODO : This might not fire if the error occurs on the last item;
		// Throw a 404 if the URL doesn't specify a class and method
		if($method_level < 2)
			load_page("404", 404);
		else {
			// Otherwise, call the method in the class
			$result = call_user_func_array(
				array(
					$method_base,
					$method_name
				), $method_arguments
			);
			if($result !== false) {
				// This should have Django-like output (HttpResponse, etc.)
				$result->output();
			}
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
