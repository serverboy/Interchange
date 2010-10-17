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

$port = (int)$_SERVER['SERVER_PORT'];
// Get the protocol
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

$domain = $_SERVER['HTTP_HOST'];
$path = $_SERVER['REQUEST_URI'];

$url = "$protocol://$domain$path";
if(IXG_KV_URL_CACHE)
	$url_id = "urlcache:" . filemtime('index.json') . SUPER_SECRET . ':' . sha1($url);

if(IXG_KV_URL_CACHE && $url_cache = $keyval->get($url_id)) {
	
	$url_cache = unserialize($url_cache);
	//var_dump($url_cache);
	
	$site = $url_cache["site"];
	$libraries = $url_cache["libraries"];
	$final_path = $url_cache["final_path"];
	$path = $url_cache["actual_file"];
	$split_domain = $url_cache["split_domain"];
	define("EXTENSION", $url_cache["extension"]);
	define("TRAILING_SLASH", $url_cache["trailing_slash"]);
	define('REQUESTED_FILE', $url_cache["requested_file"]);
	define('NOSESSION', $url_cache["nosession"]);
	if($url_cache["methodical"])
		define("METHODICAL", true);
	
} else {
	
	// Get the domain
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
	unset($minlen);
	
	// Get the path and file
	if(strpos($path, '?') !== false)
		$path = substr($path, 0, strpos($path, '?'));
	define("TRAILING_SLASH", substr($path, -1) == '/');
	
	$path = explode('/', $path);
	$new_path = array();
	foreach($path as $p) {
		if($p == '..')
			die('Invalid path.');
		elseif(empty($p) || $p == '.')
			continue;
		$new_path[] = $p;
	}
	$path = $new_path;
	unset($new_path);
	
	if($path_count = count($path)) {
		$final_path = urldecode($path[$path_count - 1]);
		unset($path_count);
	} else
		$final_path = '';
	if(strpos($final_path, '.') !== false) {
		$expl = explode('.', $final_path);
		define('EXTENSION', strtolower($expl[count($expl)-1]));
	} else
		define('EXTENSION', '');
	
	// We'll depopulate this in the parser.
	$actual_file = $path;
	
	# TODO: Make the parser suck less with all these extra variables.
	$site = interchange::parse('index.json');
	$path = $actual_file;
	
	define('REQUESTED_FILE', implode('/', $actual_file));
	
	if(IXG_KV_URL_CACHE)
		$keyval->set($url_id, serialize(array(
			"actual_file"=>$actual_file,
			"final_path"=>$final_path,
			"extension"=>EXTENSION,
			"trailing_slash"=>TRAILING_SLASH,
			"requested_file"=>REQUESTED_FILE,
			"methodical"=>defined("METHODICAL"),
			"nosession"=>defined("NOSESSION"),
			"site"=>$site,
			"libraries"=>empty($libraries)?array():$libraries
		)));
	
}

define('PROTOCOL', $protocol);
define('DOMAIN', $domain);
define('FILENAME', $final_path);
define('URL', $url);
