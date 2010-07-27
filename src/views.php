<?php

/*
Serverboy Interchange
View manager

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

$ixg_viewstack = array();
$ixg_viewvalues = array();
class view_manager {
	
	public static function setvalue($name, $value) {
		global $ixg_viewvalues;
		$ixg_viewvalues[$name] = $value;
	}
	public static function getvalue($name) {
		global $ixg_viewvalues;
		if(isset($ixg_viewvalues[$name]))
			return $ixg_viewvalues[$name];
		return '';
	}
	public static function addview($name) {
		global $ixg_viewstack;
		$ixg_viewstack[] = $name;
	}
	public static function render() {
		global $ixg_viewstack, $path, $session;
		$file = array_shift($ixg_viewstack);
		if(empty($file)) return '';
		ob_start();
		require('./views/' . $file . '.php');
		return ob_get_clean();
	}
	
}