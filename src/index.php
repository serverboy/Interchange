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

header('Date: ' . gmdate('D, d M Y H:i:s', time()) . ' GMT');
header('X-Powered-By: Interchange');

$location = dirname(__FILE__);
define('IXG_PATH_PREFIX', $location . (strlen($location) > 1 ? '/' : ''));

if(file_exists("./constants.php"))
	require("./constants.php");

require("procedures/url_parser.php");
require("parser.php");

$libraries = array();
$wildcards = array();
$site = interchange::parse('index.json');

if(!defined('IXG_LOG'))
	define('IXG_LOG', IXG_PATH_PREFIX . 'access.log');

if(count($actual_file) == 0)
	define('FILE', '');
else
	define('FILE', urldecode(implode('/', $actual_file)));

$directories = $actual_file;
if(defined('EXTENSION'))
	$directories = array_slice($directories, 0, count($directories) - 1);
define('FULLPATH', implode('/', $directories));

if ( $site === false ) {
	//header('HTTP/1.0 404 Not Found');
	readfile('pages/fail.php');
} else {
	
	define('PATH_PREFIX', IXG_PATH_PREFIX . 'endpoints/' . $site);
	
	// Do some cleanup
	$initialized = array('port', 'directories', 'domain', 'split_domain', 'minlen', 'tld', 'file', 'url', 'site', 'final_path');
	foreach($initialized as $i) unset($$i);
	
	require('sessionmanager.php');
	require('views.php');
	require('logging.php');
	require('procedures/local_files.php');
	require('procedures/libraries.php');
	
	if(is_file(PATH_PREFIX . '/endpoint.php')) {
		loadScriptFile(PATH_PREFIX . '/endpoint.php');
	} else {
		ini_set("include_path", ini_get("include_path") . ':' . PATH_PREFIX);
		if(!doload(PATH_PREFIX . '/' . FILE)) {
			header('HTTP/1.0 404 Not Found');
			readfile('./pages/fail.php');
		}
	}
}
