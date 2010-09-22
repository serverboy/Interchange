<?php

/*
Serverboy Interchange
Local file handler

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

function load_page($page, $http_code=200) {
	require_once(IXG_PATH_PREFIX . 'http_codes.php');
	header($error_codes[$http_code]);
	readfile("./pages/$page.php");
}

function load_script_file($file) {
	global $keyval, $path, $session;
	
	require($file);
}

function load_local_file($file, $extension = '', $may_execute=true) {
	
	if(!file_exists($file) || !is_file($file)) {
		return load_page("404", 405);
	}
	
	if(strtoupper($extension) == 'PHP') {
		if($may_execute)
			return load_script_file($file);
		else
			return load_page("405", 405);
	}
	
	require('mimes.php');
	if(isset($mimes[$extension]))
		header('Content-type: ' . $mimes[$extension]);
	else
		header('Content-type: application/octet-stream');
	
	$filesize = filesize($file);
	if(defined('STREAMING'))
		header('Accept-Ranges: bytes');
	
	$last_modified = filemtime($file);
	if(isset($_SERVER["HTTP_IF_MODIFIED_SINCE"])) {
		$expected_modified = strtotime(preg_replace('/;.*$/','',$_SERVER["HTTP_IF_MODIFIED_SINCE"]));
		if($last_modified <= $expected_modified) {
			header("HTTP/1.0 304 Not Modified");
			return;
		}
	}
    header("Expires: Tue, 01 Dec 2037 16:00:00 GMT");
    $cache_for = 31536000;
	header('Age: ' . (((int)$_SERVER["REQUEST_TIME"] - $last_modified) % $cache_for));
	header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $last_modified) . ' GMT');
	
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
		if(($start = (int)$range[0])<=0)
			$start = 0;
		if(!isset($range[1]) || ($end = (int)$range[1])<=0)
			$end = $filesize;
		
		header('HTTP/1.0 206 Partial Content');
		header('Pragma: public');
		header('Accept-Ranges: bytes');
		header("Content-Range: bytes $start-$end/$filesize");
		header('Cache-Control: public');
		header('Content-Transfer-Encoding: binary');
		
		if($_SERVER["REQUEST_METHOD"] == 'HEAD')
			return;
		
		$length = $end - $start;
		
		$fh = fopen($file, 'rb');
		fseek($fh, $start);
		
		while(!feof($fh) && $length > 0 && !(connection_aborted() || connection_status() == 1)) {
			echo fread($fh, min($length, PACKET_SIZE));
			flush();
			$length -= PACKET_SIZE;
		}
		
		fclose($fh);
		return;
		
	}
	
	header('Content-length: ' . $filesize);
	
	#header('Expires: ' . gmdate('D, d M Y H:i:s', (int)$_SERVER["REQUEST_TIME"] + $cache_for) . ' GMT');
	header("Cache-Control: public, max-age=$cache_for");
	
	if($_SERVER["REQUEST_METHOD"] == 'HEAD')
		return;
	
	readfile($file);
	
}

function serve_favicon() {
	if(!SERVE_DEFAULT_FAVICON)
		return false;
	load_local_file("pages/favicon.ico", "ico");
	return true;
}

function doload($dir, $allow_directory=true, $may_execute=true) {
	if(file_exists($dir)) {
		if(is_file($dir)) {
			load_local_file($dir, EXTENSION, $may_execute);
			return true;
		} elseif(is_dir($dir) && !TRAILING_SLASH && REDIRECT_TRAILING_SLASH) {
			if($_SERVER['REQUEST_METHOD'] == "POST") {
				load_page("404", 503);
			} else
				header('Location: ' . URL . '/');
			return true;
		} elseif($allow_directory && is_dir($dir) && (TRAILING_SLASH || FILENAME == '' || HANDLE_TRAILING_SLASH)) {
			require("defaults.php");
			
			foreach($defaults as $default=>$execute) {
				# TODO: Optimize this!
				$extension = explode('.', $default);
				$extension = $extension[1];
				
				if(file_exists("$dir/$default")) {
					if($execute && $may_execute)
						load_script_file("$dir/$default");
					else
						load_local_file("$dir/$default", $extension);
					return true;
				}
			}
		}
	}
	if(REQUESTED_FILE == "favicon.ico")
		return serve_favicon();
	return false;
}