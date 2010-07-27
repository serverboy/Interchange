<?php

/*
Serverboy Interchange
URL Parser

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

// Get the protocol
$port = intval($_SERVER['SERVER_PORT']);
switch($port) {
	case '443':
		$protocol = 'https';
		break;
	case '80':
	case '8000':
	case '8080':
	default:
		$protocol = 'http';
		break;
}

// Get the domain
$domain = $_SERVER['SERVER_NAME'];
$split_domain = explode('.', $domain);
$split_domain = array_reverse($split_domain);
$minlen = 2;
do {
	$minlen++;
	$tld = array_shift($split_domain);
	$split_domain[0] .= '.' . $tld;
	if($minlen > 3)
		$minlen--;
} while (strlen($split_domain[0]) <= $minlen);

// Get the path and file
$file = $_SERVER['REQUEST_URI'];
$file = substr($file, 1); // Strip the leading slash
if(strpos($file, '?') !== false)
	$file = substr($file, 0, strpos($file, '?'));
$path = $file;
$path = str_replace('//', '/', $path);
$path = str_replace('/./', '/', $path);
if(substr($path, -1) == '/') {
	$path = substr($path, 0, strlen($path) - 1);
	define("TRAILING_SLASH", true);
} else
	define("TRAILING_SLASH", false);
$path = explode('/', $path);

foreach($path as $p)
	if($p == '..')
		die('Invalid path.');

$final_path = $path[count($path) - 1];
if(strpos($final_path, '.') !== false) {
	$expl = explode('.', $final_path);
	define('EXTENSION', strtolower($expl[count($expl)-1]));
}

// We'll depopulate this in the parser.
$actual_file = $path;

define('PROTOCOL', $protocol);
define('DOMAIN', $domain);
define('REQUESTED_FILE', $file);
define('FILENAME', $final_path);
$url = $protocol . '://' . $domain . '/' . $file;
define('URL', $url);
