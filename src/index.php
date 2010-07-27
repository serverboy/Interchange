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

require_once('parser.php');

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
	
	function loadLocalFile($file, $extension = '', $cache_for = 0) {
		global $cache;
		
		if(!file_exists($file) || !is_file($file)) {
			header('HTTP/1.0 404 Not Found');
			readfile('pages/fail.php');
			return;
		}
		
		if(strtoupper($extension) == 'PHP') {
			require($file);
			return;
		}
		
		require('mimes.php');
		if(isset($mimes[$extension]))
			header('Content-type: ' . $mimes[$extension]);
		else
			header('Content-type: application/octet-stream');
		
		$filesize = filesize($file);
		header('Content-length: ' . $filesize);
		if(defined('STREAMING'))
			header('Accept-Ranges: bytes');
		
		if(defined("STREAMING") && (isset($_SERVER['HTTP_RANGE']) || isset($_REQUEST['start']) || isset($_REQUEST['end']))) {
			if(isset($_SERVER['HTTP_RANGE'])) {
				if (!preg_match('/^bytes=\d*-\d*(,\d*-\d*)*$/', $_SERVER['HTTP_RANGE'])) {
					header('HTTP/1.1 416 Requested Range Not Satisfiable');
					header('Content-Range: bytes */' . $filesize); // Required in 416.
					return;
				}
				
				// TODO : Handle multiple range requests
				
				$range = $_SERVER['HTTP_RANGE'];
				$range = substr($range, 6); // Skip "bytes "
				
				$range = explode('-',$range);
				if(($start = intval($range[0]))<=0)
					$start = 0;
				if(!isset($range[1]) || ($end = intval($range[1]))<=0)
					$end = $filesize;
				
			} else {
				$start = 0;
				$end = $filesize;
				
				if(isset($_REQUEST['start']))
					$start = intval($_REQUEST['start']);
				if(isset($_REQUEST['end']))
					$end = intval($_REQUEST['end']);
				
				
			}
			
			header('HTTP/1.0 206 Partial Content');
			header('Pragma: public');
			header('Accept-Ranges: bytes');
			header("Content-Range: bytes $start-$end/$filesize");
			header('Cache-Control: public');
			header('Content-Transfer-Encoding: binary');
			
			if($_SERVER["REQUEST_METHOD"] == 'HEAD')
				return;
			
			$length = $end - $start;
			
			if($start > 0 && defined('EXTENSION') && EXTENSION == 'flv') {
				echo 'FLV',
					pack('C', 1),
					pack('C', 1),
					pack('N', 9),
					pack('N', 9);
			}
			
			$fh = fopen($file, 'rb');
			fseek($fh, $start);
			
			while(!feof($fh) && $length > 0 && !(connection_aborted() || connection_status() == 1)) {
				echo fread($fh, min($length, 4096));
				flush();
				$length -= 4096;
			}
			
			fclose($fh);
			return;
			
		}
		
		$last_modified = filemtime($file);
		if(isset($_SERVER["HTTP_IF_MODIFIED_SINCE"])) {
			$expected_modified = strtotime(preg_replace('/;.*$/','',$_SERVER["HTTP_IF_MODIFIED_SINCE"]));
			if($last_modified <= $expected_modified) {
				header("HTTP/1.0 304 Not Modified");
				return;
			}
		}
		
		if($cache_for == 0) {
			header("Expires: Tue, 01 Dec 2037 16:00:00 GMT");
			$cache_for = 31536000;
		} else {
			header('Expires: ' . gmdate('D, d M Y H:i:s', time() + $cache_for) . ' GMT');
		}
		header("Cache-Control: public, max-age=$cache_for");
		
		header('Age: ' . ((time() - $last_modified) % $cache_for));
		header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $last_modified) . ' GMT');
		
		// Do some GZip goodness
		//ob_start("ob_gzhandler");
		
		if($_SERVER["REQUEST_METHOD"] == 'HEAD')
			return;
		
		readfile($file);
		
	}
	
	function doload($dir) {
		
		if(file_exists($dir)) {
			if(is_file($dir)) {
				loadLocalFile($dir, EXTENSION);
				return true;
			} elseif(is_dir($dir) && !TRAILING_SLASH && REDIRECT_TRAILING_SLASH) {
				header('Location: ' . URL . '/');
				return true;
			} elseif(is_dir($dir) && (TRAILING_SLASH || FILE == '' || HANDLE_TRAILING_SLASH)) {
				require("./defaults.php");
				
				foreach($defaults as $default=>$execute) {
					if(file_exists("$dir/$default")) {
						
						$session = new session_manager();
						
						if($execute)
							require("$dir/$default");
						else
							loadLocalFile("$dir/$default");
						return true;
					}
				}
			}
		}
		return false;
	}
	
	foreach($libraries as $lib)
		require("./libraries/$lib.php");
	function getLib($lib) {
		static $libs;
		
		if (isset($libs[$lib])) return $libs[$lib];
		
		require("./libraries/$lib.php");
		
		$lib_ref = "lib_$lib";
		$nlib = new $lib_ref();
		$libs[$lib] =& $nlib;
		
		if(method_exists($nlib, 'init')) $nlib->init();
		
		return $nlib;
	}
	
	unset($libraries);
	
	if(is_file(PATH_PREFIX . '/endpoint.php')) {
		$session = new session_manager();
		require(PATH_PREFIX . '/endpoint.php');
	} else {
		ini_set("include_path", ini_get("include_path") . ':' . PATH_PREFIX);
		if(!doload(PATH_PREFIX . '/' . FILE)) {
			header('HTTP/1.0 404 Not Found');
			readfile('./pages/fail.php');
		}
	}
}
