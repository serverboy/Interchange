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

function loadScriptFile($file) {
	$session = new session_manager();
	require($file);
}

function loadLocalFile($file, $extension = '') {
	
	if(!file_exists($file) || !is_file($file)) {
		header('HTTP/1.0 404 Not Found');
		readfile('pages/fail.php');
		return;
	}
	
	if(strtoupper($extension) == 'PHP')
		return loadScriptFile($file);
	
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
			require("defaults.php");
			
			foreach($defaults as $default=>$execute) {
				# TODO: Optimize this!
				$extension = explode('.', $default);
				$extension = $extension[1];
				
				if(file_exists("$dir/$default")) {
					if($execute)
						loadScriptFile("$dir/$default");
					else
						loadLocalFile("$dir/$default", $extension);
					return true;
				}
			}
		}
	}
	return false;
}